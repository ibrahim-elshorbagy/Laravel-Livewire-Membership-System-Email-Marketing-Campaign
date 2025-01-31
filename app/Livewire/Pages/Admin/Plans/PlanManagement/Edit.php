<?php

namespace App\Livewire\Pages\Admin\Plans\PlanManagement;

use Livewire\Component;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    use LivewireAlert;

    public Plan $plan;
    public $name;
    public $price;
    // public $periodicity_type;
    // public $periodicity;

    // Features management
    public $features = [];
    public $availableFeatures = [];
    public $selectedFeature = '';
    public $featureLimit;

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        // 'periodicity' => 'required|integer|min:1',
        // 'periodicity_type' => 'required|in:month,year',
        'features.*.limit' => 'nullable|integer|min:0',
    ];

    public function mount(Plan $plan)
    {
        $this->plan = $plan;
        $this->name = $plan->name;
        $this->price = $plan->price;
        // $this->periodicity = $plan->periodicity;
        // $this->periodicity_type = strtolower($plan->periodicity_type);

        $this->loadFeatures();
        $this->loadAvailableFeatures();
    }

    public function loadFeatures()
    {
        $this->features = $this->plan->features->mapWithKeys(function ($feature) {
            return [$feature->id => [
                'id' => $feature->id,
                'name' => $feature->name,
                'limit' => (int) $feature->pivot->charges,
            ]];
        })->toArray();
    }

    public function loadAvailableFeatures()
    {
        $this->availableFeatures = Feature::whereNotIn('id', collect($this->features)->pluck('id'))
            ->get()
            ->toArray();
    }

    public function attachFeature()
    {
        $this->validate([
            'selectedFeature' => 'required|exists:features,id',
            'featureLimit' => 'required|integer|min:0',
        ]);

        try {
            $this->plan->features()->attach($this->selectedFeature, ['charges' => $this->featureLimit]);
            $this->loadFeatures();
            $this->loadAvailableFeatures();
            $this->reset(['selectedFeature', 'featureLimit']);
            $this->alert('success', 'Feature added successfully.',['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to add feature.',['position' => 'bottom-end']);
        }
    }

    public function updateFeatureLimit($featureId)
    {
        $this->validate([
            "features.$featureId.limit" => 'required|integer|min:0',
        ]);

        try {
            $this->plan->features()->updateExistingPivot($featureId, [
                'charges' => $this->features[$featureId]['limit'],
            ]);
            $this->alert('success', 'Feature limit updated successfully.',['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to update feature limit.',['position' => 'bottom-end']);
        }
    }

    public function detachFeature($featureId)
    {
        try {
            $this->plan->features()->detach($featureId);
            $this->loadFeatures();
            $this->loadAvailableFeatures();
            $this->alert('success', 'Feature removed successfully.', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to remove feature.',['position' => 'bottom-end']);
        }
    }

    public function updatePlan()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->plan->update([
                'name' => $this->name,
                'price' => $this->price,
                // 'periodicity' => $this->periodicity,
                // 'periodicity_type' => $this->periodicity_type === 'month'   ? PeriodicityType::Month   : PeriodicityType::Year,
            ]);

            DB::commit();
            Session::flash('success', 'Plan updated successfully.');
            return $this->redirect(route('admin.plans'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Failed to update plan.');
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.plans.plan-management.edit')
            ->layout('layouts.app',['title' => 'Edit Plan']);
    }
}
