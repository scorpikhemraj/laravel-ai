<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_group',
        'creator_id',
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the chat name for the given user (for 1-to-1 chats, it shows the other user's name).
     */
    public function displayNameFor(User $user): string
    {
        if ($this->is_group) {
            return $this->name ?? 'Group Chat';
        }

        $otherUser = $this->users()->where('users.id', '!=', $user->id)->first();
        return $otherUser?->name ?? 'Direct Message';
    }
}
