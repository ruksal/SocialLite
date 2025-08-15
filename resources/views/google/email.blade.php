{{-- filepath: e:\laragon\www\SocialLite\resources\views\google\email.blade.php --}}
<x-layouts.app :title="__('Email')">
    <h1 class="text-center mt-8 mb-6 text-white" style="font-size:2rem; font-weight:700;">Latest Gmail Emails</h1>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-900 text-white p-6">
            <table class="table-auto w-full text-left border border-neutral-700 rounded-lg overflow-hidden bg-neutral-800">
                <thead class="bg-neutral-700 text-neutral-100">
                    <tr>
                        <th class="px-4 py-2">From</th>
                        <th class="px-4 py-2">Subject</th>
                        <th class="px-4 py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $email)
                    <tr class="border-b border-neutral-700 hover:bg-neutral-700/50">
                        <td class="px-4 py-2">{{ $email['from'] ?? '' }}</td>
                        <td class="px-4 py-2">{{ $email['subject'] ?? '' }}</td>
                        <td class="px-4 py-2">{{ $email['date'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
