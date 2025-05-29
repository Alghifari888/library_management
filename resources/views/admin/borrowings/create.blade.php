<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Catat Peminjaman Baru') }}
        </h2>
    </x-slot>

    <form method="POST" action="{{ route('admin.borrowings.store') }}" class="mt-6 space-y-6">
        @csrf

        {{-- Pilih Anggota --}}
        <div>
            <x-input-label for="user_id" :value="__('Anggota Peminjam')" />
            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                <option value="">{{ __('-- Pilih Anggota --') }}</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" {{ old('user_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }} ({{ $member->nis_nim ?? $member->email }})
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
        </div>

        {{-- Pilih Buku --}}
        <div>
            <x-input-label for="book_id" :value="__('Buku yang Dipinjam')" />
            <select id="book_id" name="book_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                <option value="">{{ __('-- Pilih Buku --') }}</option>
                @foreach ($books as $book)
                    <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                        {{ $book->title }} (Stok: {{ $book->available_quantity }})
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('book_id')" />
        </div>

        {{-- Tanggal Pinjam --}}
        <div>
            <x-input-label for="borrowed_at" :value="__('Tanggal Pinjam')" />
            <x-text-input id="borrowed_at" name="borrowed_at" type="date" class="mt-1 block w-full" :value="old('borrowed_at', $defaultBorrowedAt)" required />
            <x-input-error class="mt-2" :messages="$errors->get('borrowed_at')" />
        </div>
        
        {{-- Tanggal Jatuh Tempo --}}
        <div>
            <x-input-label for="due_at" :value="__('Tanggal Jatuh Tempo')" />
            <x-text-input id="due_at" name="due_at" type="date" class="mt-1 block w-full" :value="old('due_at', $defaultDueAt)" required />
            <x-input-error class="mt-2" :messages="$errors->get('due_at')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Peminjaman') }}</x-primary-button>
            <a href="{{ route('admin.borrowings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __('Batal') }}
            </a>
        </div>
    </form>
</x-admin-layout>