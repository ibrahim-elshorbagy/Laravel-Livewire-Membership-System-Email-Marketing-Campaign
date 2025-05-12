<?php

namespace App\Mail;

use App\Models\Admin\Site\SystemSetting\SystemEmail;
use App\Models\Payment\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use App\Models\Admin\Site\SiteSetting;

class BaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $user;
    public $subscription;

    public function __construct($data)
    {
        $this->data = $data;
        $user_id = $data['user_id'] ?? null;
        $subscription_id = $data['subscription_id'] ?? null;

        if($user_id){
            $this->user = User::find($user_id);
        }

        if($subscription_id){
            $this->subscription = Subscription::with(['plan'])->withoutGlobalScopes([SuppressingScope::class, StartingScope::class])
            ->find($subscription_id);
        }

    }

    private function getSubscriptionStatus(): ?string
    {
        if (!$this->subscription) {
            return null;
        }

        $subscription = $this->subscription;

        if ($subscription->suppressed_at) {
            return 'Suppressed';
        }

        if ($subscription->canceled_at) {
            return 'Canceled';
        }

        if ($subscription->expired_at && now()->gt($subscription->expired_at)) {
            return 'Expired';
        }

        return 'Active';
    }

    public function build()
    {
        $emailTemplate = SystemEmail::where('slug', $this->data['slug'])->first();

        // Get the HTML template
        $templateHtml = $emailTemplate->message_html;

        // Create data array with all variables needed in the template
        $payment = null;
        if ($this->subscription) {
            $payment = Payment::where('subscription_id', $this->subscription->id)
                ->latest()
                ->first();
        }

        // Initialize base data array
        $data = [
            //General Info
            'site_name' => SiteSetting::getValue('site_name', config('app.name')),
            'site_logo' => SiteSetting::getLogoUrl(),


            // user profile information
            'full_name' => $this->user ? "{$this->user->first_name} {$this->user->last_name}" : null,
            'first_name' => $this->user?->first_name,
            'last_name' => $this->user?->last_name,
            'email' => $this->user?->email,
            'username' => $this->user?->username,
            'whatsapp' => $this->user?->whatsapp,
            'country' => $this->user?->country,

            // Subscription information
            'subscription_status' => $this->getSubscriptionStatus(),
            'subscription_start_date' => $this->subscription && $this->subscription->started_at ? Carbon::parse($this->subscription->started_at)->format('d/m/Y h:i:s A') : null,
            'subscription_end_date' => $this->subscription && $this->subscription->expired_at? Carbon::parse($this->subscription->expired_at)->format('d/m/Y h:i:s A'): null,
            'subscription_grace_days_ended_date' => $this->subscription && $this->subscription->grace_days_ended_at ? Carbon::parse($this->subscription->grace_days_ended_at)->format('d/m/Y h:i:s A') : null,
            'current_datetime' => now()->format('d/m/Y h:i:s A'),

            // Plan information
            'plan_name' => $this->subscription?->plan?->name,
            'plan_price' => $this->subscription?->plan?->price,
            'plan_duration' => $this->subscription?->plan?->periodicity_type,

            // Payment information
            'payment_gateway' => $payment?->gateway,
            'payment_note_or_gateway_order_id' => $payment?->gateway_subscription_id,
            'payment_transaction_id' => $payment?->transaction_id,
            'payment_amount' => $payment?->amount,
            'payment_currency' => $payment?->currency,
        ];


        // Merge any additional custom data from child classes
        if (isset($this->data['data']) && is_array($this->data['data'])) {
            $data = array_merge($data, $this->data['data']);
        }

        // email Template subject will be enforced
        $subject = !empty($emailTemplate->email_subject) ? $emailTemplate->email_subject : ($this->data['data']['subject'] ?? 'No Subject');
        $data['subject'] = $subject;

        // Render the template string directly with the data
        try {
            // Render the template string directly with the data
            $templateHtml = str_replace('&nbsp;', ' ', $templateHtml);
            $renderedHtml = html_entity_decode(Blade::render($templateHtml, $data));


            return $this->subject($subject)
                ->html($renderedHtml);
        } catch (\Exception $e) {
            Log::error('Failed to render email template: ' . $e->getMessage(), [
                'template_slug' => $this->data['slug'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
