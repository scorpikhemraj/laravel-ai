<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'description',
        'publish_date',
        'publish_datetime',
        'publish_time',
        'views',
        'price',
        'is_active',
        'category',
        'tags',
        'status',
        'image',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'publish_datetime' => 'datetime',
        'tags' => 'array',
        'is_active' => 'boolean',
    ];
}
