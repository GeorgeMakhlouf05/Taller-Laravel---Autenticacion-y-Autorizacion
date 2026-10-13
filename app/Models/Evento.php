<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['titulo', 'descripcion', 'cupo_maximo'])]
class Evento extends Model
{
    /**
     * El organizador responsable del evento.
     * Se usa 'user_id' como llave foránea hacia la tabla users.
     */
    public function organizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}