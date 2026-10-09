<?php

namespace Tests\Feature\Pipeline;

use App\Models\CatalogueAction;
use App\Models\Commune;
use App\Models\RegleDecision;
use App\Services\Audio\AudioAssemblyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AudioAssemblyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_echoue_clairement_si_un_segment_audio_manque_en_base(): void
    {
        $commune = Commune::create(['nom' => 'Banikoara']);
        $action = CatalogueAction::create(['code' => 'paillage_sol', 'description_fr' => 'Pailler le sol']);
        $regle = RegleDecision::create([
            'niveau_risque' => 'secheresse_severe',
            'duree_min' => 8,
            'duree_max' => 14,
            'action_id' => $action->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Aucun segment audio en base');

        (new AudioAssemblyService())->assemble($regle, $commune, 'bariba');
    }
}
