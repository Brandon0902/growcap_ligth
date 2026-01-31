<?php

namespace App\Services\Loans;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoanRequestService
{
    public function submit(
        array $payload,
        array $files = [],
        ?string $tokenOverride = null,
        string $tokenType = 'Bearer'
    ): array {
        return $this->post('loan', $payload, $files, $tokenOverride, $tokenType);
    }

    public function plans(?string $tokenOverride = null, string $tokenType = 'Bearer'): array
    {
        return $this->get('loan_plans', $tokenOverride, $tokenType);
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

        return [
            'success' => false,
            'message' => $this->buildErrorMessage($response->json(), 'No se pudieron cargar los planes.'),
            'data' => $response->json(),
            'status' => $response->status(),
            'errors' => $this->extractErrors($response->json()),
        ];
    }

    private function post(
        string $endpointKey,
        array $payload,
        array $files = [],
        ?string $tokenOverride = null,
        string $tokenType = 'Bearer'
    ): array {
        $url = $this->buildUrl($endpointKey);
        $token = $tokenOverride ?: config('growcap.token');

        try {
            $request = Http::acceptJson()
                ->withToken($token, $tokenType)
                ->timeout((int) config('growcap.timeout'));

            foreach ($files as $field => $file) {
                if ($file instanceof UploadedFile) {
                    $request = $request->attach(
                        $field,
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName()
                    );
                }
            }

            $response = $request->post($url, $payload);
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
            'message' => $this->buildErrorMessage($response->json(), 'Ocurrió un error al enviar la solicitud.'),
            'data' => $response->json(),
            'status' => $response->status(),
            'errors' => $this->extractErrors($response->json()),
        ];
    }

    private function buildUrl(string $endpointKey): string
    {
        $baseUrl = rtrim((string) config('growcap.base_url'), '/');
        $endpoint = (string) config("growcap.endpoints.{$endpointKey}");

        if ($baseUrl !== '' && str_ends_with($baseUrl, '/api') && str_starts_with($endpoint, '/api/')) {
            $endpoint = substr($endpoint, 4);
        }

        return $baseUrl . '/' . ltrim($endpoint, '/');
    }

    private function buildErrorMessage($json, string $fallback): string
    {
        $message = data_get($json, 'message', data_get($json, 'error', $fallback));
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
        $singleError = data_get($json, 'error');
        if (is_string($singleError) && $singleError !== '') {
            $errors[] = $singleError;
        }
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
