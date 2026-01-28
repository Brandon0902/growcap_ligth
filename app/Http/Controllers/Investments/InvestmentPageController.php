<?php

namespace App\Http\Controllers\Investments;

use App\Http\Controllers\Controller;
use App\Services\Investments\InvestmentRequestService;
use Illuminate\View\View;

class InvestmentPageController extends Controller
{
    public function index(InvestmentRequestService $service): View
    {
        $plans = [];
        $plansError = null;
        $plansErrors = [];

        $apiToken = trim((string) config('growcap.token'));

        $tokenInvalid =
            $apiToken === '' ||
            in_array(strtolower($apiToken), ['null', 'undefined'], true);

        if (!$tokenInvalid) {
            $response = $service->plans();

            if ($response['success'] ?? false) {
                $plans = data_get($response, 'data.data', []);
            } else {
                $plansError = $response['message'] ?? 'No se pudieron cargar los planes.';
                $plansErrors = $response['errors'] ?? [];
            }
        } else {
            $plansError = 'No hay token configurado para consumir la API de Growcap.';
        }

        return view('inversion.index', [
            'plans' => $plans,
            'plansError' => $plansError,
            'plansErrors' => $plansErrors,
        ]);
    }
}
