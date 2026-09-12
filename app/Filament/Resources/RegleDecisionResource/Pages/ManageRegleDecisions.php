<?php

namespace App\Filament\Resources\RegleDecisionResource\Pages;

use App\Filament\Resources\RegleDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRegleDecisions extends ManageRecords
{
    protected static string $resource = RegleDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
