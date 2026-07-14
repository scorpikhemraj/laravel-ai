<?php

namespace Khemraj\LaravelDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Khemraj\LaravelDashboard\Services\ModuleRegistry;
use Khemraj\LaravelDashboard\Services\FieldDiscovery;
use Illuminate\Http\JsonResponse;

class ModuleDiscoveryController extends Controller
{
    protected ModuleRegistry $registry;
    protected FieldDiscovery $discovery;

    public function __construct(ModuleRegistry $registry, FieldDiscovery $discovery)
    {
        $this->registry = $registry;
        $this->discovery = $discovery;
    }

    public function getModules(): JsonResponse
    {
        $modules = $this->registry->all();
        $formatted = [];

        foreach ($modules as $slug => $config) {
            $class = $config['model'] ?? null;
            if (!$class) {
                continue;
            }
            $formatted[] = [
                'slug' => $slug,
                'class' => $class,
                'name' => $config['name'] ?? class_basename($class)
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    public function getFields(Request $request, string $module): JsonResponse
    {
        $class = $this->registry->resolve($module);

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => "Module '{$module}' is not registered."
            ], 404);
        }

        try {
            $fields = $this->discovery->discover($class);
            $relations = $this->discovery->discoverRelations($class);

            return response()->json([
                'success' => true,
                'data' => [
                    'fields' => $fields,
                    'relations' => $relations
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to discover fields for module '{$module}': " . $e->getMessage()
            ], 500);
        }
    }
}
