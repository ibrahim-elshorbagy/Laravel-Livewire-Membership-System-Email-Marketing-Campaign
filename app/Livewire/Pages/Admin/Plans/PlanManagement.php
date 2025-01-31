<?php

namespace App\Livewire\Pages\Admin\Plans;

use Livewire\Component;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

class PlanManagement extends Component
{    public $selectedTab = 'monthly';

    public function getMonthlyPlansProperty()
    {
        return Plan::where('periodicity_type', PeriodicityType::Month)
            ->with('features')
            ->get();
    }

    public function getYearlyPlansProperty()
    {
        return Plan::where('periodicity_type', PeriodicityType::Year)
            ->with('features')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.admin.plans.plan-management', [
            'monthlyPlans' => $this->monthlyPlans,
            'yearlyPlans' => $this->yearlyPlans,
        ])->layout('layouts.app',['title' => 'Plan Management']);
    }
}
