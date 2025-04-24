<div class="overflow-hidden overflow-x-auto w-full rounded-lg">
    <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
        <thead
            class="text-xs font-medium uppercase bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">
            <tr>
                <th scope="col" class="p-4">
                    <input type="checkbox" wire:model.live="selectPage" class="rounded">
                </th>
                <th scope="col" class="p-4">Name</th>
                <th scope="col" class="p-4">Subject</th>
                <th scope="col" class="p-4">Updated</th>
                <th scope="col" class="p-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-300 dark:divide-neutral-700">
            @forelse($systemEmails as $email)
            <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                <td class="p-4">
                    <input type="checkbox" wire:model.live="selectedTemplates" value="{{ $email->id }}" class="rounded">
                </td>
                <td class="p-4">
                    <div>
                        <p>{{ $email->name }}</p>
                        <p class="text-sm text-neutral-500">{{ $email->slug }}</p>
                    </div>
                </td>
                <td class="p-4 text-nowrap">
                    {{ $email->email_subject }}
                </td>
                <td class="p-4 text-nowrap">
                    {{ $email->updated_at->format('d/m/Y H:i:s') }}
                </td>
                <td class="p-4">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.site-settings-emails.form', $email->id) }}"
                            class="inline-flex items-center px-2 py-1 text-xs text-blue-500 rounded-md bg-blue-500/10 hover:bg-blue-500/20">
                            Edit
                        </a>

                        <button wire:click="deleteTemplate({{ $email->id }})"
                            wire:confirm="Are you sure you want to delete this template?"
                            class="inline-flex items-center px-2 py-1 text-xs text-red-500 rounded-md bg-red-500/10 hover:bg-red-500/20">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-neutral-500">
                    No email templates found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $systemEmails->links() }}
</div>
