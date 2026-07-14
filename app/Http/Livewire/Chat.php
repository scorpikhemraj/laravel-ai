<?php

namespace App\Http\Livewire;

use App\Events\MessageSent;
use App\Models\Chat as ChatModel;
use App\Models\Message;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Chat extends Component
{
    use WithFileUploads;

    public $activeChatId = null;
    public $messageText = '';
    public $attachment = null;
    
    // Group creation properties
    public $newGroupName = '';
    public $selectedUsersForGroup = [];
    public $groupModalOpen = false;

    // Search properties
    public $searchQuery = '';

    protected $queryString = ['activeChatId'];

    public function mount()
    {
        // Select the first chat if available and activeChatId is not set
        if (!$this->activeChatId) {
            $firstChat = auth()->user()->chats()->latest('updated_at')->first();
            if ($firstChat) {
                $this->activeChatId = $firstChat->id;
            }
        }
    }

    /**
     * Get the active chat instance.
     */
    public function getActiveChatProperty()
    {
        if (!$this->activeChatId) {
            return null;
        }

        return auth()->user()->chats()->with(['users', 'messages.user'])->find($this->activeChatId);
    }

    /**
     * Get all chats the user belongs to.
     */
    public function getChatsProperty()
    {
        return auth()->user()->chats()
            ->with(['users', 'latestMessage.user'])
            ->latest('updated_at')
            ->get();
    }

    /**
     * Get other users in the system.
     */
    public function getUsersProperty()
    {
        $query = User::where('id', '!=', auth()->id());

        if (trim($this->searchQuery) !== '') {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return $query->take(15)->get();
    }

    /**
     * Select a chat.
     */
    public function selectChat($chatId)
    {
        $this->activeChatId = $chatId;
        $this->messageText = '';
        $this->attachment = null;
        
        $this->dispatch('chat-selected', chatId: $chatId);
    }

    /**
     * Start a direct 1-on-1 chat.
     */
    public function startDirectChat($otherUserId)
    {
        $authUserId = auth()->id();

        // Check if direct chat already exists
        $existingChat = ChatModel::where('is_group', false)
            ->whereHas('users', function ($q) use ($authUserId) {
                $q->where('users.id', $authUserId);
            })
            ->whereHas('users', function ($q) use ($otherUserId) {
                $q->where('users.id', $otherUserId);
            })
            ->first();

        if ($existingChat) {
            $this->selectChat($existingChat->id);
            return;
        }

        // Create new direct chat
        $chat = ChatModel::create([
            'is_group' => false,
        ]);

        $chat->users()->attach([$authUserId, $otherUserId]);

        $this->selectChat($chat->id);
    }

    /**
     * Open group creation modal.
     */
    public function openGroupModal()
    {
        $this->newGroupName = '';
        $this->selectedUsersForGroup = [];
        $this->groupModalOpen = true;
    }

    /**
     * Create group chat.
     */
    public function createGroupChat()
    {
        $this->validate([
            'newGroupName' => 'required|string|min:3|max:50',
            'selectedUsersForGroup' => 'required|array|min:1',
        ]);

        // Create new group chat
        $chat = ChatModel::create([
            'name' => $this->newGroupName,
            'is_group' => true,
            'creator_id' => auth()->id(),
        ]);

        // Attach selected users + current user
        $userIds = array_merge($this->selectedUsersForGroup, [auth()->id()]);
        $chat->users()->attach($userIds);

        $this->groupModalOpen = false;
        $this->selectChat($chat->id);
    }

    /**
     * Send message.
     */
    public function sendMessage()
    {
        if (trim($this->messageText) === '' && !$this->attachment) {
            return;
        }

        $this->validate([
            'messageText' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:20480', // 20MB limit
        ]);

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('chat_attachments', 'public');
            $attachmentName = $this->attachment->getClientOriginalName();
            $attachmentMime = $this->attachment->getMimeType();
            $attachmentSize = $this->attachment->getSize();
        }

        $message = Message::create([
            'chat_id' => $this->activeChatId,
            'user_id' => auth()->id(),
            'message' => $this->messageText,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
        ]);

        // Touch the parent chat to update updated_at timestamp
        $message->chat->touch();

        // Broadcast event to other users
        $socketId = request()->header('X-Socket-ID');
        if ($socketId === 'undefined' || !$socketId) {
            broadcast(new MessageSent($message));
        } else {
            broadcast(new MessageSent($message))->toOthers();
        }

        // Reset input fields
        $this->messageText = '';
        $this->attachment = null;

        // Auto scroll to bottom
        $this->dispatch('message-sent');
    }

    /**
     * Append message received from broadcast.
     */
    public function appendBroadcastedMessage($messageData)
    {
        // Simply refresh the active chat messages
        if ($this->activeChatId && $messageData['chat_id'] == $this->activeChatId) {
            $this->dispatch('message-received');
        }
    }

    public function render()
    {
        return view('livewire.chat', [
            'activeChat' => $this->activeChat,
            'chats' => $this->chats,
            'users' => $this->users,
        ])->layout('layouts.app');
    }
}
