@extends('layouts.app')

@section('title', 'Overview Dashboard - Warenesia')

@section('page-header')
    <div>
        <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
            Overview dashboard
        </h1>
        <p class="text-xs md:text-sm text-slate-500">
            General Dashboard
        </p>
    </div>
@endsection

@section('content')
    {{-- Bar atas: filter tanggal + manage dashboard --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-slate-600">Date range</label>
            <select
                class="text-sm rounded-lg border border-slate-300 bg-white px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                <option>Today</option>
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>This Month</option>
            </select>
        </div>

        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h8"/>
            </svg>
            Manage dashboard
        </button>
    </div>

    {{-- Contoh dua widget sederhana --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-slate-800">Key performance indicators</h2>
                <span class="text-xs text-slate-400">Widget</span>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-3 text-sm">
                <div>
                    <p class="text-slate-500">Total products</p>
                    <p class="text-xl font-semibold text-slate-900">156</p>
                </div>
                <div>
                    <p class="text-slate-500">Low stock</p>
                    <p class="text-xl font-semibold text-amber-600">12</p>
                </div>
                <div>
                    <p class="text-slate-500">Incoming this month</p>
                    <p class="text-xl font-semibold text-slate-900">23</p>
                </div>
                <div>
                    <p class="text-slate-500">Outgoing this month</p>
                    <p class="text-xl font-semibold text-slate-900">18</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-slate-800">Manage demo data</h2>
                <span class="text-xs text-slate-400">Widget</span>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Your dashboard is currently showing demo data. Later you can link it to real warehouse transactions.
            </p>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-600"
            >
                Clear demo data
            </button>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <h2 class="text-sm font-semibold text-slate-800 mb-3">Quick links</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
            <a href="#" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-center">
                Suppliers<br><span class="text-xs text-slate-500">7</span>
            </a>
            <a href="#" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-center">
                Products<br><span class="text-xs text-slate-500">156</span>
            </a>
            <a href="#" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-center">
                Customers<br><span class="text-xs text-slate-500">4</span>
            </a>
            <a href="#" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-center">
                Incoming<br><span class="text-xs text-slate-500">23</span>
            </a>
            <a href="#" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-center">
                Outgoing<br><span class="text-xs text-slate-500">18</span>
            </a>
        </div>
    </div>
@endsection