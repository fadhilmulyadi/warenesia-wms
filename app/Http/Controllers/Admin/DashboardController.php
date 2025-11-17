<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Statistik utama untuk quick links
        $stats = [
            'products'   => Product::count(),
            'categories' => Category::count(),
            'suppliers'  => Supplier::count(),
            'customers'  => Customer::count(),
            'users'      => User::count(),
        ];

        // Stock health
        $stock = [
            'total_skus'   => $stats['products'],
            'low_stock'    => Product::whereColumn('current_stock', '<=', 'min_stock')
                                     ->where('current_stock', '>', 0)
                                     ->count(),
            'out_of_stock' => Product::where('current_stock', 0)->count(),
            'total_on_hand'=> Product::sum('current_stock'),
        ];

        // KPI
        $kpi = [
            'revenue'        => 0,
            'net'            => 0,
            'pending_orders' => 0,
            'due_orders'     => 0,
            'overdue_orders' => 0,
            'inflow'         => 0,
            'outflow'        => 0,
        ];

        return view('admin.dashboard', compact('stats', 'stock', 'kpi'));
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
}
