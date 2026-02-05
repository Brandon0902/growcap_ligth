<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ApiSessionLoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'single' => ['sometimes', 'boolean'],
        ]);

        $payload = [
            'email' => $validated['login'],
            'password' => $validated['password'],
            'device' => 'cliente-web',
            'single' => $request->boolean('single'),
        ];

        try {
            $response = Http::acceptJson()->post($this->apiUrl('/auth/login'), $payload);
        } catch (\Exception $exception) {
            return back()
                ->withErrors(['login' => 'No pudimos contactar el servicio de autenticación. Intenta más tarde.'])
                ->withInput($request->only('login', 'single'));
        }

        if ($response->successful()) {
            $data = $response->json() ?? [];

            $accessToken = Arr::get($data, 'access_token') ?? Arr::get($data, 'data.access_token');
            $tokenType = Arr::get($data, 'token_type') ?? Arr::get($data, 'data.token_type') ?? 'Bearer';
            $user = Arr::get($data, 'user') ?? Arr::get($data, 'data.user');

            if (! $accessToken) {
                return back()
                    ->withErrors(['login' => 'No pudimos iniciar sesión. Intenta de nuevo.'])
                    ->withInput($request->only('login', 'single'));
            }

            $request->session()->put([
                'gc_token_type' => $tokenType,
                'gc_access_token' => $accessToken,
                'gc_user' => $user,
            ]);

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        if ($response->status() === 422) {
            $errors = $this->formatValidationErrors($response->json() ?? []);

            return back()
                ->withErrors($errors)
                ->withInput($request->only('login', 'single'));
        }

        return back()
            ->withErrors(['login' => 'Credenciales inválidas o servicio no disponible.'])
            ->withInput($request->only('login', 'single'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $accessToken = $request->session()->get('gc_access_token');

        if ($accessToken) {
            $tokenType = $request->session()->get('gc_token_type', 'Bearer');

            try {
                Http::withToken($accessToken, $tokenType)
                    ->acceptJson()
                    ->post($this->apiUrl('/auth/logout'));
            } catch (\Exception $exception) {
                // Ignore logout failures and continue.
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('app.backend_api_url'), '/').$path;
    }

    /**
     * @return array<string, string>
     */
    private function formatValidationErrors(array $payload): array
    {
        $errors = Arr::get($payload, 'errors', []);

        if (! is_array($errors)) {
            return ['login' => 'No pudimos iniciar sesión. Verifica tus datos.'];
        }

        $formatted = [];

        foreach ($errors as $field => $messages) {
            $key = $field === 'email' ? 'login' : $field;
            $message = is_array($messages) ? (string) Arr::first($messages) : (string) $messages;
            $formatted[$key] = $message;
        }

        return $formatted ?: ['login' => 'No pudimos iniciar sesión. Verifica tus datos.'];
    }
}
