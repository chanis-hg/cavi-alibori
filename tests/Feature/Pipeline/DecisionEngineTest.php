<?php

namespace Tests\Feature\Pipeline;

use App\DataTransferObjects\ExtractionResult;
use App\Models\CatalogueAction;
use App\Models\RegleDecision;
use App\Services\Decision\DecisionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecisionEngineTest extends TestCase
{
    use RefreshDatabase;

    private function creerRegle(string $risque, int $min, int $max, bool $actif = true): RegleDecision
    {
        $action = CatalogueAction::create([
            'code' => "action_{$risque}_{$min}_{$max}",
            'description_fr' => 'Action de test',
        ]);

        return RegleDecision::create([
            'niveau_risque' => $risque,
            'duree_min' => $min,
            'duree_max' => $max,
            'action_id' => $action->id,
            'actif' => $actif,
        ]);
    }

    private function resultat(string $risque, int $duree): ExtractionResult
    {
        return new ExtractionResult(
            communeId: null,
            niveauRisque: $risque,
            dureeJours: $duree,
            confiance: null,
            methode: 'test',
        );
    }

    public function test_trouve_la_regle_qui_correspond_au_risque_et_a_la_duree(): void
    {
        $regle = $this->creerRegle('secheresse_severe', 8, 14);

        $decision = (new DecisionEngine())->decide($this->resultat('secheresse_severe', 12));

        $this->assertNotNull($decision);
        $this->assertSame($regle->id, $decision->id);
    }

    public function test_les_bornes_de_duree_sont_incluses(): void
    {
        $this->creerRegle('secheresse_severe', 8, 14);
        $moteur = new DecisionEngine();

        $this->assertNotNull($moteur->decide($this->resultat('secheresse_severe', 8)));
        $this->assertNotNull($moteur->decide($this->resultat('secheresse_severe', 14)));
    }

    public function test_aucune_regle_si_la_duree_est_hors_intervalle(): void
    {
        $this->creerRegle('secheresse_severe', 8, 14);

        $this->assertNull((new DecisionEngine())->decide($this->resultat('secheresse_severe', 20)));
    }

    public function test_une_regle_desactivee_n_est_jamais_appliquee(): void
    {
        $this->creerRegle('secheresse_severe', 8, 14, actif: false);

        $this->assertNull((new DecisionEngine())->decide($this->resultat('secheresse_severe', 10)));
    }

    public function test_un_risque_inconnu_ne_declenche_aucune_consigne(): void
    {
        $this->creerRegle('secheresse_severe', 0, 30);

        $this->assertNull((new DecisionEngine())->decide($this->resultat('inconnu', 10)));
    }
}
