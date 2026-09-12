<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegleDecisionResource\Pages;
use App\Models\RegleDecision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegleDecisionResource extends Resource
{
    protected static ?string $model = RegleDecision::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Règles ATDA';

    protected static ?string $modelLabel = 'Règle de décision';

    protected static ?string $pluralModelLabel = 'Règles de décision';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('niveau_risque')
                ->label('Niveau de risque')
                ->options([
                    'secheresse_severe' => 'Sécheresse sévère',
                    'secheresse_moderee' => 'Sécheresse modérée',
                ])
                ->required(),

            Forms\Components\TextInput::make('duree_min')
                ->label('Durée min (jours)')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('duree_max')
                ->label('Durée max (jours)')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('action_id')
                ->label('Action')
                ->relationship('action', 'code')
                ->required(),

            Forms\Components\Toggle::make('actif')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('niveau_risque')
                    ->label('Risque')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'secheresse_severe' => 'danger',
                        'secheresse_moderee' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('duree_min')
                    ->label('Durée min'),

                Tables\Columns\TextColumn::make('duree_max')
                    ->label('Durée max'),

                Tables\Columns\TextColumn::make('action.code')
                    ->label('Action')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('actif')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validée par')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('valide_le')
                    ->label('Validée le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('actif')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRegleDecisions::route('/'),
        ];
    }
}
