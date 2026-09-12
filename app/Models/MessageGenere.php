<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageGenere extends Model
{
    use HasFactory;

    protected $table = 'messages_generes';

    protected $fillable = ['bulletin_id', 'regle_id', 'langue', 'chemin_fichier_final', 'genere_le'];

    protected $casts = [
        'genere_le' => 'datetime',
    ];

    public function bulletin(): BelongsTo
    {
        return $this->belongsTo(Bulletin::class);
    }

    public function regle(): BelongsTo
    {
        return $this->belongsTo(RegleDecision::class, 'regle_id');
    }

    public function journauxDiffusion(): HasMany
    {
        return $this->hasMany(JournalDiffusion::class, 'message_id');
    }
}
