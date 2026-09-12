<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelaisCooperative extends Model
{
    use HasFactory;

    protected $table = 'relais_cooperatives';

    protected $fillable = ['nom', 'commune_id', 'telephone', 'whatsapp_id'];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }
}
