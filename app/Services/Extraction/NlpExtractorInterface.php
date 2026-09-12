<?php

namespace App\Services\Extraction;

use App\DataTransferObjects\ExtractionResult;

interface NlpExtractorInterface
{
    public function extract(string $texteBulletin): ExtractionResult;
}