<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Buku: ') }} {{ $book->title }}
        </h2>
    </x-slot>

    <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT') {{-- Method spoofing untuk UPDATE --}}

        {{-- Judul Buku --}}
        <div>
            <x-input-label for="title" :value="__('Judul Buku')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $book->title)" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        {{-- Kategori --}}
        <div>
            <x-input-label for="category_id" :value="__('Kategori')" />
            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                <option value="">{{ __('-- Pilih Kategori --') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
        </div>

        {{-- Penulis --}}
        <div>
            <x-input-label for="author" :value="__('Penulis')" />
            <x-text-input id="author" name="author" type="text" class="mt-1 block w-full" :value="old('author', $book->author)" required />
            <x-input-error class="mt-2" :messages="$errors->get('author')" />
        </div>

        {{-- Penerbit --}}
        <div>
            <x-input-label for="publisher" :value="__('Penerbit')" />
            <x-text-input id="publisher" name="publisher" type="text" class="mt-1 block w-full" :value="old('publisher', $book->publisher)" required />
            <x-input-error class="mt-2" :messages="$errors->get('publisher')" />
        </div>

        {{-- Tahun Terbit --}}
        <div>
            <x-input-label for="publication_year" :value="__('Tahun Terbit (YYYY)')" />
            <x-text-input id="publication_year" name="publication_year" type="number" class="mt-1 block w-full" :value="old('publication_year', $book->publication_year)" required placeholder="Contoh: 2023" />
            <x-input-error class="mt-2" :messages="$errors->get('publication_year')" />
        </div>

        {{-- ISBN --}}
        <div>
            <x-input-label for="isbn" :value="__('ISBN')" />
            <x-text-input id="isbn" name="isbn" type="text" class="mt-1 block w-full" :value="old('isbn', $book->isbn)" required />
            <x-input-error class="mt-2" :messages="$errors->get('isbn')" />
        </div>

        {{-- Jumlah Stok --}}
        <div>
            <x-input-label for="stock_quantity" :value="__('Jumlah Stok')" />
            <x-text-input id="stock_quantity" name="stock_quantity" type="number" class="mt-1 block w-full" :value="old('stock_quantity', $book->stock_quantity)" required min="0" />
            <x-input-error class="mt-2" :messages="$errors->get('stock_quantity')" />
        </div>
        
        {{-- Deskripsi --}}
        <div>
            <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $book->description) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        {{-- Sampul Buku --}}
        <div>
            <x-input-label for="cover_image" :value="__('Ganti Sampul Buku (Opsional)')" />
            @if ($book->cover_image_path)
                <div class="mt-2 mb-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sampul Saat Ini:</p>
                    <img src="{{ Storage::url($book->cover_image_path) }}" alt="Sampul {{ $book->title }}" class="h-32 w-auto object-cover rounded mt-1">
                </div>
            @else
                <p class="mt-2 mb-2 text-sm text-gray-600 dark:text-gray-400">Tidak ada sampul saat ini.</p>
            @endif
            <input id="cover_image" name="cover_image" type="file" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">Biarkan kosong jika tidak ingin mengganti. PNG, JPG, GIF, WEBP (MAX. 2MB).</p>
            <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Update Buku') }}</x-primary-button>
            <a href="{{ route('admin.books.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __('Batal') }}
            </a>
        </div>
    </form>
</x-admin-layout>