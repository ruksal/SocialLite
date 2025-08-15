{{-- filepath: e:\laragon\www\SocialLite\resources\views\google\todos.blade.php --}}
<x-layouts.app :title="__('ToDos')">
    <h1 class="text-center mt-8 mb-6 text-white" style="font-size:2rem; font-weight:700;">Google ToDos</h1>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-900 text-white p-6">
            @php
                $hasTasks = false;
                foreach($tasksByList as $list) {
                    if (!empty($list['tasks'])) {
                        $hasTasks = true;
                        break;
                    }
                }
            @endphp
            @if(!$hasTasks)
                <div class="text-center text-neutral-400 mt-8 text-lg">No tasks yet.</div>
            @else
                @foreach($tasksByList as $list)
                    @if(!empty($list['tasks']))
                        <h2 class="text-xl font-semibold mt-6 mb-2">{{ $list['title'] }}</h2>
                        <table class="table-auto w-full text-left border border-neutral-700 rounded-lg overflow-hidden bg-neutral-800 mb-4">
                            <thead class="bg-neutral-700 text-neutral-100">
                                <tr>
                                    <th class="px-4 py-2">Task</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($list['tasks'] as $todo)
                                <tr class="border-b border-neutral-700 hover:bg-neutral-700/50">
                                    <td class="px-4 py-2">{{ $todo->getTitle() ?? '' }}</td>
                                    <td class="px-4 py-2">{{ $todo->getStatus() ?? '' }}</td>
                                    <td class="px-4 py-2">{{ $todo->getDue() ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</x-layouts.app>
