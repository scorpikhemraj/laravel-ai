<?php

use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

it('requires authentication to access events API', function () {
    $this->getJson('/api/events')->assertUnauthorized();
    $this->postJson('/api/events', [])->assertUnauthorized();
    
    $event = Event::create([
        'user_id' => $this->user->id,
        'title' => 'Temp Event',
        'event_date' => '2026-07-09',
    ]);
    
    $this->deleteJson("/api/events/{$event->id}")->assertUnauthorized();
});

it('can retrieve only the authenticated user\'s events', function () {
    $myEvent = Event::create([
        'user_id' => $this->user->id,
        'title' => 'My Event',
        'event_date' => '2026-07-09',
        'event_time' => '14:30',
        'color' => '#4f46e5',
    ]);

    $otherEvent = Event::create([
        'user_id' => $this->otherUser->id,
        'title' => 'Other Event',
        'event_date' => '2026-07-09',
        'event_time' => '15:00',
        'color' => '#10b981',
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/events');
    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data',
            'message',
        ]);

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($myEvent->id)
        ->and($response->json('data.0.title'))->toBe('My Event');
});

it('can validate and store an event', function () {
    $data = [
        'title' => 'New Event',
        'description' => 'Test event description',
        'event_date' => '2026-07-10',
        'event_time' => '09:00',
        'color' => '#f59e0b',
    ];

    $response = $this->actingAs($this->user)->postJson('/api/events', $data);
    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'New Event');

    $this->assertDatabaseHas('events', [
        'user_id' => $this->user->id,
        'title' => 'New Event',
        'event_date' => '2026-07-10',
        'event_time' => '09:00',
        'color' => '#f59e0b',
    ]);
});

it('fails storing event with invalid date format', function () {
    $data = [
        'title' => 'Invalid Event',
        'event_date' => '07/10/2026', // invalid Y-m-d format
    ];

    $response = $this->actingAs($this->user)->postJson('/api/events', $data);
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['event_date']);
});

it('can delete user\'s own event', function () {
    $event = Event::create([
        'user_id' => $this->user->id,
        'title' => 'To Delete',
        'event_date' => '2026-07-09',
    ]);

    $response = $this->actingAs($this->user)->deleteJson("/api/events/{$event->id}");
    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('events', [
        'id' => $event->id,
    ]);
});

it('cannot delete another user\'s event', function () {
    $event = Event::create([
        'user_id' => $this->otherUser->id,
        'title' => 'Cannot Delete',
        'event_date' => '2026-07-09',
    ]);

    $response = $this->actingAs($this->user)->deleteJson("/api/events/{$event->id}");
    $response->assertForbidden();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
    ]);
});
