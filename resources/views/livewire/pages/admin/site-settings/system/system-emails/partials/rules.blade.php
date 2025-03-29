<div
    class="overflow-hidden p-4 my-4 w-full bg-white rounded-lg text-neutral-600 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400">
    <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Templates Notes</h3>

    <div>

        <x-primary-accordion title="All System Emails" :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li>All of these Emails Must Be Built.</li>
                </ul>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-gray-600 dark:text-gray-300">
                    <thead class="bg-neutral-50 dark:bg-neutral-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Email Slug</th>
                            <th class="px-4 py-2 text-left">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Support Emails -->
                        <tr class="bg-blue-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-blue-900">
                            <td colspan="3" class="px-6 py-4">
                                <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-200">Support Emails</h4>
                            </td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">support-ticket-user-request</td>
                            <td class="px-4 py-2">Sent to admin when a user submits a new support ticket</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">support-ticket-admin-response</td>
                            <td class="px-4 py-2">Sent to user when an admin responds to their support ticket</td>
                        </tr>

                        <!-- Subscription Emails -->
                        <tr class="bg-green-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-green-900">
                            <td colspan="3" class="px-6 py-4">
                                <h4 class="text-lg font-semibold text-green-800 dark:text-green-200">Subscription Emails
                                </h4>
                            </td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">admin-cancelled-subscription</td>
                            <td class="px-4 py-2">Notification to user when admin cancels their subscription</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">admin-suppressed-subscription</td>
                            <td class="px-4 py-2">Notification to user when admin suppresses their subscription</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">admin-reactivated-subscription</td>
                            <td class="px-4 py-2">Notification to user when admin reactivates their subscription</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">user-start-new-subscription</td>
                            <td class="px-4 py-2">Confirmation email when user starts a new subscription</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">user-renew-subscription</td>
                            <td class="px-4 py-2">Confirmation email when user renews their subscription</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">user-cancel-subscription</td>
                            <td class="px-4 py-2">Confirmation email when user cancels their subscription</td>
                        </tr>

                        <!-- System Emails -->
                        <tr class="bg-amber-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-amber-900">
                            <td colspan="3" class="px-6 py-4">
                                <h4 class="text-lg font-semibold text-amber-800 dark:text-amber-200">System Emails
                                </h4>
                            </td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">user-email-verification</td>
                            <td class="px-4 py-2">Email containing verification link for new user registration</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">user-forgot-password</td>
                            <td class="px-4 py-2">Email containing password reset link for forgot password requests</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-primary-accordion>

        <x-primary-accordion title="General Info" :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li>Can be use on all emails.</li>
                </ul>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-gray-600 dark:text-gray-300">
                    <thead class="bg-neutral-50 dark:bg-neutral-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Variable</th>
                            <th class="px-4 py-2 text-left">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">@{{ $site_name }}</td>
                            <td class="px-4 py-2">Name</td>
                        </tr>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-2 font-mono">@{{ $site_logo }}</td>
                            <td class="px-4 py-2">Logo</td>
                        </tr>


                    </tbody>
                </table>
            </div>
        </x-primary-accordion>

        <x-primary-accordion title="Support Ticket"
            :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li><strong>You Can Use All info from subscription tabel</strong>,To show current subscription </li>
                    <li>(support-ticket-user-request) When a user sent support ticket or Message</li>
                    <li>If you set the email subject, it will be enforced, and the client won't be able to write their own email subject</li>
                    <li>(support-ticket-admin-response) When Admin Response To Support Ticket or Message</li>
                </ul>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-gray-600 dark:text-gray-300">
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
                        <tr>
                            <td class="px-4 py-2 font-mono">@{{ $ticket_id }}</td>
                            <td class="px-4 py-2">Id of the ticket</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-primary-accordion>

        <x-primary-accordion title="For Subscription Notifications" :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li>These variables are available for all subscription-related notifications</li>
                    <li>Subscription slugs: subscription-activated, subscription-renewed, subscription-cancelled,
                        subscription-suppressed, subscription-reactivated</li>
                    <li>All dates are formatted as 'd/m/Y h:i:s A' (01/01/2024 12:00:00 AM)</li>
                    <li>Price values are automatically formatted with 2 decimal places</li>
                </ul>
            </div>

            <!-- User Information Section -->
            <div class="mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[500px] text-gray-600 dark:text-gray-300">
                        <thead class="bg-neutral-50 dark:bg-neutral-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Variable</th>
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-left">Example Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- User Information Section Header -->
                            <tr class="bg-blue-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-blue-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-200">User Information
                                    </h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $first_name }}</td>
                                <td class="px-4 py-2">User's first name</td>
                                <td class="px-4 py-2 italic">"Dear @{{ $first_name }},"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $last_name }}</td>
                                <td class="px-4 py-2">User's last name</td>
                                <td class="px-4 py-2 italic">"Mr/Ms @{{ $last_name }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $full_name }}</td>
                                <td class="px-4 py-2">User's full name</td>
                                <td class="px-4 py-2 italic">"Welcome, @{{ $full_name }}!"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $email }}</td>
                                <td class="px-4 py-2">User's email address</td>
                                <td class="px-4 py-2 italic">"Your registered email: @{{ $email }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $username }}</td>
                                <td class="px-4 py-2">User's username</td>
                                <td class="px-4 py-2 italic">"Username: @{{ $username }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $whatsapp }}</td>
                                <td class="px-4 py-2">User's WhatsApp number</td>
                                <td class="px-4 py-2 italic">"WhatsApp: @{{ $whatsapp }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $country }}</td>
                                <td class="px-4 py-2">User's country</td>
                                <td class="px-4 py-2 italic">"Location: @{{ $country }}"</td>
                            </tr>

                            <!-- Subscription Details Section Header -->
                            <tr
                                class="bg-green-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-green-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-green-800 dark:text-green-200">Subscription
                                        Details</h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $subscription_status }}</td>
                                <td class="px-4 py-2">subscription status</td>
                                <td class="px-4 py-2 italic">"Status: @{{ $subscription_status }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $plan_name }}</td>
                                <td class="px-4 py-2">Name of the subscription plan</td>
                                <td class="px-4 py-2 italic">"Plan: @{{ $plan_name }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $plan_duration }}</td>
                                <td class="px-4 py-2">Plan duration type</td>
                                <td class="px-4 py-2 italic">"Billing cycle: @{{ $plan_duration }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ number_format($plan_price, 2) }}</td>
                                <td class="px-4 py-2">Plan price with 2 decimal places</td>
                                <td class="px-4 py-2 italic">"Price: $@{{ number_format($plan_price, 2) }}"</td>
                            </tr>

                            <!-- Important Dates Section Header -->
                            <tr
                                class="bg-purple-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-purple-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-purple-800 dark:text-purple-200">Important
                                        Dates
                                        (please use it with the '?' mark)
                                    </h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $subscription_start_date?->format('d/m/Y h:i:sA') }}
                                </td>
                                <td class="px-4 py-2">Subscription start date</td>
                                <td class="px-4 py-2 italic">"Started: @{{ $subscription_start_date?->format('d/m/Y
                                    h:i:sA') }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $subscription_end_date?->format('d/m/Y h:i:s A') }}
                                </td>
                                <td class="px-4 py-2">Subscription end date</td>
                                <td class="px-4 py-2 italic">"Ends: @{{ $subscription_end_date?->format('d/m/Y h:i:s A')
                                    }}"
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $subscription_grace_days_ended_date?->format('d/m/Y
                                    h:i:s A') }}</td>
                                <td class="px-4 py-2">Grace period end date</td>
                                <td class="px-4 py-2 italic">"Grace period ends: @{{
                                    $subscription_grace_days_ended_date?->format('d/m/Y h:i:s A') }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ now()->format('d/m/Y h:i:s A') }}</td>
                                <td class="px-4 py-2">Current date and time</td>
                                <td class="px-4 py-2 italic">"Generated on: @{{ now()->format('d/m/Y h:i:s A') }}"</td>
                            </tr>

                            <!-- Payment Information Section Header -->
                            <tr
                                class="bg-amber-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-amber-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-amber-800 dark:text-amber-200">Payment
                                        Information
                                    </h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $payment_gateway }}</td>
                                <td class="px-4 py-2">Payment gateway used</td>
                                <td class="px-4 py-2 italic">"Paid via @{{ $payment_gateway }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $payment_transaction_id }}</td>
                                <td class="px-4 py-2">Transaction ID</td>
                                <td class="px-4 py-2 italic">"Transaction ID: @{{ $payment_transaction_id }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $payment_note_or_gateway_order_id }}</td>
                                <td class="px-4 py-2">Paypal Order ID Or Note</td>
                                <td class="px-4 py-2 italic">"Paypal Order ID: @{{ $payment_note_or_gateway_order_id }}"
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $payment_amount }}</td>
                                <td class="px-4 py-2">Payment amount</td>
                                <td class="px-4 py-2 italic">"Amount paid: @{{ $payment_amount }} @{{ $payment_currency
                                    }}"
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $payment_currency }}</td>
                                <td class="px-4 py-2">Payment currency</td>
                                <td class="px-4 py-2 italic">"Currency: @{{ $payment_currency }}"</td>
                            </tr>
                        </tbody>
                    </table>
                </div>



                <!-- Complete Examples Section -->
                <div class="p-4 mt-4 bg-blue-50 rounded-lg dark:bg-blue-900">
                    <h4 class="mb-4 text-lg font-semibold text-blue-800 dark:text-blue-200">Complete Template Examples
                    </h4>
                    <div class="space-y-4 text-blue-700 dark:text-blue-300">
                        <div class="p-3 bg-white rounded dark:bg-blue-800">
                            <h5 class="mb-2 font-medium">Welcome Email</h5>
                            <pre class="text-sm whitespace-pre-wrap">Dear @{{ $first_name }},
