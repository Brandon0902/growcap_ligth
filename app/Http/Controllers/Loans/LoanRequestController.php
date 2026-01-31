<?php

namespace App\Http\Controllers\Loans;

use App\Http\Controllers\Controller;
use App\Services\Loans\LoanRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoanRequestController extends Controller
{
    public function store(Request $request, LoanRequestService $service): RedirectResponse
    {
        $data = $request->validate([
            'id_activo' => ['required', 'integer', 'min:1'],
            'cantidad' => ['required', 'numeric', 'min:1'],
            'codigo_aval' => [
                'nullable',
                'string',
                'max:50',
                'required_without_all:doc_solicitud_aval,doc_comprobante_domicilio,doc_ine_frente,doc_ine_reverso',
            ],
            'doc_solicitud_aval' => [
                'nullable',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120',
                'required_without:codigo_aval',
            ],
            'doc_comprobante_domicilio' => [
                'nullable',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120',
                'required_without:codigo_aval',
            ],
            'doc_ine_frente' => [
                'nullable',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120',
                'required_without:codigo_aval',
            ],
            'doc_ine_reverso' => [
                'nullable',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120',
                'required_without:codigo_aval',
            ],
        ]);

        $files = [
            'doc_solicitud_aval' => $request->file('doc_solicitud_aval'),
            'doc_comprobante_domicilio' => $request->file('doc_comprobante_domicilio'),
            'doc_ine_frente' => $request->file('doc_ine_frente'),
            'doc_ine_reverso' => $request->file('doc_ine_reverso'),
        ];

        $payload = collect($data)
            ->except(array_keys($files))
            ->toArray();

        $token = $request->input('auth_token');
        $tokenType = $request->input('auth_token_type', 'Bearer');

        $result = $service->submit($payload, $files, $token, $tokenType);

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
