<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * RF 1.2: Devuelve los datos de la cuenta del usuario autenticado.
     */
    public function perfil(Request $request): JsonResponse
    {
        // El usuario sale del token Bearer, nunca de un dato enviado por el cliente
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ], 200);
    }
}