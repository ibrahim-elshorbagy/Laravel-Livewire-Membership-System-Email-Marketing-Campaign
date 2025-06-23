<?php

namespace App\Livewire\Pages\User\Emails\Campaign\Repeater;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRepeater;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class RepeaterForm extends Component
{
    use LivewireAlert;

    public $campaignId;
    public $repeaterId;
    public $campaign;
    public $intervalValue = 1;
    public $intervalType = 'days';
    public $totalRepeats = 1;
    public $active = true;

    /**
     * Define validation rules for repeater form inputs
     */
    protected function rules()
    {
        return [
            'intervalValue' => 'required|integer|min:1|max:365',
            'intervalType' => 'required|in:hours,days,weeks',
            'totalRepeats' => 'required|integer|min:1|max:50',
            'active' => 'boolean',
        ];
    }

    /**
     * Initialize component with campaign or existing repeater data
     */
    public function mount($campaign = null, $repeater = null)
    {
        if ($repeater) {
            // Validate repeater ID using Laravel validator
            $validator = Validator::make(['repeater' => $repeater], [
                'repeater' => 'required|integer|min:1|exists:campaign_repeaters,id'
            ]);

            if ($validator->fails()) {
                return redirect()->route('user.campaigns.repeaters.list');
            }

            $this->repeaterId = $repeater;
            $repeaterModel = CampaignRepeater::with('campaign')->findOrFail($repeater);

            if ($repeaterModel->user_id != Auth::id()) {
                return redirect()->route('user.campaigns.repeaters.list');
            }

            $this->campaignId = $repeaterModel->campaign_id;
            $this->campaign = $repeaterModel->campaign;

            // Convert interval hours to appropriate unit
            if ($repeaterModel->interval_type === 'hours') {
                $this->intervalValue = (int) $repeaterModel->interval_hours;
            } elseif ($repeaterModel->interval_type === 'days') {
                $this->intervalValue = (int) ($repeaterModel->interval_hours / 24);
            } else { // weeks
                $this->intervalValue = (int) ($repeaterModel->interval_hours / (24 * 7));
            }

            $this->intervalType = $repeaterModel->interval_type;
            $this->totalRepeats = $repeaterModel->total_repeats;
            $this->active = $repeaterModel->active;
        }
        elseif ($campaign) {
            // Validate campaign ID using Laravel validator
            $validator = Validator::make(['campaign' => $campaign], [
                'campaign' => 'required|integer|min:1|exists:campaigns,id'
            ]);

            if ($validator->fails()) {
                return redirect()->route('user.campaigns.list');
            }

            $this->campaignId = $campaign;
            $this->campaign = Campaign::findOrFail($campaign);

            // Only allow repeater creation for campaigns owned by the user
            if ($this->campaign->user_id != Auth::id()) {
                return redirect()->route('user.campaigns.list');
            }

            // Prevent creating repeaters for completed campaigns (no servers available)
            if ($this->campaign->status === Campaign::STATUS_COMPLETED) {
                Session::flash('error', 'Cannot create repeater for completed campaigns. Completed campaigns have no servers assigned.');
                return redirect()->route('user.campaigns.list');
            }

            // Check if repeater already exists
            $existingRepeater = CampaignRepeater::where('campaign_id', $this->campaignId)->first();
            if ($existingRepeater) {
                return redirect()->route('user.campaigns.repeaters.form', ['repeater' => $existingRepeater->id]);
            }

        }
        else {
            return redirect()->route('user.campaigns.list');
        }
    }

    /**
     * Save or update the campaign repeater configuration
     */
    public function saveRepeater()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Convert interval to hours for storage
            $intervalHours = $this->intervalValue;
            if ($this->intervalType === 'days') {
                $intervalHours = $this->intervalValue * 24;
            } elseif ($this->intervalType === 'weeks') {
                $intervalHours = $this->intervalValue * 24 * 7;
            }

            $repeaterData = [
                'user_id' => Auth::id(),
                'campaign_id' => $this->campaignId,
                'interval_hours' => $intervalHours,
                'interval_type' => $this->intervalType,
                'total_repeats' => $this->totalRepeats,
                'active' => $this->active,
            ];

            if ($this->repeaterId) {
                // When updating existing repeater, don't reset completed_repeats
                $repeater = CampaignRepeater::findOrFail($this->repeaterId);
                $oldTotalRepeats = $repeater->total_repeats;
                $completedRepeats = $repeater->completed_repeats;

                $repeater->update($repeaterData);

                // Check if user increased total_repeats for a completed repeater
                if ($this->totalRepeats > $oldTotalRepeats && $completedRepeats >= $oldTotalRepeats) {
                    // Repeater was completed but user increased total repeats, create new clone
                    $campaignRepeaterService = new \App\Services\CampaignRepeaterService();
                    $campaignRepeaterService->createNewCloneForIncreasedRepeats($repeater);
                }
            } else {
                // Only set completed_repeats to 0 for new repeaters
                $repeaterData['completed_repeats'] = 0;
                $repeater = CampaignRepeater::create($repeaterData);
            }

            
            $campaignRepeaterService = new \App\Services\CampaignRepeaterService();
            $campaignRepeaterService->checkAndActivateScheduledCampaigns();

            DB::commit();

            Session::flash('success', 'Campaign repeater saved successfully.');
            return redirect()->route('user.campaigns.repeaters.list');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Failed to save repeater: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    /**
     * Render the repeater form view
     */
    public function render()
    {
        return view('livewire.pages.user.emails.campaign.repeater.repeater-form', [
            'campaign' => $this->campaign,
        ])->layout('layouts.app', ['title' => $this->campaignId ? 'Edit Campaign Repeater' : 'New Campaign Repeater']);
    }
}
