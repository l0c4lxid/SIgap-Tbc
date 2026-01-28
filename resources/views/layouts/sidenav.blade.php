<aside class="fixed top-4 left-4 bottom-4 w-72 glass-panel rounded-2xl flex flex-col z-50 transition-all duration-300 hidden xl:flex" id="sidenav-main">
    <!-- Brand -->
    <div class="p-6 pb-2">
        <a class="flex items-center gap-3 no-underline hover:opacity-80 transition-opacity" href="{{ route('dashboard') }}">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[var(--color-glass-primary)] to-[var(--color-glass-secondary)] flex items-center justify-center text-white shadow-lg">
                {{-- <img src="{{ asset('assets/img/logo-ct-dark.png') }}" class="w-6 h-6 object-contain brightness-0 invert" alt="main_logo"> --}}
                <span class="font-bold text-lg">S</span>
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-gray-800 text-lg tracking-tight">SITUBA</span>
                <span class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold">Dashboard</span>
            </div>
        </a>
    </div>

    <hr class="h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent border-none my-2 mx-6">

    <!-- Nav Items -->
    <div class="flex-1 overflow-y-auto px-4 py-2 custom-scrollbar">
        <ul class="flex flex-col gap-1.5 list-none pl-0 m-0">
            @foreach($navItems as $item)
                @php
                    $isActive = false;
                    $itemUrl = $item['url'] ?? '#';
                    $base = rtrim($itemUrl, '/');
                    $routes = $item['active_routes'] ?? [];
                    
                    if (!empty($routes) && request()->route()) {
                        $isActive = request()->routeIs($routes);
                    } elseif ($base !== '#' && (url()->current() === $itemUrl || str_starts_with(url()->current(), $base . '/'))) {
                        $isActive = true;
                    }
                @endphp
                <li>
                    <a href="{{ $itemUrl }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group no-underline
                              {{ $isActive 
                                 ? 'bg-[var(--color-glass-primary)]/10 text-[var(--color-glass-primary)] font-semibold shadow-sm' 
                                 : 'text-gray-600 hover:bg-gray-100/50 hover:text-gray-900' }}">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors
                                    {{ $isActive ? 'bg-[var(--color-glass-primary)] text-white shadow-md' : 'bg-white/50 text-gray-400 group-hover:text-[var(--color-glass-primary)] shadow-sm' }}">
                              <i class="{{ $item['icon'] ?? 'ri-circle-fill' }} text-lg"></i>
                        </div>
                        <span class="text-sm tracking-wide">{{ $item['label'] }}</span>
                        
                        @if($isActive)
                            <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[var(--color-glass-primary)]"></div>
                        @endif
                    </a>
                </li>
            @endforeach

            <li class="mt-6 mb-2 px-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Akun</span>
            </li>

            {{-- Profile Link --}}
            <li>
                <a href="{{ $profileNav['url'] }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group no-underline
                          {{ request()->routeIs('profile.edit') || request()->routeIs('pemda.profile.edit')
                             ? 'bg-[var(--color-glass-primary)]/10 text-[var(--color-glass-primary)] font-semibold shadow-sm' 
                             : 'text-gray-600 hover:bg-gray-100/50 hover:text-gray-900' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/50 text-gray-400 group-hover:text-[var(--color-glass-primary)] shadow-sm transition-colors">
                        <i class="ri-user-settings-line text-lg"></i>
                    </div>
                    <span class="text-sm tracking-wide">{{ $profileNav['label'] }}</span>
                </a>
            </li>

            {{-- Logout --}}
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group no-underline text-gray-600 hover:bg-red-50 hover:text-red-600 cursor-pointer border-none bg-transparent">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/50 text-gray-400 group-hover:text-red-500 shadow-sm transition-colors">
                            <i class="ri-logout-box-line text-lg"></i>
                        </div>
                        <span class="text-sm tracking-wide">Keluar</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidenav-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 hidden transition-opacity opacity-0 lg:hidden" onclick="toggleSidebar()"></div>

<script>
    function toggleSidebar() {
        const sidenav = document.getElementById('sidenav-main');
        const backdrop = document.getElementById('sidenav-backdrop');
        
        if (sidenav.classList.contains('hidden')) {
            // Open
            sidenav.classList.remove('hidden');
            sidenav.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
            }, 10);
        } else {
            // Close
            sidenav.classList.add('-translate-x-full'); 
            backdrop.classList.add('opacity-0');
            setTimeout(() => {
                sidenav.classList.add('hidden');
                backdrop.classList.add('hidden');
            }, 300);
        }
    }
</script>
