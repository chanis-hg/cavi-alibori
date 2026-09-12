<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogueAction extends Model
{
    use HasFactory;

    protected $table = 'catalogue_actions';

    protected $fillable = ['code', 'description_fr', 'version', 'valide_par', 'valide_le'];

    protected $casts = [
        'valide_le' => 'datetime',
    ];

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function reglesDecision(): HasMany
    {
        return $this->hasMany(RegleDecision::class, 'action_id');
    }
}
