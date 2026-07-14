<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leads Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div id="lead-crud-app">
                <lead-crud></lead-crud>
            </div>
        </div>
    </div>
</x-app-layout>
