@extends('layouts.admin')

@section('title', 'Notifikasi')
@section('page-title', 'Pusat Notifikasi')

@section('content')
<div class="space-y-6">
    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('admin.notifications.index', ['filter' => 'all']) }}"
            class="bg-white p-5 rounded-xl border {{ $filter === 'all' ? 'border-blue-500 ring-2 ring-blue-100' : 'border-gray-200' }} shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Notifikasi</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalCount) }}</p>
                </div>
                <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-bell text-lg"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}"
            class="bg-white p-5 rounded-xl border {{ $filter === 'unread' ? 'border-amber-500 ring-2 ring-amber-100' : 'border-gray-200' }} shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Belum Dibaca</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($unreadCount) }}</p>
                </div>
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-envelope text-lg"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.notifications.index', ['filter' => 'read']) }}"
            class="bg-white p-5 rounded-xl border {{ $filter === 'read' ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-gray-200' }} shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sudah Dibaca</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($readCount) }}</p>
                </div>
                <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-envelope-open text-lg"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- Controls & Search Bar --}}
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        {{-- Filter Tabs --}}
        <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-lg w-full md:w-auto">
            <a href="{{ route('admin.notifications.index', ['filter' => 'all', 'search' => request('search')]) }}"
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition {{ $filter === 'all' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                Semua ({{ $totalCount }})
            </a>
            <a href="{{ route('admin.notifications.index', ['filter' => 'unread', 'search' => request('search')]) }}"
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition {{ $filter === 'unread' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                Belum Dibaca ({{ $unreadCount }})
            </a>
            <a href="{{ route('admin.notifications.index', ['filter' => 'read', 'search' => request('search')]) }}"
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition {{ $filter === 'read' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                Sudah Dibaca ({{ $readCount }})
            </a>
        </div>

        {{-- Search & Bulk Actions --}}
        <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full md:w-auto">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="relative w-full sm:w-64">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari notifikasi..."
                       class="w-full pl-9 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition border border-blue-200"
                                title="Tandai semua notifikasi telah dibaca">
                            <i class="fa-solid fa-check-double text-blue-600"></i>
                            <span>Tandai Semua Dibaca</span>
                        </button>
                    </form>
                @endif

                @if ($readCount > 0 || $totalCount > 0)
                    <div x-data="{ openClearModal: false }" class="relative">
                        <button @click="openClearModal = true"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 text-xs font-medium rounded-lg transition border border-gray-200 hover:border-red-200">
                            <i class="fa-regular fa-trash-can"></i>
                            <span>Bersihkan</span>
                        </button>

                        {{-- Modal Konfirmasi Bersihkan --}}
                        <div x-show="openClearModal"
                             x-cloak
                             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
                             @keydown.escape.window="openClearModal = false">
                            <div @click.away="openClearModal = false"
                                 class="bg-white rounded-xl max-w-sm w-full p-5 shadow-2xl border border-gray-200">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 text-red-600 text-xl">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-900">Bersihkan Notifikasi</h4>
                                    <p class="text-xs text-gray-600 mt-1">Pilih notifikasi yang ingin Anda hapus dari akun Anda.</p>
                                </div>

                                <div class="mt-5 space-y-2">
                                    @if ($readCount > 0)
                                        <form method="POST" action="{{ route('admin.notifications.clear-all') }}">
                                            @csrf
                                            <input type="hidden" name="scope" value="read">
                                            <button type="submit"
                                                    class="w-full px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                                Hapus yang Sudah Dibaca ({{ $readCount }})
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.notifications.clear-all') }}">
                                        @csrf
                                        <input type="hidden" name="scope" value="all">
                                        <button type="submit"
                                                class="w-full px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                            Hapus Semua Notifikasi ({{ $totalCount }})
                                        </button>
                                    </form>

                                    <button @click="openClearModal = false"
                                            type="button"
                                            class="w-full px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 transition">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Notifications List Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $icon = $data['icon'] ?? 'fa-solid fa-bell';
                    $color = $data['color'] ?? 'blue';
                    $actionUrl = $data['action_url'] ?? null;
                @endphp
                <div class="p-4 sm:p-5 transition-colors {{ $isUnread ? 'bg-blue-50/40 hover:bg-blue-50/70' : 'hover:bg-gray-50/80' }} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        {{-- Icon badge --}}
                        <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center text-sm shadow-xs
                            {{ $color === 'emerald' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $color === 'blue' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $color === 'amber' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $color === 'red' ? 'bg-red-100 text-red-700' : '' }}
                            {{ !in_array($color, ['emerald', 'blue', 'amber', 'red']) ? 'bg-indigo-100 text-indigo-700' : '' }}
                        ">
                            <i class="{{ $icon }}"></i>
                        </div>

                        {{-- Text Body --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-semibold text-gray-900">
                                    {{ $data['title'] ?? 'Notifikasi Sistem' }}
                                </h4>
                                @if ($isUnread)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                        Baru
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    • {{ $notification->created_at->diffForHumans() }} ({{ $notification->created_at->format('d M Y, H:i') }})
                                </span>
                            </div>

                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                {{ $data['message'] ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                        @if ($actionUrl)
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="inline">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ $actionUrl }}">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                                    <span>Lihat Detail</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            </form>
                        @endif

                        @if ($isUnread)
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition"
                                        title="Tandai sebagai telah dibaca">
                                    <i class="fa-solid fa-check text-gray-500"></i>
                                    <span class="hidden md:inline">Tandai Dibaca</span>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}"
                              onsubmit="return confirm('Hapus notifikasi ini?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                    title="Hapus notifikasi">
                                <i class="fa-regular fa-trash-can text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-16 px-4 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-gray-400 text-2xl">
                        <i class="fa-regular fa-bell-slash"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Tidak Ada Notifikasi</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        @if (request('search'))
                            Tidak ada notifikasi yang cocok dengan kata kunci pencarian "{{ request('search') }}".
                        @elseif ($filter === 'unread')
                            Semua notifikasi Anda sudah dibaca. Tidak ada notifikasi yang tertunda!
                        @elseif ($filter === 'read')
                            Belum ada notifikasi yang ditandai dibaca.
                        @else
                            Anda belum memiliki notifikasi apapun saat ini.
                        @endif
                    </p>
                    @if (request('search') || $filter !== 'all')
                        <div class="mt-4">
                            <a href="{{ route('admin.notifications.index') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Reset Filter</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($notifications->hasPages())
            <div class="p-4 bg-gray-50 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
