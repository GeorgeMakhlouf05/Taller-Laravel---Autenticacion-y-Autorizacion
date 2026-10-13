<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * RF 1.1: Inicia sesión y devuelve un token Bearer de Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        // Si la validación falla, Laravel responde automáticamente con 422
        $request->validate([
            'email'    => 'required|email|max:150',
            'password' => 'required|string',
        ], [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'email.max'         => 'El correo electrónico no puede superar los 150 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Mismo mensaje si el usuario no existe o la contraseña es incorrecta,
        // para no revelar qué correos están registrados
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        // createToken() existe gracias al trait HasApiTokens del modelo User
        $token = $user->createToken('token-eventhub')->plainTextToken;

        return response()->json([
            'message'    => 'Inicio de sesión exitoso.',
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}