<?php

namespace App\Services\Extraction;

use App\DataTransferObjects\ExtractionResult;
use App\Models\Commune;

final class RegexFallbackExtractor implements NlpExtractorInterface
{
    private const COMMUNES = [
        'banikoara', 'gogounou', 'kandi', 'karimama', 'malanville', 'segbana',
    ];

    private const NIVEAUX_RISQUE = [
        'secheresse severe' => 'secheresse_severe',
        'secheresse moderee' => 'secheresse_moderee',
        'risque faible' => 'risque_faible',
    ];

    public function extract(string $texteBulletin): ExtractionResult
    {
        $texte = $this->normaliser($texteBulletin);

        return new ExtractionResult(
            communeId: $this->detecterCommune($texte),
            niveauRisque: $this->detecterNiveauRisque($texte) ?? 'inconnu',
            dureeJours: $this->detecterDuree($texte) ?? 0,
            confiance: null,
            methode: 'regex_fallback',
        );
    }

    private function normaliser(string $texte): string
    {
        $texte = mb_strtolower($texte);
        return str_replace(
            ['é', 'è', 'ê', 'à', 'â', 'î', 'ô', 'û'],
            ['e', 'e', 'e', 'a', 'a', 'i', 'o', 'u'],
            $texte
        );
    }

    private function detecterCommune(string $texte): ?int
    {
        foreach (self::COMMUNES as $nom) {
            if (str_contains($texte, $nom)) {
                return Commune::where('nom', 'like', "%{$nom}%")->value('id');
            }
        }
        return null;
    }

    private function detecterNiveauRisque(string $texte): ?string
    {
        foreach (self::NIVEAUX_RISQUE as $motif => $code) {
            if (str_contains($texte, $motif)) {
                return $code;
            }
        }
        return null;
    }

    private function detecterDuree(string $texte): ?int
    {
        if (preg_match('/(\d+)\s*j(?:ours?)?\b/', $texte, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}