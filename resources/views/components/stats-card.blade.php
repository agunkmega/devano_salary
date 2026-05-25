@props([
    'icon' => '',
    'label' => '',
    'value' => '0',
    'color' => 'blue',
    'trend' => null,
    'trendUp' => true,
])

@php
$colors = [
    'blue' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800',
    'green' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
    'orange' => 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800',
    'red' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800',
    'purple' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800',
    'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
    'gray' => 'bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600',
];
@endphp

<div
    x-data="{ visible: false }"
    x-intersect="visible = true"
    x-show="visible"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="relative overflow-hidden rounded-2xl border {{ $colors[$color] ?? $colors['blue'] }} bg-white dark:bg-gray-800 p-6 shadow-sm hover:shadow-md transition-all duration-300"
>
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium opacity-75">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight">{{ $value }}</p>
            @if($trend)
            <div class="mt-2 flex items-center gap-1 text-sm">
                @if($trendUp)
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                @else
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                @endif
                <span class="{{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $trend }}</span>
            </div>
            @endif
        </div>
        <div class="flex-shrink-0 ml-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-current/10">
                @if($icon)
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                @endif
            </div>
        </div>
    </div>
</div>
