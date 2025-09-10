<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\BackChannelLogoutController;

Route::middleware(['csrf.verify'])
    ->post('/Back_logout', [BackChannelLogoutController::class, 'handle'])
    ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ONLY IF USING FORTIFY AUTH ROUTES
require __DIR__ . '/fortify.php';
