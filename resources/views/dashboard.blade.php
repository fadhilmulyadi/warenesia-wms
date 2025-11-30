@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-header')
    <div class="space-y-1">
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500">No dashboard available for your role yet.</p>
    </div>
@endsection

@section('content')
    <x-dashboard.card>
        <p class="text-sm text-slate-600">Please contact the administrator to enable a dashboard for your role.</p>
    </x-dashboard.card>
@endsection
