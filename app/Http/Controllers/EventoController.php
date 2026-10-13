<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventoController extends Controller
{
    /**
     * RF 2.1: catálogo público de eventos con el nombre del organizador.
     */
    public function index(): JsonResponse
    {
        $eventos = Evento::with('organizador:id,name')->get()->map(function (Evento $evento) {
            return [
                'id'          => $evento->id,
                'titulo'      => $evento->titulo,
                'descripcion' => $evento->descripcion,
                'cupo_maximo' => $evento->cupo_maximo,
                'organizador' => $evento->organizador->name,
            ];
        });

        return response()->json($eventos, 200);
    }

    /**
     * RF 2.2 y RF 2.3: actualiza un evento solo si el usuario es su propietario.
     */
    public function update(Request $request, Evento $evento): JsonResponse
    {
        // La Policy decide. Si falla, Laravel responde 403 automáticamente.
        // Se autoriza ANTES de validar para no dar pistas a quien no es el dueño.
        Gate::authorize('update', $evento);

        $datos = $request->validate([
            'titulo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'cupo_maximo' => 'required|integer|min:1',
        ], [
            'titulo.required'      => 'El título es obligatorio.',
            'titulo.max'           => 'El título no puede superar los 100 caracteres.',
            'cupo_maximo.required' => 'El cupo máximo es obligatorio.',
            'cupo_maximo.integer'  => 'El cupo máximo debe ser un número entero.',
            'cupo_maximo.min'      => 'El cupo máximo debe ser mayor que cero.',
        ]);

        // validate() devuelve solo los campos validados,
        // así que un user_id enviado por el cliente se ignora.
        $evento->update($datos);

        return response()->json([
            'message' => 'Evento actualizado con éxito.',
            'evento'  => [
                'id'          => $evento->id,
                'titulo'      => $evento->titulo,
                'descripcion' => $evento->descripcion,
                'cupo_maximo' => $evento->cupo_maximo,
            ],
        ], 200);
    }
}