<?php

namespace App\Livewire\Pages\Admin\Dashboard;

use Livewire\Component;
use App\Models\Admin\Site\ApiRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardApiRequests extends Component
{
    public $startDate;
    public $endDate;
    public $customLabels = [];
    public $customData = [];

    protected function getStats($period)
    {
        $requests = ApiRequest::where('request_time', '>=', $period)
            ->selectRaw('COUNT(*) as count, AVG(execution_time) as avg_execution_time')
            ->first();

        return [
            'count' => $requests->count ?? 0,
            'avg_execution_time' => round($requests->avg_execution_time ?? 0, 3)
        ];
    }



    public function refresh()
    {
        $this->dispatch('stats-refreshed');
    }

    public $hourLabels = [];
    public $hourData = [];
    public $dayLabels = [];
    public $dayData = [];
    public $weekLabels = [];
    public $weekData = [];
    public $monthLabels = [];
    public $monthData = [];
    public $yearLabels = [];
    public $yearData = [];

    protected function getDetailedStats($period, $interval, $format)
    {
        $data = [];
        $labels = [];
        $current = Carbon::now();

        for ($i = 0; $i < $interval; $i++) {
            $endTime = $current->copy()->subMinutes($i * $period);
            $startTime = $current->copy()->subMinutes(($i + 1) * $period);

            $count = ApiRequest::where('request_time', '>=', $startTime)
                ->where('request_time', '<', $endTime)
                ->count();

            array_unshift($data, $count);
            array_unshift($labels, $endTime->format($format));
        }

        return [$labels, $data];
    }

    public function render()
    {
        $now = Carbon::now();

        // Get detailed stats for each time period
        [$this->hourLabels, $this->hourData] = $this->getDetailedStats(10, 6, 'H:i'); // Every 10 minutes for last hour
        [$this->dayLabels, $this->dayData] = $this->getDetailedStats(180, 8, 'H:i'); // Every 3 hours for last day
        [$this->weekLabels, $this->weekData] = $this->getDetailedStats(1440, 7, 'D M'); // Daily for last week
        [$this->monthLabels, $this->monthData] = $this->getDetailedStats(10080, 4, 'D M'); // Weekly for last month
        [$this->yearLabels, $this->yearData] = $this->getDetailedStats(43800, 12, 'M Y'); // Monthly for last year

        $stats = [
            'hour' => $this->getStats($now->copy()->subHour()),
            'day' => $this->getStats($now->copy()->subDay()),
            'week' => $this->getStats($now->copy()->subWeek()),
            'month' => $this->getStats($now->copy()->subMonth()),
            'year' => $this->getStats($now->copy()->subYear())
        ];

        return view('livewire.pages.admin.dashboard.dashboard-api-requests', [
            'stats' => $stats
        ]);
    }
}
