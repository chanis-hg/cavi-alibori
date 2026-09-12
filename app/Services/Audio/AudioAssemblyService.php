<?php

namespace App\Services\Audio;

use App\Models\Commune;
use App\Models\RegleDecision;
use App\Models\SegmentAudio;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AudioAssemblyService
{
    /**
     * Assemble un message audio à partir de segments pré-enregistrés (Slot-Filling).
     *
     * @return string Chemin absolu du fichier MP3 généré
     * @throws RuntimeException si la concaténation échoue
     */
    public function assemble(RegleDecision $regle, Commune $commune, string $langue): string
    {
        // 1. Résoudre les 3 segments nécessaires (commune, risque, action)
        $segments = [
            $this->resolveSegment('commune', $commune->nom, $langue),
            $this->resolveSegment('risque', $regle->niveau_risque, $langue),
            $this->resolveSegment('action', $regle->action->code, $langue),
        ];

        // 2. Vérifier que tous les fichiers existent physiquement
        foreach ($segments as $s) {
            if (!file_exists($s)) {
                throw new RuntimeException("Segment audio introuvable : {$s}");
            }
        }

        // 3. Construire le fichier concat.txt temporaire
        $concatFile = storage_path('app/audio/concat_' . uniqid() . '.txt');
        $this->writeConcatFile($concatFile, $segments);

        // 4. Fichier de sortie
        $sortie = storage_path('app/audio/genera_' . uniqid() . '.mp3');

        // 5. Concaténation via FFmpeg (forme tableau = pas d'échappement à gérer)
        $this->runFfmpegCopy($concatFile, $sortie);

        // 6. Nettoyage du fichier temporaire
        @unlink($concatFile);

        return $sortie;
    }

    /**
     * Récupère le chemin d'un segment depuis la base, ou construit un chemin par convention.
     */
    protected function resolveSegment(string $typeSlot, string $valeurSlot, string $langue): string
    {
        $segment = SegmentAudio::query()
            ->where('type_slot', $typeSlot)
            ->where('valeur_slot', $valeurSlot)
            ->where('langue', $langue)
            ->first();

        if (!$segment) {
            throw new RuntimeException(
                "Aucun segment audio en base pour slot={$typeSlot}, valeur={$valeurSlot}, langue={$langue}"
            );
        }

        return $segment->chemin_fichier;
    }

    /**
     * Écrit le fichier concat.txt au format attendu par le demuxer FFmpeg.
     * Chemins en forward slashes, échappement des quotes simples.
     */
    protected function writeConcatFile(string $path, array $segments): void
    {
        $lines = [];
        foreach ($segments as $s) {
            // FFmpeg veut des / et échappe les ' par '\''
            $safe = str_replace('\\', '/', $s);
            $safe = str_replace("'", "'\\''", $safe);
            $lines[] = "file '{$safe}'";
        }

        file_put_contents($path, implode("\n", $lines) . "\n");
    }

    /**
     * Concaténation sans réencodage (-c copy). Rapide, mais exige des segments homogènes.
     */
    protected function runFfmpegCopy(string $concatFile, string $sortie): void
    {
        $process = new Process([
            'ffmpeg',
            '-f', 'concat',
            '-safe', '0',
            '-i', $concatFile,
            '-c', 'copy',
            '-y',          // overwrite sans demander
            $sortie,
        ]);

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('FFmpeg -c copy a échoué, tentative avec réencodage', [
                'stderr' => $process->getErrorOutput(),
            ]);
            $this->runFfmpegReencode($concatFile, $sortie);
        }
    }

    /**
     * Fallback : réencodage complet. Plus lent, mais tolère des segments hétérogènes
     * (sample rate, canaux, bitrate différents — ce qui arrivera avec de vrais enregistrements).
     */
    protected function runFfmpegReencode(string $concatFile, string $sortie): void
    {
        $process = new Process([
            'ffmpeg',
            '-f', 'concat',
            '-safe', '0',
            '-i', $concatFile,
            '-ar', '44100',
            '-ac', '2',
            '-b:a', '128k',
            '-y',
            $sortie,
        ]);

        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                "Échec de l'assemblage audio FFmpeg : " . $process->getErrorOutput()
            );
        }
    }
}