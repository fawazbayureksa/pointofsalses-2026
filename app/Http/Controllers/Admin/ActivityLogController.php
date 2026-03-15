<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }

        $activities = $query->paginate(20)->withQueryString();

        return view('admin.logs.index', compact('activities'));
    }

    public function export(Request $request)
    {
        $activities = Activity::with('causer')->latest()->get();

        $csv = "ID,Description,Causer,Created At\n";
        foreach ($activities as $activity) {
            $csv .= sprintf(
                "%s,%s,%s,%s\n",
                $activity->id,
                '"' . str_replace('"', '""', $activity->description) . '"',
                $activity->causer->name ?? 'System',
                $activity->created_at
            );
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-log-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function clear()
    {
        Activity::query()->delete();

        return redirect()->route('admin.logs.index')->with('success', 'Activity logs cleared.');
    }
}
