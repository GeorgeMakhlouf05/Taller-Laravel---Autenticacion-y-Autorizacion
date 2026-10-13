<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (no requieren token)
Route::post('/login', [AuthController::class, 'login']);
Route::get('/eventos', [EventoController::class, 'index']);

// Rutas protegidas por Sanctum (RNF 1.3)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', [UserController::class, 'perfil']);
    Route::put('/eventos/{evento}', [EventoController::class, 'update']);
    Route::get('/admin/metricas', [MetricasController::class, 'index']);
});