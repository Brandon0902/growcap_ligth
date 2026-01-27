<?php

namespace App\Services\Investments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class InvestmentRequestService
{
    public function submit(array $payload): array
    {
        return $this->post('investment', $payload);
    }

    public function plans(): array
    {
        return $this->get('investment_plans');
    }

    private function get(string $endpointKey): array
    {
        $baseUrl = rtrim(config('growcap.base_url'), '/');
        $endpoint = config("growcap.endpoints.{$endpointKey}");
        $url = $baseUrl . '/' . ltrim($endpoint, '/');
        $token = config('growcap.token');

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout((int) config('growcap.timeout'))
                ->get($url);
        } catch (ConnectionException $exception) {
            Log::error('Growcap API connection failed (GET)', [
                'endpoint' => $endpointKey,
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'No se pudo conectar con la API de Growcap. Intenta nuevamente.',
            ];
        }

        if ($response->successful()) {
            Log::info('Growcap API request succeeded (GET)', [
                'endpoint' => $endpointKey,
                'url' => $url,
                'status' => $response->status(),
            ]);
            return [
                'success' => true,
                'message' => data_get($response->json(), 'message'),
                'data' => $response->json(),
            ];
        }

        Log::warning('Growcap API request failed (GET)', [
            'endpoint' => $endpointKey,
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'success' => false,
            'message' => data_get($response->json(), 'message', 'No se pudieron cargar los planes.'),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    private function post(string $endpointKey, array $payload): array
    {
        $baseUrl = rtrim(config('growcap.base_url'), '/');
        $endpoint = config("growcap.endpoints.{$endpointKey}");
        $url = $baseUrl . '/' . ltrim($endpoint, '/');
        $token = config('growcap.token');

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout((int) config('growcap.timeout'))
                ->post($url, $payload);
        } catch (ConnectionException $exception) {
            Log::error('Growcap API connection failed (POST)', [
                'endpoint' => $endpointKey,
                'url' => $url,
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);
            return [
                'success' => false,
                'message' => 'No se pudo conectar con la API de Growcap. Intenta nuevamente.',
            ];
        }

        if ($response->successful()) {
            Log::info('Growcap API request succeeded (POST)', [
                'endpoint' => $endpointKey,
                'url' => $url,
                'status' => $response->status(),
            ]);
            return [
                'success' => true,
                'message' => data_get($response->json(), 'message', 'Solicitud enviada correctamente.'),
                'data' => $response->json(),
            ];
        }

        Log::warning('Growcap API request failed (POST)', [
            'endpoint' => $endpointKey,
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
            'payload' => $payload,
        ]);

        return [
            'success' => false,
            'message' => data_get($response->json(), 'message', 'Ocurrió un error al enviar la solicitud.'),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }
}
