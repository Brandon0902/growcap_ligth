<?php

namespace App\Http\Controllers\Investments;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InvestmentPageController extends Controller
{
    public function index(): View
    {
        return view('inversion.index', [
            'plans' => [],
            'plansError' => null,
        ]);
    }
}
