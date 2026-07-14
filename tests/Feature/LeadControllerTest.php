<?php

use App\Models\Lead;
use App\Models\User;

beforeEach(function () {
    // Ensure we have users for the lead model's user_id foreign key constraint
    User::factory()->count(3)->create();
});

it('can list leads with pagination, search, and sorting', function () {
    Lead::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'email' => 'alice@example.com',
        'status' => 'new',
    ]);

    Lead::factory()->create([
        'first_name' => 'Bob',
        'last_name' => 'Jones',
        'email' => 'bob@example.com',
        'status' => 'contacted',
    ]);

    // Test list all
    $response = $this->getJson('/api/leads');
    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'total',
        ]);

    expect($response->json('total'))->toBe(2);

    // Test search filter
    $searchResponse = $this->getJson('/api/leads?search=Alice');
    $searchResponse->assertOk();
    expect($searchResponse->json('data'))->toHaveCount(1)
        ->and($searchResponse->json('data.0.first_name'))->toBe('Alice');

    // Test status/search parameters
    $noMatchResponse = $this->getJson('/api/leads?search=XYZ');
    $noMatchResponse->assertOk();
    expect($noMatchResponse->json('data'))->toBeEmpty();
});

it('can create a lead', function () {
    $user = User::first();
    $data = [
        'first_name' => 'Charlie',
        'last_name' => 'Brown',
        'email' => 'charlie@example.com',
        'phone' => '1234567890',
        'company' => 'Peanuts Inc',
        'status' => 'new',
        'source' => 'website',
        'user_id' => $user->id,
    ];

    $response = $this->postJson('/api/leads', $data);
    $response->assertCreated();

    $this->assertDatabaseHas('leads', [
        'first_name' => 'Charlie',
        'last_name' => 'Brown',
        'email' => 'charlie@example.com',
    ]);
});

it('can update a lead', function () {
    $lead = Lead::factory()->create([
        'first_name' => 'Dave',
        'last_name' => 'Miller',
    ]);

    $updateData = [
        'first_name' => 'David',
        'last_name' => 'Miller',
        'email' => 'david@example.com',
        'status' => 'qualified',
        'source' => 'referral',
    ];

    $response = $this->putJson("/api/leads/{$lead->id}", $updateData);
    $response->assertOk();

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'first_name' => 'David',
    ]);
});

it('can delete a single lead', function () {
    $lead = Lead::factory()->create();

    $response = $this->deleteJson("/api/leads/{$lead->id}");
    $response->assertOk();

    $this->assertDatabaseMissing('leads', [
        'id' => $lead->id,
    ]);
});

it('can bulk delete leads', function () {
    $leads = Lead::factory()->count(3)->create();
    $ids = $leads->pluck('id')->toArray();

    $response = $this->postJson('/api/leads/bulk-delete', [
        'ids' => $ids,
    ]);
    $response->assertOk();

    foreach ($ids as $id) {
        $this->assertDatabaseMissing('leads', [
            'id' => $id,
        ]);
    }
});

it('can filter leads by individual columns', function () {
    Lead::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'email' => 'alice@example.com',
        'status' => 'new',
        'source' => 'website',
    ]);

    Lead::factory()->create([
        'first_name' => 'Bob',
        'last_name' => 'Jones',
        'email' => 'bob@example.com',
        'status' => 'contacted',
        'source' => 'referral',
    ]);

    // Test filter by email
    $response = $this->getJson('/api/leads?filter_email=alice@example.com');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.first_name'))->toBe('Alice');

    // Test filter by status
    $statusResponse = $this->getJson('/api/leads?filter_status=contacted');
    $statusResponse->assertOk();
    expect($statusResponse->json('data'))->toHaveCount(1)
        ->and($statusResponse->json('data.0.first_name'))->toBe('Bob');

    // Test filter by source
    $sourceResponse = $this->getJson('/api/leads?filter_source=website');
    $sourceResponse->assertOk();
    expect($sourceResponse->json('data'))->toHaveCount(1)
        ->and($sourceResponse->json('data.0.first_name'))->toBe('Alice');
});

it('can bulk update leads status, source, or favorite state', function () {
    $leads = Lead::factory()->count(3)->create([
        'status' => 'new',
        'source' => 'website',
        'is_favorite' => false,
    ]);
    $ids = $leads->pluck('id')->toArray();

    // 1. Bulk update status to contacted
    $response = $this->postJson('/api/leads/bulk-update', [
        'ids' => $ids,
        'field' => 'status',
        'value' => 'contacted',
    ]);
    $response->assertOk();

    foreach ($ids as $id) {
        $this->assertDatabaseHas('leads', [
            'id' => $id,
            'status' => 'contacted',
        ]);
    }

    // 2. Bulk update source to referral
    $response = $this->postJson('/api/leads/bulk-update', [
        'ids' => $ids,
        'field' => 'source',
        'value' => 'referral',
    ]);
    $response->assertOk();

    foreach ($ids as $id) {
        $this->assertDatabaseHas('leads', [
            'id' => $id,
            'source' => 'referral',
        ]);
    }

    // 3. Bulk update is_favorite to true
    $response = $this->postJson('/api/leads/bulk-update', [
        'ids' => $ids,
        'field' => 'is_favorite',
        'value' => true,
    ]);
    $response->assertOk();

    foreach ($ids as $id) {
        $this->assertDatabaseHas('leads', [
            'id' => $id,
            'is_favorite' => true,
        ]);
    }
});

it('can sort and filter by the new CRM fields', function () {
    Lead::factory()->create([
        'first_name' => 'Alice',
        'address' => '123 Main St',
        'state' => 'CA',
        'postal_code' => '90210',
        'industry' => 'Technology',
        'annual_revenue' => 5000000.00,
        'number_of_employees' => 50,
        'website' => 'https://alice.com',
        'linkedin_url' => 'https://linkedin.com/in/alice',
        'lead_score' => 85,
        'notes' => 'Important technology client',
    ]);

    Lead::factory()->create([
        'first_name' => 'Bob',
        'address' => '456 Oak Ave',
        'state' => 'NY',
        'postal_code' => '10001',
        'industry' => 'Finance',
        'annual_revenue' => 12000000.00,
        'number_of_employees' => 150,
        'website' => 'https://bob.com',
        'linkedin_url' => 'https://linkedin.com/in/bob',
        'lead_score' => 45,
        'notes' => 'Mid-market finance prospect',
    ]);

    // Test filter by state
    $response = $this->getJson('/api/leads?filter_state=CA');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.first_name'))->toBe('Alice');

    // Test filter by lead_score
    $response = $this->getJson('/api/leads?filter_lead_score=45');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.first_name'))->toBe('Bob');

    // Test sort by annual_revenue ascending
    $response = $this->getJson('/api/leads?sortField=annual_revenue&sortOrder=1');
    $response->assertOk();
    expect($response->json('data.0.first_name'))->toBe('Alice'); // 5M vs 12M

    // Test sort by annual_revenue descending
    $response = $this->getJson('/api/leads?sortField=annual_revenue&sortOrder=-1');
    $response->assertOk();
    expect($response->json('data.0.first_name'))->toBe('Bob'); // 12M vs 5M
});


