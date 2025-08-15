<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <a href="{{ route('google.calendar') }}"
               class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                      flex items-center justify-center transition-colors text-white text-2xl font-semibold"
               style="background-color: #1e3a8a; /* darker blue */
                      &:hover { background-color: #162c6a; }">
                Calendar
            </a>
            <a href="{{ route('google.email') }}"
               class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                      flex items-center justify-center transition-colors text-white text-2xl font-semibold"
               style="background-color: #166534; /* darker green */
                      &:hover { background-color: #104025; }">
                Email
            </a>
            <a href="{{ route('google.todos') }}"
               class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                      flex items-center justify-center transition-colors text-white text-2xl font-semibold"
               style="background-color: #6b21a8; /* darker purple */
                      &:hover { background-color: #4b1473; }">
                ToDos
            </a>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
