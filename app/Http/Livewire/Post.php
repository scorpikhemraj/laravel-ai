<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post as PostModel;

class Post extends Component
{
    use WithFileUploads;

    public $posts, $post_id;

    public $title, $description;
    public $publish_date, $publish_datetime, $publish_time;
    public $views = 0, $price;
    public $is_active = true;
    public $category;
    public $tags = [];
    public $status = 'draft';
    public $image;

    public $isEdit = false;

    protected $rules = [
        'title' => 'required|min:3',
        'description' => 'nullable',
        'publish_date' => 'nullable|date',
        'publish_datetime' => 'nullable|date',
        'publish_time' => 'nullable',
        'views' => 'integer',
        'price' => 'nullable|numeric',
        'is_active' => 'boolean',
        'category' => 'nullable|string',
        'tags' => 'array',
        'status' => 'required',
        'image' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->posts = PostModel::latest()->get();
    }

    public function store()
    {
        $this->validate();

        $imagePath = $this->image
            ? $this->image->store('posts', 'public')
            : null;

        PostModel::create([
            'title' => $this->title,
            'description' => $this->description,
            'publish_date' => $this->publish_date,
            'publish_datetime' => $this->publish_datetime,
            'publish_time' => $this->publish_time,
            'views' => $this->views,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'category' => $this->category,
            'tags' => $this->tags,
            'status' => $this->status,
            'image' => $imagePath,
        ]);

        $this->reset();
        $this->posts = PostModel::latest()->get();
    }

    public function delete($id)
    {
        PostModel::findOrFail($id)->delete();
        $this->posts = PostModel::latest()->get();
    }

    public function render()
    {
        return view('livewire.post')->layout('layouts.app');
    }
}

