<?php

namespace App\Livewire\Pages\Admin\Plans\PlanManagement;

use Livewire\Component;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Feature;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    use LivewireAlert;

    public Plan $plan;
    public $name;
    public $price;
    public $featureLimits = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'featureLimits.*' => 'nullable|min:0',
    ];

    public function mount(Plan $plan)
    {
        $this->plan = $plan;
        $this->name = $plan->name;
        $this->price = $plan->price;

        // Initialize feature limits for all features
        $attachedCharges = $plan->features()->pluck('charges', 'feature_id')->toArray();

        Feature::all()->each(function ($feature) use ($attachedCharges) {
            $this->featureLimits[$feature->id] = $attachedCharges[$feature->id] ?? null;
        });
    }

    public function updatePlan()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Update plan details
            $this->plan->update([
                'name' => $this->name,
                'price' => $this->price,
            ]);

            // Prepare features to sync
            $featuresToSync = [];
            foreach ($this->featureLimits as $featureId => $limit) {
                if (is_numeric($limit) && $limit >= 0) {
                    $featuresToSync[$featureId] = ['charges' => (int) $limit];
                }
            }

            // Sync features
            $this->plan->features()->sync($featuresToSync);

            DB::commit();
            Session::flash('success', 'Plan updated successfully.');
            return $this->redirect(route('admin.plans',['tab' => $this->plan->periodicity_type === 'Year' ? 'yearly' : 'monthly']), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Failed to update plan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.plans.plan-management.edit', [
            'features' => Feature::all(),
        ])->layout('layouts.app', ['title' => 'Edit Plan']);
    }
}
