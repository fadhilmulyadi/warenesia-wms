<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $today = now()->toDateString();

        $incomingToday = IncomingTransaction::with('supplier')
            ->whereDate('transaction_date', $today)
            ->where('created_by', $userId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $outgoingToday = OutgoingTransaction::whereDate('transaction_date', $today)
            ->where('created_by', $userId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return view('staff.dashboard', compact('incomingToday', 'outgoingToday', 'today'));
    }
}
