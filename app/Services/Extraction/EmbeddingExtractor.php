<?php

namespace App\Services\Extraction;

use App\DataTransferObjects\ExtractionResult;
use App\Models\Commune;
use Illuminate\Support\Facades\Http;

final class EmbeddingExtractor implements NlpExtractorInterface
{
    public function extract(string $texteBulletin): ExtractionResult
    {
        // Au-delà du délai (3 s par défaut), on considère le microservice
        // endormi ou en panne, et le job bascule sur le repli regex.
        $reponse = Http::timeout(config('services.cavi_ml.timeout'))
            ->post(rtrim(config('services.cavi_ml.url'), '/') . '/classifier', [
                'texte' => $texteBulletin,
            ]);

        $reponse->throw(); // lève une exception si le statut n'est pas 2xx

        $donnees = $reponse->json();

        return new ExtractionResult(
            communeId: $this->detecterCommune($texteBulletin), // toujours par regex, section 2 non gérée par le modèle
            niveauRisque: $donnees['niveau_risque'],
            dureeJours: $this->detecterDuree($texteBulletin) ?? 0, // idem ; 0 = durée non trouvée (même convention que le repli)
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