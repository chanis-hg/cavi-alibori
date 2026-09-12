<?php

namespace Database\Seeders;

use App\Models\CatalogueAction;
use App\Models\Commune;
use App\Models\RegleDecision;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Un compte agronome pour valider les règles dans Filament
        $agronome = User::factory()->create([
            'name' => 'Agent ATDA (démo)',
            'email' => 'atda@cavi-alibori.test',
            'role' => 'agronome_atda',
        ]);

        // Les 6 communes de l'Alibori — la couverture réseau reste à documenter (action n°6 du doc de synthèse)
        $communes = collect([
            'Banikoara', 'Gogounou', 'Kandi', 'Karimama', 'Malanville', 'Ségbana',
        ])->map(fn ($nom) => Commune::create([
            'nom' => $nom,
            'couverture_reseau_estimee' => null, // à vérifier avant le pilote
        ]));

        // Catalogue d'actions — 2 exemples suffisants pour la démo
        $paillage = CatalogueAction::create([
            'code' => 'paillage_sol',
            'description_fr' => 'Pailler le sol pour conserver l\'humidité restante',
            'valide_par' => $agronome->id,
            'valide_le' => now(),
        ]);

        $reportEngrais = CatalogueAction::create([
            'code' => 'report_engrais',
            'description_fr' => 'Reporter l\'apport d\'engrais pour éviter de brûler les cultures',
            'valide_par' => $agronome->id,
            'valide_le' => now(),
        ]);

        // Règles de décision — le scénario de démo (sécheresse sévère, 8-14 jours -> paillage)
        // est celui utilisé dans le microservice Python et dans AudioAssemblyService.
        RegleDecision::create([
            'niveau_risque' => 'secheresse_severe',
            'duree_min' => 8,
            'duree_max' => 14,
            'action_id' => $paillage->id,
            'valide_par' => $agronome->id,
            'valide_le' => now(),
            'actif' => true,
        ]);

        RegleDecision::create([
            'niveau_risque' => 'secheresse_moderee',
            'duree_min' => 1,
            'duree_max' => 7,
            'action_id' => $reportEngrais->id,
            'valide_par' => $agronome->id,
            'valide_le' => now(),
            'actif' => true,
        ]);

        $this->command->info('Communes, catalogue et règles de démo créés. Prochaine étape : segments_audio.');
        $this->call(DemoSeeder::class);
    }
}
