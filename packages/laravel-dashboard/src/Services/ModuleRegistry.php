<?php

namespace Khemraj\LaravelDashboard\Services;

class ModuleRegistry
{
    protected array $modules = [];

    public function __construct()
    {
        $configModules = config('dashboard.modules', []);
        $discovered = $this->discoverModels();

        $this->modules = [];
        foreach ($discovered as $slug => $className) {
            $this->modules[$slug] = [
                'model' => $className,
                'name' => class_basename($className),
            ];
        }

        foreach ($configModules as $key => $config) {
            if (is_string($config)) {
                $config = ['model' => $config];
            }
            $this->modules[$key] = array_merge($this->modules[$key] ?? [], $config);
        }
    }

    protected function discoverModels(): array
    {
        $models = [];
        if (!function_exists('app_path')) {
            return [];
        }

        $modelDir = app_path('Models');
        if (!is_dir($modelDir)) {
            $modelDir = app_path();
            if (!is_dir($modelDir)) {
                return [];
            }
        }

        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($modelDir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $filePath = $file->getPathname();
                $content = @file_get_contents($filePath);
                if (!$content) {
                    continue;
                }

                $namespace = '';
                if (preg_match('/namespace\s+([^;]+);/i', $content, $matches)) {
                    $namespace = trim($matches[1]);
                }

                $className = '';
                if (preg_match('/class\s+([a-zA-Z0-9_]+)/i', $content, $matches)) {
                    $className = trim($matches[1]);
                }

                if ($className) {
                    $fqcn = $namespace ? $namespace . '\\' . $className : $className;
                    
                    if (class_exists($fqcn)) {
                        $reflector = new \ReflectionClass($fqcn);
                        if ($reflector->isSubclassOf(\Illuminate\Database\Eloquent\Model::class) && !$reflector->isAbstract()) {
                            if (str_contains($fqcn, 'Khemraj\\LaravelDashboard')) {
                                continue;
                            }
                            
                            $slug = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $reflector->getShortName()));
                            $models[$slug] = $fqcn;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore scan errors
        }

        return $models;
    }

    public function register(string $key, array|string $config): void
    {
        if (is_string($config)) {
            $config = ['model' => $config];
        }
        $this->modules[$key] = $config;
    }

    public function all(): array
    {
        return $this->modules;
    }

    public function get(string $key): ?array
    {
        return $this->modules[$key] ?? null;
    }

    public function getModel(string $key): ?string
    {
        return $this->modules[$key]['model'] ?? null;
    }

    public function resolve(string $modelOrSlug): ?string
    {
        if (class_exists($modelOrSlug)) {
            return $modelOrSlug;
        }

        return $this->getModel($modelOrSlug);
    }
}
