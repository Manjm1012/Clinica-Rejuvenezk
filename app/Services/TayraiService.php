<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\SiteSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TayrAI CRM & WhatsApp integration service.
 * API docs: https://tayrai.com/
 */
class TayraiService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.tayrai.com/v1';
    private bool   $enabled;

    public function __construct()
    {
        $this->apiKey  = SiteSetting::get('tayrai', 'api_key', '');
        $this->enabled = (bool) SiteSetting::get('tayrai', 'enabled', false);
    }

    /**
     * Create or update a contact in TayrAI CRM.
     */
    public function upsertContact(string $name, string $phone, string $email = ''): ?string
    {
        if (! $this->isReady()) {
            return null;
        }

        try {
            $response = $this->post('/contacts', [
                'name'  => $name,
                'phone' => $this->normalizePhone($phone),
                'email' => $email,
            ]);

            if ($response->successful()) {
                return $response->json('data.id');
            }

            Log::warning('TayrAI upsertContact failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('TayrAI upsertContact exception', ['message' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Create a lead in TayrAI CRM pipeline.
     */
    public function createLead(Lead $lead): ?string
    {
        if (! $this->isReady()) {
            return null;
        }

        try {
            $payload = [
                'contact_id' => $lead->tayrai_contact_id,
                'name'       => $lead->name,
                'phone'      => $this->normalizePhone($lead->phone ?? ''),
                'email'      => $lead->email ?? '',
                'source'     => $lead->source,
                'notes'      => $lead->message,
                'service'    => $lead->service?->name ?? '',
                'metadata'   => [
                    'lead_id'      => $lead->id,
                    'utm_source'   => $lead->utm_source,
                    'utm_medium'   => $lead->utm_medium,
                    'utm_campaign' => $lead->utm_campaign,
                ],
            ];

            $response = $this->post('/leads', $payload);

            if ($response->successful()) {
                return $response->json('data.id');
            }

            Log::warning('TayrAI createLead failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('TayrAI createLead exception', ['message' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Send a WhatsApp message via TayrAI.
     */
    public function sendWhatsApp(string $phone, string $message): bool
    {
        if (! $this->isReady()) {
            return false;
        }

        try {
            $response = $this->post('/whatsapp/send', [
                'phone'   => $this->normalizePhone($phone),
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('TayrAI sendWhatsApp exception', ['message' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Update lead status in TayrAI.
     */
    public function updateLeadStatus(string $tayraiLeadId, string $status): bool
    {
        if (! $this->isReady() || ! $tayraiLeadId) {
            return false;
        }

        try {
            $response = $this->patch("/leads/{$tayraiLeadId}", [
                'status' => $status,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('TayrAI updateLeadStatus exception', ['message' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Verify webhook signature from TayrAI.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = SiteSetting::get('tayrai', 'webhook_secret', '');
        if (! $secret) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    private function post(string $endpoint, array $data): Response
    {
        return Http::withToken($this->apiKey)
            ->timeout(15)
            ->post($this->baseUrl . $endpoint, $data);
    }

    private function patch(string $endpoint, array $data): Response
    {
        return Http::withToken($this->apiKey)
            ->timeout(15)
            ->patch($this->baseUrl . $endpoint, $data);
    }

    private function normalizePhone(string $phone): string
    {
        // Remove everything except digits and leading +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        // Ensure country code format for Colombia if no prefix
        if (strlen($phone) === 10 && str_starts_with($phone, '3')) {
            $phone = '+57' . $phone;
        }
        return $phone;
    }

    private function isReady(): bool
    {
        return $this->enabled && ! empty($this->apiKey);
    }
}
