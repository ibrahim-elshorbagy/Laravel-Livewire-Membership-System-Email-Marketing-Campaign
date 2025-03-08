<div x-data="{ refreshing: false }">
    <!-- Header with Refresh Button -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">API Request Statistics</h2>
        <button
            @click="refreshing = true; $wire.refresh().then(() => { refreshing = false; $dispatch('refresh-charts') })"
            :class="{ 'opacity-50 cursor-not-allowed': refreshing }"
            class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 ease-in-out dark:text-white bg-primary-600 hover:bg-primary-700">
            <i class="mr-2 fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
            <span x-text="refreshing ? 'Refreshing...' : 'Refresh'"></span>
        </button>
    </div>

    <!-- Time-based Stats Grid -->
    <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
        <!-- Hourly Stats -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-blue-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-blue-500 dark:text-blue-400 fas fa-clock"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-blue-500 bg-blue-50 rounded-full dark:text-blue-400 dark:bg-blue-500/10">Last
                        Hour</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Requests</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{number_format($stats['hour']['count'])}}</p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg. Execution Time</p>
                    <p class="text-xl font-semibold text-blue-600 dark:text-blue-400">{{$stats['hour']['avg_execution_time'] }}ms</p>
                </div>
                <div class="mt-4" x-data="{ chart: null, initChart() { chart = new Chart(document.getElementById('hourlyChart').getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: $wire.hourLabels,
                            datasets: [{
                                label: 'Requests per Hour',
                                data: $wire.hourData,
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 2,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    }); }, refreshChart() { if(this.chart) { this.chart.destroy(); } this.initChart(); } }"
                    x-init="initChart()" @refresh-charts.window="refreshChart()">
                    <canvas id="hourlyChart" style="height: 200px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Stats -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-green-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-green-500 dark:text-green-400 fas fa-calendar-day"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-green-500 bg-green-50 rounded-full dark:text-green-400 dark:bg-green-500/10">Last
                        24 Hours</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Requests</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{number_format($stats['day']['count']) }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg. Execution Time</p>
                    <p class="text-xl font-semibold text-green-600 dark:text-green-400">{{$stats['day']['avg_execution_time'] }}ms</p>
                </div>
                <div class="mt-4" x-data="{ chart: null, initChart() { chart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: $wire.dayLabels,
                            datasets: [{
                                label: 'Requests per Day',
                                data: $wire.dayData,
                                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 2,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    }); }, refreshChart() { if(this.chart) { this.chart.destroy(); } this.initChart(); } }"
                    x-init="initChart()" @refresh-charts.window="refreshChart()">
                    <canvas id="dailyChart" style="height: 200px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Weekly Stats -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-yellow-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-yellow-500 dark:text-yellow-400 fas fa-calendar-week"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-yellow-500 bg-yellow-50 rounded-full dark:text-yellow-400 dark:bg-yellow-500/10">Last
                        Week</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Requests</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{number_format($stats['week']['count']) }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg. Execution Time</p>
                    <p class="text-xl font-semibold text-yellow-600 dark:text-yellow-400">{{$stats['week']['avg_execution_time'] }}ms</p>
                </div>
                <div class="mt-4" x-data="{ chart: null, initChart() { chart = new Chart(document.getElementById('weeklyChart').getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: $wire.weekLabels,
                            datasets: [{
                                label: 'Requests per Week',
                                data: $wire.weekData,
                                backgroundColor: 'rgba(234, 179, 8, 0.2)',
                                borderColor: 'rgba(234, 179, 8, 1)',
                                borderWidth: 2,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    }); }, refreshChart() { if(this.chart) { this.chart.destroy(); } this.initChart(); } }"
                    x-init="initChart()" @refresh-charts.window="refreshChart()">
                    <canvas id="weeklyChart" style="height: 200px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Stats -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-red-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-red-500 dark:text-red-400 fas fa-calendar-alt"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-red-500 bg-red-50 rounded-full dark:text-red-400 dark:bg-red-500/10">Last
                        Month</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Requests</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{number_format($stats['month']['count'])}}</p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg. Execution Time</p>
                    <p class="text-xl font-semibold text-red-600 dark:text-red-400">{{$stats['month']['avg_execution_time']}}ms</p>
                </div>
                <div class="mt-4" x-data="{ chart: null, initChart() { chart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: $wire.monthLabels,
                            datasets: [{
                                label: 'Requests per Month',
                                data: $wire.monthData,
                                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderWidth: 2,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    }); }, refreshChart() { if(this.chart) { this.chart.destroy(); } this.initChart(); } }"
                    x-init="initChart()" @refresh-charts.window="refreshChart()">
                    <canvas id="monthlyChart" style="height: 200px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Yearly Stats -->
        <div
            class="overflow-hidden relative bg-white rounded-lg border shadow-sm transition-all duration-300 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-700 group hover:shadow-md">
            <div class="absolute inset-0 bg-gradient-to-br to-transparent from-indigo-500/10"></div>
            <div class="relative p-3 lg:p-6">
                <div class="flex justify-between items-center">
                    <i class="text-2xl text-indigo-500 dark:text-indigo-400 fas fa-calendar"></i>
                    <span
                        class="px-2 py-1 text-xs font-medium text-indigo-500 bg-indigo-50 rounded-full dark:text-indigo-400 dark:bg-indigo-500/10">Last
                        Year</span>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Requests</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{number_format($stats['year']['count']) }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg. Execution Time</p>
                    <p class="text-xl font-semibold text-indigo-600 dark:text-indigo-400">{{$stats['year']['avg_execution_time'] }}ms</p>
                </div>
                <div class="mt-4" x-data="{ chart: null, initChart() { chart = new Chart(document.getElementById('yearlyChart').getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: $wire.yearLabels,
                            datasets: [{
                                label: 'Requests per Year',
                                data: $wire.yearData,
                                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                                borderColor: 'rgba(99, 102, 241, 1)',
                                borderWidth: 2,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    }); }, refreshChart() { if(this.chart) { this.chart.destroy(); } this.initChart(); } }"
                    x-init="initChart()" @refresh-charts.window="refreshChart()">
                    <canvas id="yearlyChart" style="height: 200px;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
@endpush
