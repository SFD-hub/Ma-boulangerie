<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Boulangerie;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['user', 'boulangerie'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->filled('boulangerie_id')) {
            $query->where('boulangerie_id', $request->input('boulangerie_id'));
        }

        $logs         = $query->paginate(20)->withQueryString();
        $boulangeries = Boulangerie::orderBy('nom')->get();

        return view('super-admin.activity-logs.index', compact('logs', 'boulangeries'));
    }
}
