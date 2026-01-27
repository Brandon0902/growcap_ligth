<?php

namespace App\Http\Controllers\Investments;

use App\Http\Controllers\Controller;
use App\Services\Investments\InvestmentRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvestmentRequestController extends Controller
{
    public function store(Request $request, InvestmentRequestService $service): RedirectResponse
    {
        $data = $request->validate([
            'id_activo' => ['required', 'integer', 'min:1'],
            'cantidad' => ['required', 'numeric', 'min:1'],
            'tiempo' => ['nullable', 'integer', 'min:1'],
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
