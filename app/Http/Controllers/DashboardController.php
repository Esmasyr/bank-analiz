<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $stats = AnalyticsController::getDashboardStats($userId);

        $monthlyData = AnalyticsController::getMonthlyData($userId);

        $recentTransactions = Transaction::where('uploaded_by_user_id', $userId)
            
            ->latest('processed_at')
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'monthlyData', 'recentTransactions'));
    }
}
