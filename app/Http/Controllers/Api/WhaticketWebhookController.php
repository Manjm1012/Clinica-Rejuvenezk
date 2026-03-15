<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Patient;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhaticketWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized webhook request.'], 401);
        }

        $payload = $request->all();
        $externalTicketId = (string) data_get($payload, 'ticket.id', data_get($payload, 'ticketId', ''));
        $rawPhone = data_get($payload, 'contact.number', data_get($payload, 'contact.phone', data_get($payload, 'remoteJid')));

        if ($externalTicketId === '' || empty($rawPhone)) {
            return response()->json([
                'message' => 'Missing required ticket or contact information.',
            ], 422);
        }

        $client = $this->resolveClient($payload);
        $phone = $this->normalizePhone((string) $rawPhone);
        $contactName = trim((string) data_get($payload, 'contact.name', 'Paciente'));

        $patient = Patient::firstOrCreate(
            [
                'client_id' => $client->id,
                'phone' => $phone,
            ],
            [
                'first_name' => $contactName !== '' ? $contactName : 'Paciente',
            ]
        );

        $conversation = WhatsappConversation::updateOrCreate(
            [
                'client_id' => $client->id,
                'external_ticket_id' => $externalTicketId,
            ],
            [
                'patient_id' => $patient->id,
                'status' => (string) data_get($payload, 'ticket.status', data_get($payload, 'status', 'open')),
                'last_message_at' => $this->parseTimestamp(data_get($payload, 'message.timestamp', data_get($payload, 'timestamp'))),
            ]
        );

        $messageBody = (string) data_get($payload, 'message.body', data_get($payload, 'body', ''));
        $externalMessageId = (string) data_get($payload, 'message.id', data_get($payload, 'messageId', ''));
        $isOutbound = (bool) data_get($payload, 'message.fromMe', data_get($payload, 'fromMe', false));

        if ($messageBody !== '' || $externalMessageId !== '') {
            $attributes = [
                'conversation_id' => $conversation->id,
            ];

            if ($externalMessageId !== '') {
                $attributes['external_message_id'] = $externalMessageId;
            }

            WhatsappMessage::firstOrCreate(
                $attributes,
                [
                    'direction' => $isOutbound ? 'outbound' : 'inbound',
                    'body' => $messageBody,
                    'payload' => $payload,
                    'sent_at' => $this->parseTimestamp(data_get($payload, 'message.timestamp', data_get($payload, 'timestamp'))),
                ]
            );
        }

        return response()->json([
            'status' => 'ok',
            'client_id' => $client->id,
            'patient_id' => $patient->id,
            'conversation_id' => $conversation->id,
        ]);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = (string) config('services.whaticket.webhook_secret', '');

        if ($secret === '') {
            return true;
        }

        return hash_equals($secret, (string) $request->header('X-Whaticket-Token', ''));
    }

    private function resolveClient(array $payload): Client
    {
        $defaultClientId = config('services.whaticket.default_client_id');
        $externalClientId = (string) data_get($payload, 'tenantId', data_get($payload, 'companyId', data_get($payload, 'ticket.companyId', '')));

        if ($externalClientId !== '') {
            return Client::firstOrCreate(
                ['external_id' => $externalClientId],
                [
                    'name' => 'Cliente '.$externalClientId,
                    'slug' => Str::slug('cliente-'.$externalClientId),
                    'is_active' => true,
                ]
            );
        }

        if ($defaultClientId) {
            $client = Client::find($defaultClientId);
            if ($client) {
                return $client;
            }
        }

        return Client::firstOrCreate(
            ['slug' => 'cliente-principal'],
            [
                'name' => 'Cliente Principal',
                'is_active' => true,
            ]
        );
    }

    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return $value;
        }

        if (! str_starts_with($digits, '57') && strlen($digits) === 10) {
            $digits = '57'.$digits;
        }

        return '+'.$digits;
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if (is_numeric($value)) {
            $timestamp = (int) $value;

            if ($timestamp > 9999999999) {
                $timestamp = (int) floor($timestamp / 1000);
            }

            return Carbon::createFromTimestamp($timestamp);
        }

        if (is_string($value) && trim($value) !== '') {
            return Carbon::parse($value);
        }

        return null;
    }
}
