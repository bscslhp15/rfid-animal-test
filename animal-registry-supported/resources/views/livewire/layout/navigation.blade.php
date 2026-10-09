<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $user = auth()->user();
    $isOwnerAccount = $user->isOwnerAccount();
    $homeRoute = $user->homeRouteName();

    $navigationGroups = $isOwnerAccount
        ? [
            ['label' => 'Registry', 'items' => [
                ['label' => 'My animals', 'route' => 'my-animals.index', 'active' => 'my-animals.*', 'badge' => null],
            ]],
            ['label' => 'Account', 'items' => [
                ['label' => 'Profile', 'route' => 'profile', 'active' => 'profile', 'badge' => null],
            ]],
        ]
        : [
            ['label' => 'Overview', 'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'badge' => null],
                ['label' => 'Analytics', 'route' => 'analytics', 'active' => 'analytics', 'badge' => null],
            ]],
            ['label' => 'Registry', 'items' => [
                ['label' => 'Animals', 'route' => 'animals.index', 'active' => 'animals.*', 'badge' => null],
                ['label' => 'Owners', 'route' => 'owners.index', 'active' => 'owners.*', 'badge' => null],
                ['label' => 'Feeding', 'route' => 'feeding.index', 'active' => 'feeding.*', 'badge' => null],
            ]],
            ['label' => 'Account', 'items' => [
                ['label' => 'Profile', 'route' => 'profile', 'active' => 'profile', 'badge' => null],
            ]],
        ];
@endphp

<nav x-data="{ open: false }" class="relative z-40 lg:w-64 lg:shrink-0">
    <div class="flex h-16 items-center justify-between bg-slate-950 px-4 text-white lg:hidden">
        <a href="{{ route($homeRoute) }}" wire:navigate class="flex items-center gap-3 font-semibold">
            <x-application-logo class="h-8 w-auto fill-current text-teal-300" />
            <span>Animal Registry</span>
        </a>
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-label="Toggle navigation" class="inline-flex items-center justify-center rounded-md p-2 text-slate-200 hover:bg-slate-800">
            <svg x-show="!open" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="open" x-cloak class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <aside class="sticky top-0 hidden h-screen flex-col bg-slate-950 text-white lg:flex">
        <a href="{{ route($homeRoute) }}" wire:navigate class="flex h-20 items-center gap-3 border-b border-slate-800 px-6">
            <x-application-logo class="h-9 w-auto fill-current text-teal-300" />
            <span class="font-semibold tracking-wide">Animal Registry</span>
        </a>

        <div class="px-4 pt-7">
            @foreach ($navigationGroups as $group)
                <section class="{{ $loop->first ? '' : 'mt-7' }}">
                    <p class="px-3 text-xs font-semibold uppercase text-slate-500">{{ $group['label'] }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" wire:navigate @class(['flex items-center justify-between rounded-md px-3 py-2.5 text-sm font-medium transition', 'bg-teal-300 text-slate-950' => request()->routeIs($item['active']), 'text-slate-300 hover:bg-slate-900 hover:text-white' => ! request()->routeIs($item['active'])])>
                                <span>{{ $item['label'] }}</span>
                                @if ($item['badge'])
                                    <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mt-auto border-t border-slate-800 p-4">
            <p class="truncate px-2 text-sm font-medium text-white">{{ auth()->user()->name }}</p>
            <p class="truncate px-2 pt-1 text-xs text-slate-400">{{ auth()->user()->email }}</p>
            <button wire:click="logout" class="mt-4 px-2 text-sm text-slate-300 hover:text-white">Log out</button>
        </div>
    </aside>

    <div x-show="open" x-cloak @click.outside="open = false" class="absolute inset-x-0 top-16 border-t border-slate-800 bg-slate-950 px-4 pb-4 text-white shadow-xl lg:hidden">
        <div class="space-y-4 py-3">
            @foreach ($navigationGroups as $group)
                <section>
                    <p class="px-3 text-xs font-semibold uppercase text-slate-500">{{ $group['label'] }}</p>
                    <div class="mt-2 space-y-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" wire:navigate @click="open = false" @class(['flex items-center justify-between rounded-md px-3 py-2.5 text-sm font-medium', 'bg-teal-300 text-slate-950' => request()->routeIs($item['active']), 'text-slate-300 hover:bg-slate-900' => ! request()->routeIs($item['active'])])>
                                <span>{{ $item['label'] }}</span>
                                @if ($item['badge'])
                                    <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
            <button wire:click="logout" class="w-full rounded-md px-3 py-2.5 text-left text-sm font-medium text-slate-300 hover:bg-slate-900">Log out</button>
        </div>
        <div class="border-t border-slate-800 px-3 pt-3">
            <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
            <p class="truncate pt-1 text-xs text-slate-400">{{ auth()->user()->email }}</p>
        </div>
    </div>
</nav>
