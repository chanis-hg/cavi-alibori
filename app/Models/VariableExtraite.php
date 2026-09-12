<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariableExtraite extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulletin_id', 'commune_id', 'niveau_risque', 'duree_jours',
        'confiance', 'methode', 'reponse_brute_service', 'extrait_le',
    ];

    protected $casts = [
        'reponse_brute_service' => 'array',
        'extrait_le' => 'datetime',
        'confiance' => 'float',
    ];

    public function bulletin(): BelongsTo
    {
        return $this->belongsTo(Bulletin::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }
}
