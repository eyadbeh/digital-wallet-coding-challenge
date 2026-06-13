<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working'
    ]);
});

Route::post('/webhooks/{bank}', [WebhookController::class, 'handle']);
Route::post('/payments/xml', [WebhookController::class, 'generateXml']);