<?php

use App\Models\Bug;
use App\Models\User;
use App\Models\Lead;
use App\Models\Opportunity;

it('creates CRM dashboard models and validates relationships', function () {
    $user = User::factory()->create([
        'role' => 'sales_rep',
        'target_revenue' => 100000.00,
        'department' => 'Sales East',
        'commission_rate' => 0.08,
    ]);

    expect($user->role)->toBe('sales_rep')
        ->and($user->target_revenue)->toEqual(100000.00)
        ->and($user->department)->toBe('Sales East')
        ->and($user->commission_rate)->toEqual(0.08);

    $lead = Lead::factory()->create([
        'user_id' => $user->id,
        'status' => 'contacted',
    ]);

    expect($lead->user_id)->toBe($user->id)
        ->and($lead->status)->toBe('contacted')
        ->and($lead->user->id)->toBe($user->id);

    $opportunity = Opportunity::factory()->create([
        'lead_id' => $lead->id,
        'user_id' => $user->id,
        'stage' => 'negotiation',
        'amount' => 50000,
        'probability' => 70,
    ]);

    expect($opportunity->lead_id)->toBe($lead->id)
        ->and($opportunity->user_id)->toBe($user->id)
        ->and($opportunity->stage)->toBe('negotiation')
        ->and($opportunity->amount)->toEqual(50000)
        ->and($opportunity->probability)->toBe(70)
        ->and($opportunity->lead->id)->toBe($lead->id)
        ->and($opportunity->user->id)->toBe($user->id);

    $bug = Bug::factory()->create([
        'reported_by' => $user->id,
        'assigned_to' => $user->id,
        'status' => 'open',
        'severity' => 'high',
        'priority' => 'high',
    ]);

    expect($bug->status)->toBe('open')
        ->and($bug->severity)->toBe('high')
        ->and($bug->priority)->toBe('high')
        ->and($bug->reporter->id)->toBe($user->id)
        ->and($bug->assignee->id)->toBe($user->id);

    expect($user->leads)->toHaveCount(1)
        ->and($user->opportunities)->toHaveCount(1);
});

it('discovers CRM models in the dashboard modules list', function () {
    $response = $this->getJson('/api/dashboard/modules');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $modules = collect($response->json('data'));

    $leadModule = $modules->firstWhere('slug', 'lead');
    expect($leadModule)->not->toBeNull()
        ->and($leadModule['class'])->toBe(Lead::class);

    $opportunityModule = $modules->firstWhere('slug', 'opportunity');
    expect($opportunityModule)->not->toBeNull()
        ->and($opportunityModule['class'])->toBe(Opportunity::class);
});

it('discovers the correct fields for the lead module', function () {
    $response = $this->getJson('/api/dashboard/modules/lead/fields');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'data' => [
                'fields',
                'relations',
            ],
        ]);

    $fields = $response->json('data.fields');
    expect($fields)->toHaveKey('company')
        ->and($fields)->toHaveKey('status')
        ->and($fields)->toHaveKey('source')
        ->and($fields)->toHaveKey('user_id');

    expect($fields['user_id']['type'])->toBe('relation')
        ->and($fields['user_id']['related_model'])->toBe(User::class);
});

it('discovers the correct fields for the opportunity module', function () {
    $response = $this->getJson('/api/dashboard/modules/opportunity/fields');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $fields = $response->json('data.fields');
    expect($fields)->toHaveKey('stage')
        ->and($fields)->toHaveKey('amount')
        ->and($fields)->toHaveKey('probability')
        ->and($fields)->toHaveKey('lead_id')
        ->and($fields)->toHaveKey('user_id');

    expect($fields['lead_id']['type'])->toBe('relation')
        ->and($fields['lead_id']['related_model'])->toBe(Lead::class);

    expect($fields['user_id']['type'])->toBe('relation')
        ->and($fields['user_id']['related_model'])->toBe(User::class);
});
