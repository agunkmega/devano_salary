@props(['user' => null])

@php
$user = $user ?? auth()->user();
$role = $user?->role ?? 'user';

$menuGroups = [
    'Main' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />', 'roles' => ['*']],
    ],
    'Master Data' => [
        ['label' => 'Pegawai', 'route' => 'admin.employees.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />', 'roles' => ['super_admin', 'hr', 'manager']],
        ['label' => 'Departemen', 'route' => 'admin.departments.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Jabatan', 'route' => 'admin.positions.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Station', 'route' => 'admin.stations.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Jenis Cuti', 'route' => 'admin.leave-types.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Manajemen User', 'route' => 'admin.users.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />', 'roles' => ['super_admin']],
    ],
    'Manajemen' => [
        ['label' => 'Absensi', 'route' => 'admin.attendances.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />', 'roles' => ['super_admin', 'hr', 'manager']],
        ['label' => 'Shift', 'route' => 'admin.shifts.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Cuti / Izin', 'route' => 'admin.leaves.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />', 'roles' => ['*']],
        ['label' => 'Kasbon', 'route' => 'admin.cash-advances.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />', 'roles' => ['*']],
        ['label' => 'Libur Nasional', 'route' => 'admin.national-holidays.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Riwayat Webhook', 'route' => 'admin.fingerspot.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Saldo DP', 'route' => 'admin.dp.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-3-3m3 3l3-3M4 18h16a2 2 0 002-2v-2H2v2a2 2 0 002 2z" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Pengumuman', 'route' => 'admin.announcements.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.177 0.918l-2.9-3.688a1.76 1.76 0 000-2.14l2.9-3.688A1.76 1.76 0 0111 4.76m7.28 10.466c-.058.467-.172.922-.341 1.365-.114.29-.257.573-.43.85a1.505 1.505 0 01-1.36.707c-.35 0-.67-.107-.94-.32-.27-.213-.47-.497-.59-.85a3.42 3.42 0 01-.21-1.21c0-.44.07-.86.21-1.26a2.5 2.5 0 01.59-.92c.27-.25.59-.38.94-.38.53 0 .96.23 1.29.68.33.45.55 1.02.66 1.72z" />', 'roles' => ['super_admin', 'hr']],
        ['label' => 'Mesin Absensi', 'route' => 'admin.fingerspot.machines', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />', 'roles' => ['super_admin', 'hr']],
    ],
    'Payroll' => [
        ['label' => 'Payroll', 'route' => 'admin.payrolls.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />', 'roles' => ['super_admin', 'hr', 'manager']],
    ],
    'Laporan' => [
        ['label' => 'Laporan Absensi', 'route' => 'admin.reports.attendance', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />', 'roles' => ['super_admin', 'hr', 'manager']],
        ['label' => 'Laporan Payroll', 'route' => 'admin.reports.payroll', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />', 'roles' => ['super_admin', 'hr', 'manager']],
        ['label' => 'Sisa Cuti & DP', 'route' => 'admin.reports.leave-balance', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />', 'roles' => ['super_admin', 'hr', 'manager']],
        ['label' => 'Laporan BPJS', 'route' => 'admin.reports.bpjs', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />', 'roles' => ['super_admin', 'hr', 'manager']],
    ],
    'Pengaturan' => [
        ['label' => 'Pengaturan', 'route' => 'admin.settings.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />', 'roles' => ['super_admin']],
        ['label' => 'Activity Log', 'route' => 'admin.activity-logs.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />', 'roles' => ['super_admin']],
    ],
];

function isActive($route, $groupItems = []) {
    if (request()->routeIs($route)) return true;
    if (str_ends_with($route, '.index')) {
        $current = request()->route()->getName();
        // If current route is an exact match for another sidebar item, don't highlight this one
        foreach ($groupItems as $item) {
            if ($item !== $route && $item === $current) {
                return false;
            }
        }
        $prefix = str_replace('.index', '.*', $route);
        return request()->routeIs($prefix);
    }
    return false;
}

function userCanSee($itemRoles, $userRole) {
    return in_array('*', $itemRoles) || in_array($userRole, $itemRoles);
}
@endphp

<div x-data="{ mobileOpen: false }" @toggle-sidebar.window="mobileOpen = !mobileOpen">
    <aside
    class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col"
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            @php
                $logoPath = \App\Models\Setting::where('key', 'app_logo')?->value('value');
            @endphp
            @if($logoPath)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="Logo" class="h-8 w-auto">
            @else
            <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
            </div>
            @endif
            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</span>
        </div>
        <button @click="mobileOpen = false" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @foreach($menuGroups as $groupName => $items)
        <div class="mb-4">
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $groupName }}</p>
            <div class="space-y-1">
                @php $groupRoutes = array_column($items, 'route'); @endphp
                @foreach($items as $item)
                    @if(userCanSee($item['roles'], $role))
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 {{ isActive($item['route'], $groupRoutes) ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endforeach
    </nav>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
    </aside>
    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-20 bg-black/50 lg:hidden" @click="mobileOpen = false"></div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('sidebar', () => ({
            mobileOpen: false,
            init() {
                this.$watch('mobileOpen', val => {
                    document.body.classList.toggle('overflow-hidden', val);
                });
            }
        }));
    });
</script>
@endpush