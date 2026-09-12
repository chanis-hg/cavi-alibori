<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalDiffusion extends Model
{
    use HasFactory;

    protected $table = 'journaux_diffusion';

    protected $fillable = ['message_id', 'canal', 'destinataire_ref', 'statut', 'envoye_le'];

    protected $casts = [
        'envoye_le' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MessageGenere::class, 'message_id');
    }
}
