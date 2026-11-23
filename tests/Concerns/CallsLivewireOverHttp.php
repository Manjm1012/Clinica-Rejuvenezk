<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;

/**
 * Petición real a /livewire/update (pasa por el grupo web y los middleware persistentes del panel),
 * usando el snapshot del componente renderizado en la página.
 */
trait CallsLivewireOverHttp
{
    protected function livewireCall(string $url, string $componentSuffix, array $updates, string $method, array $params = []): TestResponse
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
        $snapshot = collect($matches[1])
            ->map(fn (string $raw) => html_entity_decode($raw, ENT_QUOTES))
            ->first(fn (string $json) => str_ends_with(json_decode($json, true)['memo']['name'] ?? '', $componentSuffix));

        $this->assertNotNull($snapshot, "No se encontró el componente {$componentSuffix} en {$url}");

        return $this->withHeaders(['X-Livewire' => 'true'])->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => $updates,
                'calls' => [['path' => '', 'method' => $method, 'params' => $params]],
            ]],
        ]);
    }
}
