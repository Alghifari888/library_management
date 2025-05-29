<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Peminjaman') }}
        </h2>
    </x-slot>

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.borrowings.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            {{ __('Catat Peminjaman Baru') }}
        </a>
        {{-- Form Filter dan Pencarian --}}
        <form method="GET" action="{{ route('admin.borrowings.index') }}" class="flex items-center space-x-2">
            <input type="text" name="search" placeholder="Cari Anggota/Buku..." value="{{ request('search') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                <option value="">Semua Status</option>
                <option value="{{ \App\Models\Borrowing::STATUS_BORROWED }}" {{ request('status') == \App\Models\Borrowing::STATUS_BORROWED ? 'selected' : '' }}>Dipinjam</option>
                <option value="{{ \App\Models\Borrowing::STATUS_RETURNED }}" {{ request('status') == \App\Models\Borrowing::STATUS_RETURNED ? 'selected' : '' }}>Dikembalikan</option>
                <option value="{{ \App\Models\Borrowing::STATUS_OVERDUE }}" {{ request('status') == \App\Models\Borrowing::STATUS_OVERDUE ? 'selected' : '' }}>Terlambat</option>
            </select>
            <button type="submit" class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-md">Filter</button>
             <a href="{{ route('admin.borrowings.index') }}" class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-xs font-semibold rounded-md">Reset</a>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Anggota</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Judul Buku</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tgl Pinjam</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jatuh Tempo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tgl Kembali</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($borrowings as $index => $borrowing)
                        @php
                            $isOverdue = !$borrowing->returned_at && $borrowing->due_at->isPast();
                            $statusClass = '';
                            $statusText = ucfirst($borrowing->status);

                            if ($borrowing->status == \App\Models\Borrowing::STATUS_BORROWED && $isOverdue) {
                                $statusClass = 'text-red-500 font-semibold'; // Class untuk status overdue yang masih dipinjam
                                $statusText = 'Terlambat'; // Update text juga
                            } elseif ($borrowing->status == \App\Models\Borrowing::STATUS_RETURNED) {
                                $statusClass = 'text-green-500';
                            } elseif ($borrowing->status == \App\Models\Borrowing::STATUS_OVERDUE && $borrowing->returned_at) {
                                // Jika statusnya 'overdue' tapi sudah dikembalikan (misal, terlambat mengembalikan)
                                $statusClass = 'text-yellow-600';
                                $statusText = 'Dikembalikan Terlambat';
                            } elseif ($borrowing->status == \App\Models\Borrowing::STATUS_BORROWED) {
                                $statusClass = 'text-blue-500';
                            }
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $borrowings->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($borrowing->book->title ?? 'N/A', 30) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $borrowing->borrowed_at ? \Carbon\Carbon::parse($borrowing->borrowed_at)->isoFormat('D MMM YYYY') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $borrowing->due_at ? \Carbon\Carbon::parse($borrowing->due_at)->isoFormat('D MMM YYYY') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $borrowing->returned_at ? \Carbon\Carbon::parse($borrowing->returned_at)->isoFormat('D MMM YYYY') : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $statusClass }}">
                                {{ $statusText }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if ($borrowing->status == \App\Models\Borrowing::STATUS_BORROWED || ($borrowing->status == \App\Models\Borrowing::STATUS_OVERDUE && !$borrowing->returned_at))
                                    {{-- Tambahkan form untuk tombol "Kembalikan" di sini nanti --}}
                                    {{-- <form action="{{ route('admin.borrowings.return', $borrowing) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-600 hover:text-green-900">Kembalikan</button>
                                    </form> --}}
                                    <span class="text-xs text-gray-400">(Proses Pengembalian akan dibuat)</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Belum ada data peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Paginasi --}}
        @if ($borrowings->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            {{ $borrowings->links() }}
        </div>
        @endif
    </div>
</x-admin-layout>