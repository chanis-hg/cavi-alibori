<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bulletin extends Model
{
    use HasFactory;

    protected $fillable = ['source', 'texte_brut', 'statut_extraction', 'recu_le'];

    protected $casts = [
        'recu_le' => 'datetime',
    ];

    public function variableExtraite(): HasOne
    {
        return $this->hasOne(VariableExtraite::class);
    }

    public function messagesGeneres(): HasMany
    {
        return $this->hasMany(MessageGenere::class);
    }
}
