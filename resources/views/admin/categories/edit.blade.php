<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Kategori: ') }} {{ $category->name }}
        </h2>
    </x-slot>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT') {{-- Method spoofing untuk UPDATE --}}

        <div>
            <x-input-label for="name" :value="__('Nama Kategori')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $category->description) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>
        
        {{-- Jika Anda ingin mengizinkan edit slug secara manual (jarang diperlukan jika auto-generate)
        <div>
            <x-input-label for="slug" :value="__('Slug (Opsional, akan digenerate otomatis jika kosong)')" />
            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $category->slug)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
        </div>
        --}}

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update Kategori') }}</x-primary-button>
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __('Batal') }}
            </a>
        </div>
    </form>
</x-admin-layout>