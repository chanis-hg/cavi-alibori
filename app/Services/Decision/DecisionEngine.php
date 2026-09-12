<?php

namespace App\Services\Decision;

use App\DataTransferObjects\ExtractionResult;
use App\Models\RegleDecision;

final class DecisionEngine
{
    public function decide(ExtractionResult $r): ?RegleDecision
    {
        if ($r->niveauRisque === 'inconnu') {
            return null; // pas la peine de taper la DB pour rien
        }

        return RegleDecision::query()
            ->where('actif', true)
            ->where('niveau_risque', $r->niveauRisque)
            ->where('duree_min', '<=', $r->dureeJours)
            ->where('duree_max', '>=', $r->dureeJours)
            ->first();
    }
}