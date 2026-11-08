<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'days' => 'nullable|integer|in:7,30,90',
        ]);

        $days = (int) ($data['days'] ?? 30);

        $since = now()
            ->startOfDay()
            ->subDays($days - 1);

        $visits = ActivityLog::where(
            'action',
            'VISIT'
        )
            ->where(
                'created_at',
                '>=',
                $since
            );

        $stats = [
            'visitor' => (clone $visits)
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id'),

            'visits' => (clone $visits)->count(),

            'reads' => ActivityLog::where(
                'action',
                'READ_DOCUMENT'
            )
                ->where(
                    'created_at',
                    '>=',
                    $since
                )
                ->count(),

            'documents' => Document::published()->count(),

            'pending' => Document::where(
                'review_status',
                'pending'
            )->count(),
        ];

        $counts = (clone $visits)
            ->selectRaw(
                'DATE(created_at) as day, COUNT(*) as total'
            )
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day');

        $trend = [];

        for ($index = 0; $index < $days; $index++) {
            $date = $since
                ->copy()
                ->addDays($index)
                ->format('Y-m-d');

            $trend[] = [
                'date' => $date,
                'total' => (int) ($counts[$date] ?? 0),
            ];
        }

        $top = ActivityLog::where(
            'action',
            'READ_DOCUMENT'
        )
            ->whereNotNull('document_id')
            ->where(
                'created_at',
                '>=',
                $since
            )
            ->selectRaw(
                'document_id, COUNT(*) as total'
            )
            ->groupBy('document_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $titles = Document::whereIn(
            'id',
            $top->pluck('document_id')
        )->pluck('title', 'id');

        $cases = Document::where(
            'review_status',
            'approved'
        )
            ->selectRaw(
                'case_type, COUNT(*) as total'
            )
            ->groupBy('case_type')
            ->get();

        return view(
            'admin-dashboard',
            compact(
                'stats',
                'trend',
                'top',
                'titles',
                'cases',
                'days'
            )
        );
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(20);

        return view(
            'admin-activity-logs',
            compact('logs')
        );
    }
}