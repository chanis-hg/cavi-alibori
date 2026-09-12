<?php

namespace App\Services\Extraction;

use App\DataTransferObjects\ExtractionResult;
use App\Models\Commune;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

final class EmbeddingExtractor implements NlpExtractorInterface
{
    // 3 secondes : au-delà, on considère le microservice endormi ou en
    // panne, et on ne bloque pas le job en file d'attente pour ça.
    private const TIMEOUT_SECONDES = 3;

    public function extract(string $texteBulletin): ExtractionResult
    {
        $reponse = Http::timeout(self::TIMEOUT_SECONDES)
            ->post('http://127.0.0.1:8000/classifier', [
                'texte' => $texteBulletin,
            ]);

        $reponse->throw(); // lève une exception si le statut n'est pas 2xx

        $donnees = $reponse->json();

        return new ExtractionResult(
            communeId: $this->detecterCommune($texteBulletin), // toujours par regex, section 2 non gérée par le modèle
            niveauRisque: $donnees['niveau_risque'],
            dureeJours: $this->detecterDuree($texteBulletin), // idem
            confiance: $donnees['confiance'],
            methode: 'embeddings',
        );
    }

    // Dupliqué de RegexFallbackExtractor pour l'instant — on extraira
    // ça en trait partagé si le doublon devient gênant, pas avant.
    private function detecterCommune(string $texte): ?int
    {
        $texte = mb_strtolower($texte);
        foreach (['banikoara', 'gogounou', 'kandi', 'karimama', 'malanville', 'segbana'] as $nom) {
            if (str_contains($texte, $nom)) {
                return Commune::where('nom', 'like', "%{$nom}%")->value('id');
            }
        }
        return null;
    }

    private function detecterDuree(string $texte): ?int
    {
        if (preg_match('/(\d+)\s*j(?:ours?)?\b/', mb_strtolower($texte), $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}