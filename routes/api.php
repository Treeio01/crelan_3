<?php

declare(strict_types=1);

use App\Http\Controllers\FormController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for session management.
|
*/

Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->name('telegram.webhook');

Route::post('/visit', VisitController::class)
    ->name('visit');

Route::prefix('pre-session')->name('api.pre-session.')->group(function (): void {
    Route::get('/sessions', [TrackingController::class, 'index'])->name('index');
    Route::post('/', [TrackingController::class, 'create'])->name('create');
    Route::get('/{preSession}', [TrackingController::class, 'show'])->name('show');
    Route::post('/{preSession}/online', [TrackingController::class, 'updateOnlineStatus'])->name('online');
    Route::post('/{preSession}/convert', [TrackingController::class, 'convert'])->name('convert');
});

Route::prefix('session')->name('api.session.')->group(function (): void {
    Route::post('/', [SessionController::class, 'store'])->name('store');
    Route::get('/{session}/status', [SessionController::class, 'status'])->name('status');
    Route::post('/{session}/ping', [SessionController::class, 'ping'])->name('ping');
    Route::get('/{session}/online', [SessionController::class, 'online'])->name('online');
    Route::post('/{session}/submit', [FormController::class, 'submit'])->name('submit');
    Route::post('/{session}/visit', [SessionController::class, 'trackVisit'])->name('visit');
    Route::post('/{session}/method', [SessionController::class, 'notifyMethod'])->name('method');
});
