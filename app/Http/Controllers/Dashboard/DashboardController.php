<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardMetrics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardMetrics $metrics): View
    {
        return view('dashboard', [
            'metrics' => $metrics->summary(),
        ]);
    }
}
