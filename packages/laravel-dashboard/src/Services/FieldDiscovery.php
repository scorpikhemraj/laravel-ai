<?php

namespace Khemraj\LaravelDashboard\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;

class FieldDiscovery
{
    public function discover(string $modelClass): array
    {
        if (!class_exists($modelClass)) {
            return [];
        }

        /** @var Model $model */
        $model = new $modelClass();
        $table = $model->getTable();

        // Get columns from migration files first, then fallback to database schema
        $columns = $this->discoverFieldsFromMigrations($table);
        if (empty($columns)) {
            $dbColumns = Schema::getColumns($table);
            foreach ($dbColumns as $column) {
                $columns[] = [
                    'name' => $column['name'],
                    'type' => $column['type_name'],
                ];
            }
        }
        
        $casts = $model->getCasts();
        $fillables = $model->getFillable();

        $fields = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $dbType = $column['type'];

            // Skip passwords, sensitive tokens, or secret columns
            if (in_array($name, ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])) {
                continue;
            }

            $type = $this->mapType($name, $dbType, $casts);

            $fields[$name] = [
                'label' => ucwords(str_replace('_', ' ', $name)),
                'type' => $type,
                'is_fillable' => in_array($name, $fillables) || $name === $model->getKeyName() || str_ends_with($name, '_at'),
            ];
        }

        // Auto-discover BelongsTo relations
        $relations = $this->discoverRelations($modelClass);
        foreach ($relations as $relName => $relData) {
            $fields[$relData['foreign_key']] = array_merge($fields[$relData['foreign_key']] ?? [], [
                'type' => 'relation',
                'relation' => $relName,
                'display' => $relData['display_field'],
                'related_model' => $relData['related_model'],
            ]);
        }

        return $fields;
    }

    protected function discoverFieldsFromMigrations(string $tableName): array
    {
        $columns = [];
        if (!function_exists('database_path')) {
            return [];
        }

        $migrationsDir = database_path('migrations');
        if (!is_dir($migrationsDir)) {
            return [];
        }

        $files = glob($migrationsDir . '/*.php');
        if (!$files) {
            return [];
        }

        sort($files);

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if (!$content) {
                continue;
            }

            $tablePattern = '/Schema::(create|table)\(\s*[\'"]' . preg_quote($tableName, '/') . '[\'"]/i';
            if (!preg_match($tablePattern, $content)) {
                continue;
            }

            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '//') || str_starts_with($line, '/*') || str_starts_with($line, '*')) {
                    continue;
                }

                if (preg_match('/\$table->([a-zA-Z0-9_]+)\(\s*[\'"]([a-zA-Z0-9_]+)[\'"]/', $line, $matches)) {
                    $type = strtolower($matches[1]);
                    $name = $matches[2];
                    
                    if (in_array($type, ['index', 'unique', 'primary', 'foreign', 'dropcolumn'])) {
                        continue;
                    }

                    $columns[$name] = [
                        'name' => $name,
                        'type' => $type,
                    ];
                }
                
                if (preg_match('/\$table->id\(\s*[\'"]?([a-zA-Z0-9_]*)[\'"]?\s*\)/i', $line, $matches)) {
                    $name = $matches[1] ?: 'id';
                    $columns[$name] = [
                        'name' => $name,
                        'type' => 'integer',
                    ];
                }

                if (str_contains($line, '$table->timestamps')) {
                    $columns['created_at'] = [
                        'name' => 'created_at',
                        'type' => 'timestamp',
                    ];
                    $columns['updated_at'] = [
                        'name' => 'updated_at',
                        'type' => 'timestamp',
                    ];
                }

                if (str_contains($line, '$table->softDeletes')) {
                    $columns['deleted_at'] = [
                        'name' => 'deleted_at',
                        'type' => 'timestamp',
                    ];
                }

                if (str_contains($line, '$table->rememberToken')) {
                    $columns['remember_token'] = [
                        'name' => 'remember_token',
                        'type' => 'string',
                    ];
                }
            }
        }

        return array_values($columns);
    }

    protected function mapType(string $name, string $dbType, array $casts): string
    {
        if (isset($casts[$name])) {
            $cast = $casts[$name];
            if (in_array($cast, ['int', 'integer', 'real', 'float', 'double'])) {
                return 'integer';
            }
            if (in_array($cast, ['bool', 'boolean'])) {
                return 'boolean';
            }
            if (in_array($cast, ['date', 'datetime']) || str_contains($cast, 'datetime') || str_contains($cast, 'date')) {
                return 'date';
            }
            if ($cast === 'array' || $cast === 'json' || $cast === 'object') {
                return 'json';
            }
        }

        $dbType = strtolower($dbType);
        if (in_array($dbType, ['int', 'integer', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double', 'numeric'])) {
            return 'integer';
        }
        if (in_array($dbType, ['boolean', 'bool'])) {
            return 'boolean';
        }
        if (in_array($dbType, ['date', 'time', 'timestamp', 'datetime'])) {
            return 'date';
        }
        if (in_array($dbType, ['json', 'jsonb'])) {
            return 'json';
        }

        return 'string';
    }

    public function discoverRelations(string $modelClass): array
    {
        $relations = [];
        $reflection = new ReflectionClass($modelClass);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0 || $method->getDeclaringClass()->getName() !== $modelClass) {
                continue;
            }

            try {
                $returnType = $method->getReturnType();
                $isRelation = false;
                
                if ($returnType && method_exists($returnType, 'getName')) {
                    $typeName = $returnType->getName();
                    if (str_contains($typeName, 'Relations\\BelongsTo')) {
                        $isRelation = true;
                    }
                }

                if ($isRelation || (str_contains($method->getDocComment() ?: '', '@return \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo'))) {
                    // Instantiate model and call method
                    $model = new $modelClass();
                    $relation = $model->{$method->getName()}();
                    
                    if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $relatedModel = get_class($relation->getRelated());
                        
                        // Guess display field: 'name' or 'title' if present
                        $displayField = 'id';
                        if (Schema::hasColumn((new $relatedModel)->getTable(), 'name')) {
                            $displayField = 'name';
                        } elseif (Schema::hasColumn((new $relatedModel)->getTable(), 'title')) {
                            $displayField = 'title';
                        }

                        $relations[$method->getName()] = [
                            'foreign_key' => $relation->getForeignKeyName(),
                            'related_model' => $relatedModel,
                            'display_field' => $displayField,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore failure on discovery
            }
        }

        return $relations;
    }
}
