<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::withCount('employees')
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $stationsData = $stations->getCollection()->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'description' => $s->description,
            'employees_count' => $s->employees_count ?? 0,
        ])->values();

        return view('stations.index', compact('stations', 'stationsData'));
    }

    public function create()
    {
        return redirect()->route('admin.stations.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stations,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Station::create($validated);

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station created successfully.');
    }

    public function edit(Station $station)
    {
        return redirect()->route('admin.stations.index');
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stations,code,' . $station->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $station->update($validated);

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station updated successfully.');
    }

    public function destroy(Station $station)
    {
        if ($station->employees()->count() > 0) {
            return redirect()->route('admin.stations.index')
                ->with('error', 'Cannot delete station with active employees.');
        }

        $station->delete();

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station deleted successfully.');
    }
}
