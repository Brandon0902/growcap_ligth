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
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:1'],
            'term_months' => ['required', 'integer', 'min:1'],
            'profile' => ['nullable', 'string', 'max:120'],
        ]);

        $result = $service->submit($data);

        return back()->with($this->sessionPayload($result));
    }

    private function sessionPayload(array $result): array
    {
        return [
            'status_type' => $result['success'] ? 'success' : 'error',
            'status_message' => $result['message'] ?? 'No se pudo completar la solicitud.',
        ];
    }
}
