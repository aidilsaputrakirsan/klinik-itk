<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name')
            ->orderBy('created_at', 'desc');

        // Filter by action
        if ($request->action) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->model_type) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter by date range
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(20);

        // Transform model_type to short name
        $logs->getCollection()->transform(function ($log) {
            $log->model_name = class_basename($log->model_type);
            return $log;
        });

        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'model_type', 'start_date', 'end_date']),
        ]);
    }
}
