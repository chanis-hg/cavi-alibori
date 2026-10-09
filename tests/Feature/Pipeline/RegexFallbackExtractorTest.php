<?php

namespace Tests\Feature\Pipeline;

use App\Models\Commune;
use App\Services\Extraction\RegexFallbackExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegexFallbackExtractorTest extends TestCase
{
    use RefreshDatabase;

    public function test_extrait_commune_risque_et_duree_du_bulletin_de_demo(): void
    {
        $banikoara = Commune::create(['nom' => 'Banikoara']);

        $resultat = (new RegexFallbackExtractor())
            ->extract('Commune de Banikoara : sécheresse sévère, 12 jours.');

        $this->assertSame($banikoara->id, $resultat->communeId);
        $this->assertSame('secheresse_severe', $resultat->niveauRisque);
        $this->assertSame(12, $resultat->dureeJours);
        $this->assertSame('regex_fallback', $resultat->methode);
    }

    public function test_ignore_les_accents_et_la_casse(): void
    {
        $resultat = (new RegexFallbackExtractor())
            ->extract('SÉCHERESSE MODÉRÉE prévue sur 5 jours');

        $this->assertSame('secheresse_moderee', $resultat->niveauRisque);
        $this->assertSame(5, $resultat->dureeJours);
    }

    public function test_un_bulletin_non_reconnu_donne_un_risque_inconnu(): void
    {
        $resultat = (new RegexFallbackExtractor())
            ->extract('Temps ensoleillé sur le département.');

        $this->assertNull($resultat->communeId);
        $this->assertSame('inconnu', $resultat->niveauRisque);
        $this->assertSame(0, $resultat->dureeJours);
    }
}
