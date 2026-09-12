<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegleDecision extends Model
{
    use HasFactory;

    protected $table = 'regles_decision';

    protected $fillable = [
        'niveau_risque', 'duree_min', 'duree_max', 'action_id',
        'version', 'valide_par', 'valide_le', 'actif',
    ];

    protected $casts = [
        'valide_le' => 'datetime',
        'actif' => 'boolean',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(CatalogueAction::class, 'action_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
