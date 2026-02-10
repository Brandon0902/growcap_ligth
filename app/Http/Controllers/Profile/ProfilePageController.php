<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ProfilePageController extends Controller
{
    public function show(Request $request): View
    {
        $token = (string) $request->session()->get('gc_access_token', '');
        $tokenType = (string) $request->session()->get('gc_token_type', 'Bearer');

        $cliente = [];
        $userData = [];
        $errorMessage = null;

        if ($token === '') {
            $errorMessage = 'No encontramos la sesión para consultar tus datos.';
        } else {
            try {
                $response = Http::withToken($token, $tokenType)
                    ->acceptJson()
                    ->get($this->apiUrl('/cliente/mis-datos'));

                if ($response->successful()) {
                    $payload = $response->json() ?? [];
                    $cliente = Arr::wrap($payload['cliente'] ?? []);
                    $userData = Arr::wrap($payload['user_data'] ?? []);
                } else {
                    $errorMessage = Arr::get($response->json() ?? [], 'message', 'No pudimos cargar tus datos en este momento.');
                }
            } catch (ConnectionException|RequestException $exception) {
                $errorMessage = 'No pudimos conectar con el servicio de datos.';
            }
        }

        return view('perfil.show', [
            'cliente' => $cliente,
            'userData' => $userData,
            'errorMessage' => $errorMessage,
        ]);
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('app.backend_api_url'), '/').$path;
    }
}
