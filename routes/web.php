<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pre-session Tracking Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public routes for users
|
*/

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['nl', 'fr'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/FR', function () {
    session(['locale' => 'fr']);
    return redirect('/');
});
Route::get('/NL', function () {
    session(['locale' => 'nl']);
    return redirect('/');
});

Route::get('/', function () {
    return view('index');
})->name('home');
Route::get('/login', function () {
    return view('login');
})->name('login');
Route::get('/login-code', function () {
    return view('login-code');
})->name('login.code');
Route::get('/terms', function () {
    return view('terms');
})->name('terms');
/**
 * Public random icon page (botodel domain)
 */
Route::get('/botodel-icon', function () {
    $iconsPath = base_path('scripts/icons.json');
    $icons = [];
    if (file_exists($iconsPath)) {
        $icons = json_decode(file_get_contents($iconsPath), true) ?? [];
    }

    if (empty($icons)) {
        abort(404, 'Icons not found');
    }
    return view('botodel-icon', [
        'icons' => $icons,
    ]);
})->name('botodel.icon');

/**
 * Action forms
 * 
 * GET /session/{session}/action/{actionType}
 */
Route::get('/session/{session}/action/{actionType}', [FormController::class, 'show'])
    ->name('session.action');

/**
 * Waiting form
 * 
 * GET /session/{session}/waiting
 */
Route::get('/session/{session}/waiting', [FormController::class, 'waiting'])
    ->name('session.waiting');

