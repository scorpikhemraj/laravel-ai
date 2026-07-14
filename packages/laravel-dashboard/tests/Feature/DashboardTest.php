<?php

namespace Khemraj\LaravelDashboard\Tests\Feature;

use Khemraj\LaravelDashboard\Tests\TestCase;
use Khemraj\LaravelDashboard\Models\Dashboard;
use Khemraj\LaravelDashboard\Models\DashboardTab;
use Khemraj\LaravelDashboard\Models\DashboardWidget;
use Khemraj\LaravelDashboard\Tests\Unit\TestModel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Setup test db
        Schema::dropIfExists('test_models');
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });

        // Register the module
        config(['dashboard.modules' => [
            'test-model' => TestModel::class
        ]]);
    }

    public function test_can_create_dashboard_and_auto_creates_default_tab()
    {
        $response = $this->postJson(route('dashboard.dashboards.store'), [
            'title' => 'Executive Analytics',
            'slug' => 'exec-analytics',
            'description' => 'Real-time corporate insights',
            'is_active' => true
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Executive Analytics');

        $this->assertDatabaseHas('dashboards', [
            'slug' => 'exec-analytics'
        ]);

        // Verify default tab was auto-created
        $dashboard = Dashboard::where('slug', 'exec-analytics')->first();
        $this->assertCount(1, $dashboard->tabs);
        $this->assertEquals('Default Tab', $dashboard->tabs->first()->title);
    }

    public function test_can_retrieve_dashboard_details()
    {
        $dashboard = Dashboard::create([
            'title' => 'Operational Overview',
            'slug' => 'ops-overview',
            'is_active' => true
        ]);

        $response = $this->getJson(route('dashboard.dashboards.show', $dashboard->id));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Operational Overview');
    }

    public function test_can_create_widget_with_data_sources_and_conditions()
    {
        $dashboard = Dashboard::create(['title' => 'CRM', 'slug' => 'crm']);
        $tab = $dashboard->tabs()->create(['title' => 'Leads', 'order' => 1]);

        $response = $this->postJson(route('dashboard.widgets.store'), [
            'dashboard_tab_id' => $tab->id,
            'title' => 'Lead Volume by Status',
            'widget_type' => 'bar',
            'grid_position' => ['w' => 6, 'h' => 4],
            'order' => 1,
            'data_sources' => [
                [
                    'module' => 'test-model',
                    'x_axis_field' => 'status',
                    'x_axis_type' => 'field',
                    'y_axis_field' => 'id',
                    'y_axis_aggregate' => 'count',
                    'conditions' => [
                        ['field' => 'status', 'operator' => '!=', 'value' => 'junk']
                    ]
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $widget = DashboardWidget::first();
        $this->assertEquals('Lead Volume by Status', $widget->title);
        $this->assertCount(1, $widget->dataSources);
        $this->assertCount(1, $widget->dataSources->first()->conditions);
    }

    public function test_can_retrieve_widget_formatted_data()
    {
        // Add database records
        TestModel::create(['status' => 'new', 'amount' => 100]);
        TestModel::create(['status' => 'contacted', 'amount' => 200]);

        $dashboard = Dashboard::create(['title' => 'Sales', 'slug' => 'sales']);
        $tab = $dashboard->tabs()->create(['title' => 'Overview', 'order' => 1]);
        
        $widget = $tab->widgets()->create([
            'title' => 'Sales Chart',
            'widget_type' => 'bar'
        ]);

        $source = $widget->dataSources()->create([
            'module' => 'test-model',
            'x_axis_field' => 'status',
            'x_axis_type' => 'field',
            'y_axis_field' => 'amount',
            'y_axis_aggregate' => 'sum'
        ]);

        $response = $this->getJson(route('dashboard.widgets.data', $widget->id));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('widget_id', $widget->id);
    }

    public function test_can_preview_widget_data()
    {
        TestModel::create(['status' => 'new', 'amount' => 100]);
        TestModel::create(['status' => 'contacted', 'amount' => 200]);

        $response = $this->postJson(route('dashboard.widgets.preview'), [
            'module' => 'test-model',
            'widget_type' => 'bar',
            'x_axis_field' => 'status',
            'x_axis_type' => 'field',
            'y_axis_field' => 'amount',
            'y_axis_aggregate' => 'sum',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'formatted' => [
                    'xAxis' => [
                        'type',
                        'data',
                    ],
                    'series'
                ],
                'raw'
            ]);
    }
}
