<?php

namespace App\Services\Investments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class InvestmentRequestService
{
    public function submit(array $payload): array
    {
        return $this->post('investment', $payload);
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
            return [
                'success' => false,
                'message' => 'No se pudo conectar con la API de Growcap. Intenta nuevamente.',
            ];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => data_get($response->json(), 'message', 'Solicitud enviada correctamente.'),
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => data_get($response->json(), 'message', 'Ocurrió un error al enviar la solicitud.'),
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }
}
