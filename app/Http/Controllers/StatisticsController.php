<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Display the statistics dashboard.
     */
    public function index(HttpRequest $request): Response
    {
        // Period filter (default: last 30 days)
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int)$period);
        $endDate = Carbon::now();

        // Custom date range if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        }

        $stats = [
            'overview' => $this->getOverviewStats(),
            'requests' => $this->getRequestStats($startDate, $endDate),
            'items' => $this->getItemStats($startDate, $endDate),
            'users' => $this->getUserStats($startDate, $endDate),
            'trends' => $this->getTrendStats($startDate, $endDate),
            'chart_data' => $this->getDailyRequestChart($startDate, $endDate),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate)
            ]
        ];

        return Inertia::render('Statistics/AdvancedDashboard', [
            'stats' => $stats,
            'filters' => [
                'period' => $period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ]);
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats(): array
    {
        return [
            'total_items' => Item::count(),
            'available_items' => Item::where('status', 'available')->count(),
            'total_requests' => Request::count(),
            'pending_requests' => Request::where('status', 'pending')->count(),
            'approved_requests' => Request::where('status', 'approved')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_quantity' => Item::sum('quantity'),
            'available_quantity' => Item::where('status', 'available')->sum('quantity')
        ];
    }

    /**
     * Get request statistics for the period.
     */
    private function getRequestStats(Carbon $startDate, Carbon $endDate): array
    {
        $requests = Request::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_period' => $requests->count(),
            'by_status' => $requests->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status')
                ->toArray(),
            'by_priority' => $requests->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority')
                ->toArray(),
            'by_type' => $requests->groupBy('request_type')
                ->selectRaw('request_type, count(*) as count')
                ->pluck('count', 'request_type')
                ->toArray(),
            'daily_requests' => $this->getDailyRequestChart($startDate, $endDate),
            'average_per_day' => round($requests->count() / max(1, $startDate->diffInDays($endDate)), 2),
            'approval_rate' => $this->getApprovalRate($startDate, $endDate)
        ];
    }

    /**
     * Get item statistics for the period.
     */
    private function getItemStats(Carbon $startDate, Carbon $endDate): array
    {
        // Most requested items
        $mostRequested = Request::select('item_id', 'items.name')
            ->join('items', 'requests.item_id', '=', 'items.id')
            ->whereBetween('requests.created_at', [$startDate, $endDate])
            ->whereNotNull('item_id')
            ->groupBy('item_id', 'items.name')
            ->selectRaw('item_id, items.name, count(*) as requests_count')
            ->orderByDesc('requests_count')
            ->limit(10)
            ->get();

        // Items by category popularity
        $categoryStats = Request::select('items.category')
            ->join('items', 'requests.item_id', '=', 'items.id')
            ->whereBetween('requests.created_at', [$startDate, $endDate])
            ->whereNotNull('item_id')
            ->groupBy('items.category')
            ->selectRaw('items.category, count(*) as requests_count')
            ->orderByDesc('requests_count')
            ->get();

        // Low stock items
        $lowStockItems = Item::where('status', 'available')
            ->get()
            ->filter(function ($item) {
                return $item->getAvailableQuantity() <= 2 && $item->getAvailableQuantity() > 0;
            })
            ->values();

        return [
            'most_requested' => $mostRequested,
            'by_category' => $categoryStats,
            'low_stock' => $lowStockItems,
            'total_categories' => Item::distinct('category')->count('category'),
            'items_never_requested' => Item::whereDoesntHave('requests')->count(),
            'average_item_utilization' => $this->getAverageItemUtilization()
        ];
    }

    /**
     * Get user statistics for the period.
     */
    private function getUserStats(Carbon $startDate, Carbon $endDate): array
    {
        $topUsers = Request::select('user_id', 'users.name')
            ->join('users', 'requests.user_id', '=', 'users.id')
            ->whereBetween('requests.created_at', [$startDate, $endDate])
            ->groupBy('user_id', 'users.name')
            ->selectRaw('user_id, users.name, count(*) as requests_count')
            ->orderByDesc('requests_count')
            ->limit(10)
            ->get();

        $activeUsers = Request::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        return [
            'top_requesters' => $topUsers,
            'active_users' => $activeUsers,
            'total_users' => User::where('role', 'user')->count(),
            'users_with_no_requests' => User::where('role', 'user')
                ->whereDoesntHave('requests', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->count(),
            'average_requests_per_user' => $activeUsers > 0 ?
                round(Request::whereBetween('created_at', [$startDate, $endDate])->count() / $activeUsers, 2) : 0
        ];
    }

    /**
     * Get trend statistics.
     */
    private function getTrendStats(Carbon $startDate, Carbon $endDate): array
    {
        $previousStart = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $previousEnd = $startDate->copy();

        $currentPeriod = Request::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousPeriod = Request::whereBetween('created_at', [$previousStart, $previousEnd])->count();

        $growth = $previousPeriod > 0 ?
            round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2) : ($currentPeriod > 0 ? 100 : 0);

        return [
            'requests_growth' => [
                'current' => $currentPeriod,
                'previous' => $previousPeriod,
                'growth_percentage' => $growth,
                'trend' => $growth > 0 ? 'up' : ($growth < 0 ? 'down' : 'stable')
            ],
            'peak_day' => $this->getPeakDay($startDate, $endDate),
            'busiest_hour' => $this->getBusiestHour($startDate, $endDate)
        ];
    }

    /**
     * Get daily request chart data.
     */
    private function getDailyRequestChart(Carbon $startDate, Carbon $endDate): array
    {
        $dailyData = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $count = Request::whereDate('created_at', $current->format('Y-m-d'))->count();
            $dailyData[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('M j'),
                'requests' => $count
            ];
            $current->addDay();
        }

        return $dailyData;
    }

    /**
     * Get approval rate for the period.
     */
    private function getApprovalRate(Carbon $startDate, Carbon $endDate): array
    {
        $total = Request::whereBetween('created_at', [$startDate, $endDate])->count();
        $approved = Request::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'approved')
            ->count();
        $rejected = Request::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'rejected')
            ->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'approval_percentage' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'rejection_percentage' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get peak day of the period.
     */
    private function getPeakDay(Carbon $startDate, Carbon $endDate): array
    {
        $peakDay = Request::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('count')
            ->first();

        return $peakDay ? [
            'date' => Carbon::parse($peakDay->date)->format('Y-m-d'),
            'formatted_date' => Carbon::parse($peakDay->date)->format('M j, Y'),
            'requests' => $peakDay->count
        ] : null;
    }

    /**
     * Get busiest hour of requests.
     */
    private function getBusiestHour(Carbon $startDate, Carbon $endDate): array
    {
        $busiestHour = Request::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderByDesc('count')
            ->first();

        return $busiestHour ? [
            'hour' => $busiestHour->hour,
            'formatted_hour' => sprintf('%02d:00', $busiestHour->hour),
            'requests' => $busiestHour->count
        ] : null;
    }

    /**
     * Get average item utilization rate.
     */
    private function getAverageItemUtilization(): float
    {
        $items = Item::where('status', 'available')->get();
        $totalUtilization = 0;

        foreach ($items as $item) {
            $available = $item->getAvailableQuantity();
            $utilization = $item->quantity > 0 ? (($item->quantity - $available) / $item->quantity) * 100 : 0;
            $totalUtilization += $utilization;
        }

        return $items->count() > 0 ? round($totalUtilization / $items->count(), 2) : 0;
    }
}
