<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Services\Savings\SavingsRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavingsRequestController extends Controller
{
    public function store(Request $request, SavingsRequestService $service): RedirectResponse
    {
        $data = $request->validate([
            'ahorro_id' => ['required', 'integer', 'min:1'],
            'monto_ahorro' => ['required', 'numeric', 'min:0'],
            'cuota' => ['required', 'numeric', 'min:0'],
            'frecuencia_pago' => ['nullable', 'string', 'max:20'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);

        $token = $request->input('auth_token');
        $tokenType = $request->input('auth_token_type', 'Bearer');

        $result = $service->submit($data, $token, $tokenType);

        return back()->with($this->sessionPayload($result));
    }

    private function sessionPayload(array $result): array
    {
        return [
            'status_type' => ($result['success'] ?? false) ? 'success' : 'error',
            'status_message' => $result['message'] ?? 'No se pudo completar la solicitud.',
            'status_errors' => $result['errors'] ?? [],
        ];
    }
}
