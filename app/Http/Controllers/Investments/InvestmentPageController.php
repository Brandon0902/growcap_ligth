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
        $apiToken = (string) config('growcap.token');

        if ($apiToken !== '') {
            $response = $service->plans();

            if ($response['success'] ?? false) {
                $plans = data_get($response, 'data.data', []);
            } else {
                $plansError = $response['message'] ?? 'No se pudieron cargar los planes.';
                $plansErrors = $response['errors'] ?? [];
            }
        }

        return view('inversion.index', [
            'plans' => $plans,
            'plansError' => $plansError,
            'plansErrors' => $plansErrors,
        ]);
    }
}
