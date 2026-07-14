<?php

namespace Khemraj\LaravelDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Khemraj\LaravelDashboard\Models\DashboardTab;
use Khemraj\LaravelDashboard\Models\Dashboard;
use Illuminate\Http\JsonResponse;

class DashboardTabController extends Controller
{
    public function store(Request $request, Dashboard $dashboard): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'integer',
        ]);

        $tab = $dashboard->tabs()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tab created successfully.',
            'data' => $tab
        ], 201);
    }

    public function update(Request $request, DashboardTab $tab): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'order' => 'integer',
        ]);

        $tab->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tab updated successfully.',
            'data' => $tab
        ]);
    }

    public function destroy(DashboardTab $tab): JsonResponse
    {
        $tab->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tab deleted successfully.'
        ]);
    }
}
