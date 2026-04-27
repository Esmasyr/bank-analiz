<?php

namespace App\Http\Controllers;

use App\Services\AnalysisService;

class DashboardController extends Controller
{
    public function __construct(private AnalysisService $analysis) {}

    public function index()
    {
        $summary     = $this->analysis->getSummary(1, 'last_30');
        $categories  = $this->analysis->getByCategory(1, 'last_30');
        $monthly     = $this->analysis->getMonthlyTrend(1, 6);
        $forecast    = $this->analysis->getForecast(1);
        $anomalies   = $this->analysis->detectAnomalies(1);
        $topCats     = $this->analysis->getTopCategories(1, 5);
        $weekly      = $this->analysis->getWeeklyPattern(1);

        return view('dashboard.index', compact(
            'summary', 'categories', 'monthly',
            'forecast', 'anomalies', 'topCats', 'weekly'
        ));
    }
}
