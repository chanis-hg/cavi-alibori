<?php

namespace App\Jobs;

use App\DataTransferObjects\ExtractionResult;
use App\Models\Commune;
use App\Models\MessageGenere;
use App\Services\Audio\AudioAssemblyService;
use App\Services\Decision\DecisionEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplyDecisionRuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $bulletinId,
        private readonly ExtractionResult $resultat,
    ) {}

    public function handle(DecisionEngine $moteur, AudioAssemblyService $audio): void
    {
        $regle = $moteur->decide($this->resultat);

        if ($regle === null) {
            Log::warning('Aucune règle ne correspond', [
                'bulletin_id' => $this->bulletinId,
                'niveau_risque' => $this->resultat->niveauRisque,
                'duree_jours' => $this->resultat->dureeJours,
            ]);
            return;
        }

        Log::info('Règle trouvée', [
            'bulletin_id' => $this->bulletinId,
            'regle_id' => $regle->id,
            'action_id' => $regle->action_id,
        ]);

        // Une seule langue pour la démo : bariba
        $langue = 'bariba';

        // Récupérer la commune depuis l'ID extrait
        $commune = $this->resultat->communeId
            ? Commune::find($this->resultat->communeId)
            : null;

        if (!$commune) {
            Log::warning('Commune introuvable, audio non généré', [
                'commune_id' => $this->resultat->communeId,
            ]);
            return;
        }

        try {
            $cheminAudio = $audio->assemble($regle, $commune, $langue);

            MessageGenere::create([
                'bulletin_id' => $this->bulletinId,
                'regle_id' => $regle->id,
                'langue' => $langue,
                'chemin_fichier_final' => $cheminAudio,
                'genere_le' => now(),
            ]);

            Log::info('Message audio généré', [
                'bulletin_id' => $this->bulletinId,
                'regle_id' => $regle->id,
                'fichier' => $cheminAudio,
            ]);
        } catch (\Throwable $e) {
            Log::error('Échec génération audio', [
                'bulletin_id' => $this->bulletinId,
                'erreur' => $e->getMessage(),
            ]);
        }
    }
}