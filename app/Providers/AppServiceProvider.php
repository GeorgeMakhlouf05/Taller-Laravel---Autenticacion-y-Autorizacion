<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MetricasController extends Controller
{
    /**
     * RF 3.1 y RF 3.2: métricas globales, solo para administradores.
     */
    public function index(): JsonResponse
    {
        // Lanza 403 automáticamente si el usuario no pasa el Gate
        Gate::authorize('ver-finanzas');

        return response()->json([
            'total_eventos'       => Evento::count(),
            'ingresos_totales'    => 12500000,
            'moneda'              => 'COP',
            'ingresos_simulados'  => true,
            'nota'                => 'El enunciado no define un modelo de pagos. El monto de ingresos es un valor de demostración simulado, no un cálculo basado en transacciones reales.',
        ], 200);
    }
}