<?php

use App\Events\MessageSent;
use App\Http\Livewire\Chat;
use App\Models\Chat as ChatModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('requires authentication to access chat page', function () {
    $this->get(route('chat'))
        ->assertRedirect(route('login'));
});

it('loads the chat page for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('chat'))
        ->assertOk();
});

it('allows starting a direct chat', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Chat::class)
        ->call('startDirectChat', $otherUser->id)
        ->assertSet('activeChatId', function ($id) use ($user, $otherUser) {
            $chat = ChatModel::find($id);
            return $chat && !$chat->is_group && $chat->users->contains($user->id) && $chat->users->contains($otherUser->id);
        });
});

it('allows creating a group chat', function () {
    $user = User::factory()->create();
    $otherUser1 = User::factory()->create();
    $otherUser2 = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Chat::class)
        ->set('newGroupName', 'Test Group')
        ->set('selectedUsersForGroup', [$otherUser1->id, $otherUser2->id])
        ->call('createGroupChat')
        ->assertSet('activeChatId', function ($id) use ($user, $otherUser1, $otherUser2) {
            $chat = ChatModel::find($id);
            return $chat 
                && $chat->is_group 
                && $chat->name === 'Test Group'
                && $chat->users->contains($user->id) 
                && $chat->users->contains($otherUser1->id)
                && $chat->users->contains($otherUser2->id);
        });
});

it('dispatches MessageSent event when sending a message', function () {
    Event::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    
    // Create a chat first
    $chat = ChatModel::create(['is_group' => false]);
    $chat->users()->attach([$user->id, $otherUser->id]);

    $this->actingAs($user);

    Livewire::test(Chat::class, ['activeChatId' => $chat->id])
        ->set('messageText', 'Hello world!')
        ->call('sendMessage');

    Event::assertDispatched(MessageSent::class, function ($event) use ($chat, $user) {
        return $event->message->message === 'Hello world!'
            && $event->message->chat_id === $chat->id
            && $event->message->user_id === $user->id;
    });
});

it('handles file attachments when sending a message', function () {
    Storage::fake('public');
    Event::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    
    $chat = ChatModel::create(['is_group' => false]);
    $chat->users()->attach([$user->id, $otherUser->id]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('test_doc.jpg');

    Livewire::test(Chat::class, ['activeChatId' => $chat->id])
        ->set('attachment', $file)
        ->call('sendMessage');

    // Assert file was stored
    $message = $chat->messages()->first();
    expect($message->attachment_path)->not->toBeNull();
    Storage::disk('public')->assertExists($message->attachment_path);
    expect($message->attachment_name)->toBe('test_doc.jpg');
});
