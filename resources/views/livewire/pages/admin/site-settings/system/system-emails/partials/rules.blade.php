<div
    class="overflow-hidden p-4 my-4 w-full bg-white rounded-lg text-neutral-600 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400">
    <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Templates Notes</h3>

    <div class="space-y-2">

        <x-primary-accordion title="(support-ticket-user-request) When a user sent support ticket or Message" :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li>Email Subject is unnecessary - it is defined by the user when creating the ticket.</li>
                </ul>
            </div>

            <table class="w-full text-gray-600 dark:text-gray-300">
                <thead class="bg-neutral-50 dark:bg-neutral-700">
                    <tr>
                        <th class="px-4 py-2 text-left">Variable</th>
                        <th class="px-4 py-2 text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-2 font-mono">@{{ $subject }}</td>
                        <td class="px-4 py-2">Subject of the email</td>
                    </tr>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-2 font-mono">@{{ $name }}</td>
                        <td class="px-4 py-2">Name of the user submitting the support ticket</td>
                    </tr>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-2 font-mono">@{{ $email }}</td>
                        <td class="px-4 py-2">Email address of the submitter</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono">@{!! $messageContent !!}</td>
                        <td class="px-4 py-2">formatted content by user</td>
                    </tr>
                </tbody>
            </table>
        </x-primary-accordion>

        <x-primary-accordion title="(support-ticket-admin-response) When Admin Response To Support Ticket or Message" :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li>Email Subject is Being use here</li>
                </ul>
            </div>
        
            <table class="w-full text-gray-600 dark:text-gray-300">
                <thead class="bg-neutral-50 dark:bg-neutral-700">
                    <tr>
                        <th class="px-4 py-2 text-left">Variable</th>
                        <th class="px-4 py-2 text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-2 font-mono">@{{ $subject }}</td>
                        <td class="px-4 py-2">Subject of the email</td>
                    </tr>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-2 font-mono">@{{ $name }}</td>
                        <td class="px-4 py-2">Name of the user submitting the support ticket</td>
                    </tr>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-2 font-mono">@{{ $email }}</td>
                        <td class="px-4 py-2">Email address of the submitter</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono">@{!! $messageContent !!}</td>
                        <td class="px-4 py-2">formatted content by admin</td>
                    </tr>
                </tbody>
            </table>
        </x-primary-accordion>
    </div>
</div>