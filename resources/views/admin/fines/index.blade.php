<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Denda') }}
        </h2>
    </x-slot>

    <div class="mb-6 flex justify-end items-center">
        {{-- Form Filter dan Pencarian --}}
        <form method="GET" action="{{ route('admin.fines.index') }}" class="flex items-center space-x-2">
            <input type="text" name="search" placeholder="Cari Anggota/Buku/Alasan..." value="{{ request('search') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                <option value="">Semua Status Bayar</option>
                <option value="{{ \App\Models\Fine::STATUS_UNPAID }}" {{ request('status') == \App\Models\Fine::STATUS_UNPAID ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="{{ \App\Models\Fine::STATUS_PAID }}" {{ request('status') == \App\Models\Fine::STATUS_PAID ? 'selected' : '' }}>Sudah Dibayar</option>
            </select>
            <button type="submit" class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-md">Filter</button>
            <a href="{{ route('admin.fines.index') }}" class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-xs font-semibold rounded-md">Reset</a>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Anggota</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Judul Buku Dipinjam</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jumlah Denda</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Alasan Denda</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status Bayar</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tgl Denda</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tgl Bayar</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($fines as $index => $fine)
                        @php
                            $statusClass = '';
                            $statusText = '';
                            if ($fine->status == \App\Models\Fine::STATUS_UNPAID) {
                                $statusText = 'Belum Dibayar';
                                $statusClass = 'text-red-500 font-semibold';
                            } elseif ($fine->status == \App\Models\Fine::STATUS_PAID) {
                                $statusText = 'Sudah Dibayar';
                                $statusClass = 'text-green-500';
                            }
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fines->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fine->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($fine->borrowing->book->title ?? 'N/A', 30) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($fine->reason, 40) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $statusClass }}">{{ $statusText }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $fine->created_at ? \Carbon\Carbon::parse($fine->created_at)->isoFormat('D MMM YY, HH:mm') : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $fine->paid_at ? \Carbon\Carbon::parse($fine->paid_at)->isoFormat('D MMM YY, HH:mm') : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if ($fine->status == \App\Models\Fine::STATUS_UNPAID)
                                    <form action="{{ route('admin.fines.pay', $fine) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menandai denda ini sebagai sudah dibayar?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Belum ada data denda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Paginasi --}}
        @if ($fines->hasPages())
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            {{ $fines->links() }}
        </div>
        @endif
    </div>
</x-admin-layout>