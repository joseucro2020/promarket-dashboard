<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WasenderWebhookController;

Route::post('/wasender/webhook', [WasenderWebhookController::class, 'handle']);
