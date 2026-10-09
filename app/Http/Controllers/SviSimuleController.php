<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractVariablesJob;
use App\Models\Bulletin;
use App\Models\MessageGenere;

class SviSimuleController extends Controller
{
    /**
     * Bulletin de démonstration (scénario unique du hackathon).
     * Format supposé : la granularité réelle des bulletins Météo-Bénin reste à confirmer.
     */
    private const BULLETIN_DEMO = 'Commune de Banikoara : sécheresse sévère, 12 jours.';

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
        $bulletin = Bulletin::create([
            'source' => 'test',
            'texte_brut' => self::BULLETIN_DEMO,
            'recu_le' => now(),
            'statut_extraction' => 'en_attente',
        ]);

        // Même chemin que la production : microservice d'embeddings d'abord,
        // repli regex automatique s'il ne répond pas.
        ExtractVariablesJob::dispatchSync($bulletin);

        return redirect()->route('svi.index');
    }
}
