<?php

namespace App\DataTransferObjects;

final class ExtractionResult
{
    public function __construct(
        public readonly ?int $communeId,
        public readonly string $niveauRisque,
        public readonly int $dureeJours,
        public readonly ?float $confiance,
        public readonly string $methode,
    ) {}
}