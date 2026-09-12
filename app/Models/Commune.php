<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'departement', 'couverture_reseau_estimee'];

    public function relaisCooperatives(): HasMany
    {
        return $this->hasMany(RelaisCooperative::class);
    }

    public function variablesExtraites(): HasMany
    {
        return $this->hasMany(VariableExtraite::class);
    }
}
