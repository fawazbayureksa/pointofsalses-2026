@php
    $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();
    $recentNotifications = Auth::user()->notifications()->latest()->take(5)->get();
@endphp

<div x-data="{ notificationsOpen: false }" class="relative">
    <button @click="notificationsOpen = !notificationsOpen"
        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors relative"
        title="Notifikasi"
        aria-label="Notifikasi">
        <i class="fa-solid fa-bell text-lg"></i>
        @if ($unreadNotificationsCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none ring-2 ring-white">
                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
            </span>
        @endif
    </button>

    <div x-show="notificationsOpen"
        @click.away="notificationsOpen = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden">
        
        {{-- Header --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-800 text-sm">Notifikasi</h3>
                @if ($unreadNotificationsCount > 0)
                    <span class="px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 rounded-full">
                        {{ $unreadNotificationsCount }} baru
                    </span>
                @endif
            </div>

            @if ($unreadNotificationsCount > 0)
                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            @forelse ($recentNotifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $icon = $data['icon'] ?? 'fa-solid fa-bell';
                    $color = $data['color'] ?? 'blue';
                    $actionUrl = $data['action_url'] ?? route('admin.notifications.index');
                @endphp
                <div class="p-3 transition-colors {{ $isUnread ? 'bg-blue-50/50 hover:bg-blue-50' : 'hover:bg-gray-50' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-xs
                            {{ $color === 'emerald' ? 'bg-emerald-100 text-emerald-600' : '' }}
                            {{ $color === 'blue' ? 'bg-blue-100 text-blue-600' : '' }}
                            {{ $color === 'amber' ? 'bg-amber-100 text-amber-600' : '' }}
                            {{ $color === 'red' ? 'bg-red-100 text-red-600' : '' }}
                            {{ !in_array($color, ['emerald', 'blue', 'amber', 'red']) ? 'bg-indigo-100 text-indigo-600' : '' }}
                        ">
                            <i class="{{ $icon }}"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ $actionUrl }}">
                                <button type="submit" class="text-left w-full group">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-semibold text-gray-800 group-hover:text-blue-600 truncate">
                                            {{ $data['title'] ?? 'Notifikasi' }}
                                        </p>
                                        @if ($isUnread)
                                            <span class="w-2 h-2 rounded-full bg-blue-600 ml-1.5 flex-shrink-0" title="Belum dibaca"></span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed">
                                        {{ $data['message'] ?? '' }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 px-4 text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2 text-gray-400">
                        <i class="fa-regular fa-bell-slash text-xl"></i>
                    </div>
                    <p class="text-xs font-medium text-gray-600">Belum ada notifikasi</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Notifikasi baru akan muncul di sini</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="p-3 bg-gray-50 border-t border-gray-200 text-center">
            <a href="{{ route('admin.notifications.index') }}"
                class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition block py-0.5">
                Lihat semua notifikasi <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>
