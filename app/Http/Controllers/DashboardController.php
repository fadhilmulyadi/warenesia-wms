<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\AdminDashboardService;
use App\Services\Dashboard\ManagerDashboardService;
use App\Services\Dashboard\StaffDashboardService;
use App\Services\Dashboard\SupplierDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        $role = $request->user()?->role;

        return match ($role) {
            'admin' => redirect()->route('dashboard.admin'),
            'manager' => redirect()->route('dashboard.manager'),
            'staff' => redirect()->route('dashboard.staff'),
            'supplier' => redirect()->route('dashboard.supplier'),
            default => view('dashboard'),
        };
    }

    public function admin(AdminDashboardService $service): View
    {
        return view('dashboard.admin', $service->getData());
    }

    public function manager(ManagerDashboardService $service): View
    {
        return view('dashboard.manager', $service->getData());
    }

    public function staff(Request $request, StaffDashboardService $service): View
    {
        return view('dashboard.staff', $service->getData($request->user(), $request));
    }

    public function supplier(Request $request, SupplierDashboardService $service): View
    {
        return view('dashboard.supplier', $service->getData($request->user()));
    }
}
