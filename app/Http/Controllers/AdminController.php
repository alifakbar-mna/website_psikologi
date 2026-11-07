<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\Category;
use App\Models\DocumentRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard Utama Admin
    public function index()
    {
        $stats = [
            'total_documents' => Document::count(),
            'total_students' => User::where('role', 'mahasiswa')->count(),
            'total_lecturers' => User::where('role', 'dosen')->count(),
            'pending_requests' => DocumentRequest::where('status', 'pending')->count(),
        ];

        $latestLogs = ActivityLog::with('user')->latest()->take(7)->get();
        $recentDocuments = Document::with('category')->latest()->take(5)->get();

        return view('admin-dashboard', compact('stats', 'latestLogs', 'recentDocuments'));
    }

    // Halaman Audit Log Aktivitas Lengkap
    public function activityLogs()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);
        return view('admin-activity-logs', compact('logs'));
    }
}