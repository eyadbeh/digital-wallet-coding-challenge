<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\ProcessWebhookJob;

class WebhookController extends Controller
{
    public function handle(Request $request, string $bank): JsonResponse
    {
        //getContent to get the raw data from the request
        $payload = $request->getContent();

        $log = WebhookLog::create([
            'bank_name' => $bank,
            'payload' => $payload,
            'status' => 'pending', //the job will change the status
        ]);

        ProcessWebhookJob::dispatch($payload, $bank, $log->id);

        return response()->json([
            'message' => 'Webhook received and queued for processing.',
            'webhook_log_id' => $log->id,
        ], 202);
    }
}
