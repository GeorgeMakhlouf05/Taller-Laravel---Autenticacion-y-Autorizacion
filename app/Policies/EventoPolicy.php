<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /**
     * RF 2.3 y RNF 2.1: solo el organizador propietario puede modificar el evento.
     * Se compara el ID del usuario autenticado (que sale del token)
     * con el user_id guardado en el evento.
     */
    public function update(User $user, Evento $evento): bool
    {
        return (int) $user->id === (int) $evento->user_id;
    }
}