<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WasenderApiController;

Route::post('/wasender/send-text', [WasenderApiController::class, 'sendText'])
    ->name('panel.api.wasender.send-text');
