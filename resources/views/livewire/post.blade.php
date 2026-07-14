<div class="max-w-5xl mx-auto p-6 space-y-4">

    <h2 class="text-2xl font-bold">Post CRUD – All Field Types</h2>

    <input type="text" wire:model="title" placeholder="Title" class="w-full border p-2">

    <textarea wire:model="description" placeholder="Description" class="w-full border p-2"></textarea>

    <input type="date" wire:model="publish_date" class="border p-2">
    <input type="datetime-local" wire:model="publish_datetime" class="border p-2">
    <input type="time" wire:model="publish_time" class="border p-2">

    <input type="number" wire:model="views" placeholder="Views" class="border p-2">
    <input type="number" step="0.01" wire:model="price" placeholder="Price" class="border p-2">

    <label>
        <input type="checkbox" wire:model="is_active"> Active
    </label>

    <select wire:model="category" class="border p-2">
        <option value="">Select Category</option>
        <option value="tech">Tech</option>
        <option value="news">News</option>
    </select>

    <select wire:model="tags" multiple class="border p-2 w-full">
        <option value="php">PHP</option>
        <option value="laravel">Laravel</option>
        <option value="livewire">Livewire</option>
    </select>

    <div>
        <label><input type="radio" wire:model="status" value="draft"> Draft</label>
        <label><input type="radio" wire:model="status" value="published"> Published</label>
    </div>

    <input type="file" wire:model="image">

    <button wire:click="store" class="bg-green-600 text-white px-4 py-2 rounded">
        Save Post
    </button>

    <hr>

    <h3 class="font-bold">Posts List</h3>
    @foreach($posts as $post)
        <div class="border p-2">
            <b>{{ $post->title }}</b> |
            {{ $post->category }} |
            {{ $post->status }}
            <button wire:click="delete({{ $post->id }})" class="text-red-600 ml-2">Delete</button>
        </div>
    @endforeach

</div>
