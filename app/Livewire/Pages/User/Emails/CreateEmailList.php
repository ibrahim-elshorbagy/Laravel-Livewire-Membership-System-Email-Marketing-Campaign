<?php
namespace App\Livewire\Pages\User\Emails;

use Livewire\Component;
use App\Models\EmailList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;
use LucasDotVin\Soulbscription\Models\Feature;

class CreateEmailList extends Component
{
    use LivewireAlert;

    public $emails = [];
    public $remainingQuota = 0;
    public $user;
    public function mount()
    {
        $this->user = auth()->user();
        $this->remainingQuota = $this->user->balance('Subscribers Limit');

        if ($this->remainingQuota == 0) {
            return redirect()->route('welcome');
        }
    }


    public function saveEmails($emails)
    {
        try {
            $emailsCount = count($emails);

            // Check quota
            if (!$this->user->canConsume('Subscribers Limit', $emailsCount)) {
                $this->alert('error', 'Not enough quota remaining', [
                    'position' => 'bottom-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }

            DB::transaction(function() use ($emails) {
                // Filter out existing emails
                $existingEmails = EmailList::whereIn('email', $emails)->pluck('email')->toArray();
                $newEmails = array_diff($emails, $existingEmails);

                // Insert only new emails
                if (!empty($newEmails)) {
                    EmailList::insert(collect($newEmails)->map(fn($email) => [
                        'user_id' => $this->user->id,
                        'email' => $email,
                        'created_at' => now(),
                        'updated_at' => now()
                    ])->toArray());

                    // Update quota
                    $totalEmailCount = EmailList::where('user_id', $this->user->id)->count();
                    $this->user->setConsumedQuota('Subscribers Limit', (float) $totalEmailCount);
                }
            });

            Session::flash('success', 'Emails saved successfully.');

            return $this->redirect(route('user.emails.index'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'An error occurred', [
                'position' => 'bottom-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }


    public function render()
    {
        return view('livewire.pages.user.emails.create-email-list')
            ->layout('layouts.app', ['title' => 'Add Email List']);
    }
}
