<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\ProcessWebhookJob;

class WebhookController extends Controller
{
    public function handle(Request $request, $bank)
    {
        //getContent to get the raw data from the request
        $data = $request->getContent();

        ProcessWebhookJob::dispatch($data, $bank);

        return response()->json([
            'message' => 'Webhook received',
        ], 202);
    }
}
