<?php

use App\Http\Controllers\Api\GateAppSessionController;
use App\Http\Controllers\GateScanController;
use Illuminate\Support\Facades\Route;

/*
 * The Android gate app. It signs in with a personal access token and uses the same roster
 * and scan recording as the gate scanner page.
 */
Route::prefix('gate')->group(function () {
    Route::post('/login', [GateAppSessionController::class, 'store']);

    Route::middleware(['auth:sanctum', 'role:security,admin'])->group(function () {
        Route::get('/roster', [GateScanController::class, 'roster']);
        Route::post('/scans', [GateScanController::class, 'store']);
        Route::post('/logout', [GateAppSessionController::class, 'destroy']);
    });
});
