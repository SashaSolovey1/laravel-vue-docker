<?php

use function Livewire\Volt\{state, computed};
use Illuminate\Support\Facades\Redis;

$liveUsers = computed(function () {
    $count = 0;
    $cursor = null;
    $pattern = 'live_users:*';

    do {
        list($cursor, $keys) = Redis::scan($cursor, $pattern, 1000);
        $count += count($keys ?? []);
    } while ($cursor != 0);

    return $count;
});


?>
<div>
    <div wire:poll.5s>
        <h3 class="text-base font-semibold leading-6 text-gray-900">Live stats</h3>

        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-indigo-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">Live Now</p>
                </dt>
                <dd class="ml-16 flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($this->liveUsers, 0) }}</p>
                </dd>
            </div>
        </dl>
    </div>
</div>
