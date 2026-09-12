<?php

namespace App\Http\Controllers;

use App\Models\MessageGenere;
use App\Models\Bulletin;
use App\Services\Extraction\RegexFallbackExtractor;
use App\Jobs\ApplyDecisionRuleJob;

class SviSimuleController extends Controller
{
    public function index()
    {
        $messages = MessageGenere::with(['bulletin', 'regle.action'])
            ->latest('id')
            ->take(20)
            ->get();

        return view('svi.index', compact('messages'));
    }

    public function play(MessageGenere $message)
    {
        if (!file_exists($message->chemin_fichier_final)) {
            abort(404, 'Fichier audio introuvable');
        }

        return response()->file($message->chemin_fichier_final, [
            'Content-Type' => 'audio/mpeg',
        ]);
    }

    public function ingest()
    {
        $bulletin = \App\Models\Bulletin::create([
            'source' => 'test',
            'texte_brut' => 'Commune de Banikoara : sécheresse sévère, 12 jours.',
            'recu_le' => now(),
            'statut_extraction' => 'en_attente',
        ]);

        $extractor = new \App\Services\Extraction\RegexFallbackExtractor();
        $resultat = $extractor->extract($bulletin->texte_brut);

        \App\Jobs\ApplyDecisionRuleJob::dispatchSync($bulletin->id, $resultat);

        return redirect()->route('svi.index');
    }
}
