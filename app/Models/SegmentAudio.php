<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegmentAudio extends Model
{
    use HasFactory;

    protected $table = 'segments_audio';

    protected $fillable = ['langue', 'type_slot', 'valeur_slot', 'chemin_fichier', 'locuteur', 'duree_ms'];

    /**
     * Récupère le segment audio pour un type de slot + valeur + langue donnés.
     * Utilisé par AudioAssemblyService (étape 4.4 de l'architecture).
     */
    public static function forSlot(string $typeSlot, string $valeurSlot, string $langue): self
    {
        return static::where('type_slot', $typeSlot)
            ->where('valeur_slot', $valeurSlot)
            ->where('langue', $langue)
            ->firstOrFail();
    }
}
