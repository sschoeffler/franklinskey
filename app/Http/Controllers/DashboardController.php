<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $builds = $user->builds()->with('parts')->orderBy('sort_order')->get();
        $inventory = $user->inventoryItems()->orderBy('name')->get();
        $categories = \App\Models\InventoryItem::categories();

        return view('dashboard', compact('builds', 'inventory', 'categories'));
    }
}
