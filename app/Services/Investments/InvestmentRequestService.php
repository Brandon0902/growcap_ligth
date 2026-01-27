<?php

namespace App\Services\Investments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvestmentRequestService
{
    public function submit(array $payload, ?string $tokenOverride = null, string $tokenType = 'Bearer'): array
    {
        return $this->post('investment', $payload, $tokenOverride, $tokenType);
    }

    public function plans(): array
    {
        return $this->get('investment_plans');
    }

    private function get(string $endpointKey, ?string $tokenOverride = null, string $tokenType = 'Bearer'): array
    {
        $url = $this->buildUrl($endpointKey);
        $token = $tokenOverride ?: config('growcap.token');

        try {
            $response = Http::acceptJson()
                ->withToken($token, $tokenType)
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

        $errors = $this->extractErrors($response->json());

        return [
            'success' => false,
            'message' => $this->buildErrorMessage($response->json(), 'No se pudieron cargar los planes.'),
            'data' => $response->json(),
            'status' => $response->status(),
            'errors' => $errors,
        ];
    }

    private function post(
        string $endpointKey,
        array $payload,
        ?string $tokenOverride = null,
        string $tokenType = 'Bearer'
    ): array {
        $url = $this->buildUrl($endpointKey);
        $token = $tokenOverride ?: config('growcap.token');

        try {
            $response = Http::acceptJson()
                ->withToken($token, $tokenType)
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

        $errors = $this->extractErrors($response->json());

        return [
            'success' => false,
            'message' => $this->buildErrorMessage($response->json(), 'Ocurrió un error al enviar la solicitud.'),
            'data' => $response->json(),
            'status' => $response->status(),
            'errors' => $errors,
        ];
    }

    private function buildUrl(string $endpointKey): string
    {
        $baseUrl = rtrim((string) config('growcap.base_url'), '/');
        $endpoint = (string) config("growcap.endpoints.{$endpointKey}");

        // Evita terminar con /api/api/... cuando base_url ya incluye /api
        if ($baseUrl !== '' && str_ends_with($baseUrl, '/api') && str_starts_with($endpoint, '/api/')) {
            $endpoint = substr($endpoint, 4); // quita el primer "/api"
        }

        return $baseUrl . '/' . ltrim($endpoint, '/');
    }

    private function buildErrorMessage($json, string $fallback): string
    {
        $message = data_get($json, 'message', $fallback);
        $errors = $this->extractErrors($json);

        if (empty($errors)) {
            return $message;
        }

        return $message;
    }

    private function extractErrors($json): array
    {
        if (!is_array($json)) {
            return [];
        }

        $errors = [];
        foreach (['errors', 'detalles'] as $key) {
            $values = data_get($json, $key);
            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $field => $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $errors[] = is_string($message) ? $message : json_encode($message);
                    }
                } else {
                    $errors[] = is_string($messages) ? $messages : json_encode($messages);
                }
            }
        }

        return array_values(array_filter($errors, fn ($value) => $value !== null && $value !== ''));
    }
}