Welcome to our service! Your @{{ $plan_name }} subscription has been successfully activated.

    Subscription Details:
        - Plan: @{{ $plan_name }}
        - Duration: @{{ $plan_duration }}
        - Amount: $@{{ number_format($plan_price, 2) }}
        - Start Date: @{{ $subscription_start_date->format('d/m/Y h:i:s A') }}

Best regards,
The Team</pre>
                        </div>

                        <div class="p-3 bg-white rounded dark:bg-blue-800">
                            <h5 class="mb-2 font-medium">Payment Confirmation</h5>
                            <pre class="text-sm whitespace-pre-wrap">Hello @{{ $full_name }},
Your payment has been successfully processed:

    Transaction Details:
        - Amount: @{{ $payment_amount }} @{{ $payment_currency }}
        - Gateway: @{{ $payment_gateway }}
        - Transaction ID: @{{ $payment_transaction_id }}
        - Date: @{{ now()->format('d/m/Y h:i:s A') }}

Thank you for your business!</pre>
                        </div>
                    </div>
                </div>


        </x-primary-accordion>

        <x-primary-accordion title="For Email Verification And Forget Password" :isExpandedByDefault="false">
            <div class="p-4 mb-4 rounded-lg bg-neutral-50 dark:bg-neutral-700">
                <h4 class="mb-2 text-lg font-semibold text-neutral-800 dark:text-neutral-200">Notes:</h4>
                <ul class="list-disc list-inside text-neutral-600 dark:text-neutral-300">
                    <li>User Variables</li>
                    <li>Email Verification Variables (user-email-verification)</li>
                    <li>Forget Password Variables (user-forgot-password)</li>
                </ul>
            </div>

            <!-- User Information Section -->
            <div class="mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[500px] text-gray-600 dark:text-gray-300">
                        <thead class="bg-neutral-50 dark:bg-neutral-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Variable</th>
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-left">Example Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- User Information Section Header -->
                            <tr class="bg-blue-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-blue-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-200">User Information
                                    </h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $first_name }}</td>
                                <td class="px-4 py-2">User's first name</td>
                                <td class="px-4 py-2 italic">"Dear @{{ $first_name }},"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $last_name }}</td>
                                <td class="px-4 py-2">User's last name</td>
                                <td class="px-4 py-2 italic">"Mr/Ms @{{ $last_name }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $full_name }}</td>
                                <td class="px-4 py-2">User's full name</td>
                                <td class="px-4 py-2 italic">"Welcome, @{{ $full_name }}!"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $email }}</td>
                                <td class="px-4 py-2">User's email address</td>
                                <td class="px-4 py-2 italic">"Your registered email: @{{ $email }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $username }}</td>
                                <td class="px-4 py-2">User's username</td>
                                <td class="px-4 py-2 italic">"Username: @{{ $username }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $whatsapp }}</td>
                                <td class="px-4 py-2">User's WhatsApp number</td>
                                <td class="px-4 py-2 italic">"WhatsApp: @{{ $whatsapp }}"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $country }}</td>
                                <td class="px-4 py-2">User's country</td>
                                <td class="px-4 py-2 italic">"Location: @{{ $country }}"</td>
                            </tr>



                            <!-- Payment Information Section Header -->
                            <tr
                                class="bg-amber-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-amber-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-amber-800 dark:text-amber-200">Email
                                        Verification
                                    </h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $verification_url }}
                                <td class="px-4 py-2">Verification Link</td>
                                <td class="px-4 py-2 italic">"Use @{{ $verification_url }} To Verify Your Email"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $url_expired_after }}</td>
                                <td class="px-4 py-2">Link Expired After</td>
                                <td class="px-4 py-2 italic">"Link Expired After @{{ $url_expired_after }} (60 minute)"
                                </td>
                            </tr>

                            <!-- Payment Information Section Header -->
                            <tr
                                class="bg-amber-50 border-b border-neutral-200 dark:border-neutral-700 dark:bg-amber-900">
                                <td colspan="3" class="px-6 py-4">
                                    <h4 class="text-lg font-semibold text-amber-800 dark:text-amber-200">Forget Password
                                    </h4>
                                </td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $reset_url }}
                                <td class="px-4 py-2">Reset Password Link</td>
                                <td class="px-4 py-2 italic">"Use @{{ $reset_url }} To Reset Your Password"</td>
                            </tr>
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-2 font-mono">@{{ $url_expired_after }}</td>
                                <td class="px-4 py-2">Link Expired After</td>
                                <td class="px-4 py-2 italic">"Link Expired After @{{ $url_expired_after }} (60 minute)"
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>



                <!-- Complete Examples Section -->
                <div class="p-4 mt-4 bg-blue-50 rounded-lg dark:bg-blue-900">
                    <h4 class="mb-4 text-lg font-semibold text-blue-800 dark:text-blue-200">Complete Template Examples
                    </h4>
                    <div class="space-y-4 text-blue-700 dark:text-blue-300">

                        <div class="p-3 bg-white rounded dark:bg-blue-800">
                            <h5 class="mb-2 font-medium">Verify Email Address</h5>
                            <pre class="text-sm whitespace-pre-wrap">
Hello @{{ $full_name }},

Subject: Verify Your Email Address

Hi @{{ $email }},

Thank you for signing up! Please verify your email address by Entering the link below:

</pre>
                            <div class="px-2 py-1 my-2 text-sm text-white bg-blue-600 rounded-lg w-fit">
                                &lt;a href="@{{ $verification_url }}"&gt;Verify&lt;/a&gt;
                            </div>
                            <pre class="text-sm whitespace-pre-wrap">

This link will expire in @{{ $url_expired_after }}.

If you did not create an account, please ignore this email.

Best regards,
The Support Team
</pre>
                        </div>
                    </div>
                </div>



        </x-primary-accordion>

    </div>
</div>