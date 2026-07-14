<?php

namespace Khemraj\LaravelDashboard\Tests\Unit;

use Khemraj\LaravelDashboard\Tests\TestCase;
use Khemraj\LaravelDashboard\Services\ModuleRegistry;
use Khemraj\LaravelDashboard\Services\ConditionApplier;
use Khemraj\LaravelDashboard\Services\AggregateBuilder;
use Khemraj\LaravelDashboard\Services\ChartFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up testing tables
        Schema::dropIfExists('test_models');
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function test_module_registry_registers_and_resolves_modules()
    {
        $registry = new ModuleRegistry();
        $registry->register('test-model', TestModel::class);

        $this->assertEquals(TestModel::class, $registry->resolve('test-model'));
        $this->assertNull($registry->resolve('non-existent'));
        $this->assertArrayHasKey('test-model', $registry->all());
    }

    public function test_aggregate_builder_applies_aggregates()
    {
        $builder = new AggregateBuilder();
        
        $query = TestModel::query();
        $builder->apply($query, 'amount', 'sum', 'total_amount');
        
        $sql = $query->toSql();
        $this->assertMatchesRegularExpression('/sum\(.*?amount.*?\)\s+as\s+.*?total_amount.*?/i', $sql);
    }

    public function test_condition_applier_filters_queries()
    {
        $applier = new ConditionApplier();
        
        // Populate records
        TestModel::create(['status' => 'pending', 'amount' => 100]);
        TestModel::create(['status' => 'approved', 'amount' => 200]);

        $query = TestModel::query();
        $conditions = [
            ['field' => 'status', 'operator' => '=', 'value' => 'approved']
        ];
        
        $applier->apply($query, $conditions);
        
        $this->assertEquals(1, $query->count());
        $this->assertEquals(200, $query->first()->amount);
    }

    public function test_chart_formatter_formats_data()
    {
        $formatter = new ChartFormatter();
        $rawRows = [
            ['x_axis' => 'Jan', 'aggregate_value' => 100],
            ['x_axis' => 'Feb', 'aggregate_value' => 150]
        ];

        $option = $formatter->format($rawRows, 'bar', ['stacked' => false]);

        $this->assertArrayHasKey('xAxis', $option);
        $this->assertArrayHasKey('series', $option);
        $this->assertEquals('category', $option['xAxis']['type']);
        $this->assertEquals('bar', $option['series'][0]['type']);
        $this->assertEquals([100, 150], $option['series'][0]['data']);
    }
}

class TestModel extends Model
{
    protected $table = 'test_models';
    protected $fillable = ['status', 'amount'];
}
