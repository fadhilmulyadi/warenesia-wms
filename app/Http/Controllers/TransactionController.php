<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'incoming');

        $tabs = [
            'incoming' => 'Barang Masuk',
            'outgoing' => 'Barang Keluar',
            // 'all' => 'Semua',
        ];

        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = 'incoming';
        }

        $incomingTransactions = collect();
        $outgoingTransactions = collect();

        if ($activeTab === 'incoming') {
            $incomingTransactions = \App\Models\IncomingTransaction::with('supplier', 'createdBy')
                ->orderByDesc('transaction_date')->paginate(10);
        } elseif ($activeTab === 'outgoing') {
            $outgoingTransactions = \App\Models\OutgoingTransaction::with('createdBy')
                ->orderByDesc('transaction_date')->paginate(10);
        }

        return view('transactions.index', compact(
            'tabs', 
            'activeTab', 
            'incomingTransactions', 
            'outgoingTransactions'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function getIncomingTransactions(Request $request)
    {
        // Copy logic query dari IncomingTransactionController
        $query = IncomingTransaction::with(['supplier', 'createdBy'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');
            
        // Tambahkan filter pencarian di sini jika perlu
        
        return $query->paginate(10)->withQueryString();
    }

    private function getOutgoingTransactions(Request $request)
    {
        // Copy logic query dari OutgoingTransactionController
        $query = OutgoingTransaction::with(['createdBy'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        return $query->paginate(10)->withQueryString();
    }
}
