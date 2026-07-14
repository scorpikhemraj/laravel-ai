<?php

namespace Khemraj\LaravelDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Khemraj\LaravelDashboard\Models\Dashboard;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $dashboards = Dashboard::withCount('tabs')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dashboards
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:dashboards,slug',
            'description' => 'nullable|string',
            'layout_settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $dashboard = Dashboard::create($validated);

        // Auto-create a default tab for new dashboards
        $dashboard->tabs()->create([
            'title' => 'Default Tab',
            'order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard created successfully.',
            'data' => $dashboard->load('tabs')
        ], 201);
    }

    public function show(string $idOrSlug): JsonResponse
    {
        $dashboard = Dashboard::with(['tabs' => function ($query) {
            $query->orderBy('order');
        }, 'tabs.widgets' => function ($query) {
            $query->orderBy('order');
        }, 'tabs.widgets.dataSources.conditions'])
        ->where('id', $idOrSlug)
        ->orWhere('slug', $idOrSlug)
        ->first();

        if (!$dashboard) {
            return response()->json([
                'success' => false,
                'message' => 'Dashboard not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $dashboard
        ]);
    }

    public function update(Request $request, Dashboard $dashboard): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'slug' => 'string|max:255|unique:dashboards,slug,' . $dashboard->id,
            'description' => 'nullable|string',
            'layout_settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $dashboard->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard updated successfully.',
            'data' => $dashboard
        ]);
    }

    public function destroy(Dashboard $dashboard): JsonResponse
    {
        $dashboard->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard deleted successfully.'
        ]);
    }

    public function aiChat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            if (class_exists('App\Ai\Agents\PlaygroundAgent')) {
                $agent = new \App\Ai\Agents\PlaygroundAgent();
                $response = $agent->prompt($request->input('message'));
                return response()->json([
                    'success' => true,
                    'response' => (string) $response,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'AI Playground Agent not found.',
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

