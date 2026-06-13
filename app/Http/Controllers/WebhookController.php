<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;
use App\Services\Xml\PaymentXmlGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    // for testing the receiving money section after completing the Payment XML Generator
    public function generateXml(Request $request): Response
    {
        $generator = new PaymentXmlGenerator();
        $xml = $generator->generate($request->all());

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
