<?php

namespace App\Http\Controllers;

use App\Models\AudioSlice;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    function index()
    {
        $menu = config('settings.menus.dashboard');

        $stats = [
            'inventory_count' => Inventory::count(),
            'slice_count' => AudioSlice::count(),
        ];

        return Inertia::render('Dashboard', compact('menu', 'stats'));
    }
}
