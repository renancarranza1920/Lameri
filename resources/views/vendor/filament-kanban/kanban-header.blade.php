<h3 class="mb-2 px-2 font-semibold text-md text-gray-600 dark:text-gray-400">
    <span class="text-primary-500">❖</span>
    {{ $status['title'] }}
    <span class="text-xs font-normal ml-2 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full">
        {{ count($status['records'] ?? []) }}
    </span>
</h3>