<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NationalHoliday;
use Illuminate\Http\Request;

class NationalHolidayController extends Controller
{
    public function index()
    {
        $holidays = NationalHoliday::orderBy('date', 'desc')->paginate(20);
        return view('national-holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('national-holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:national_holidays,date',
            'name' => 'required|string|max:255',
            'religion' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        NationalHoliday::create($validated);

        return redirect()->route('admin.national-holidays.index')
            ->with('success', 'Libur nasional berhasil ditambahkan.');
    }

    public function edit(NationalHoliday $nationalHoliday)
    {
        return view('national-holidays.edit', compact('nationalHoliday'));
    }

    public function update(Request $request, NationalHoliday $nationalHoliday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:national_holidays,date,' . $nationalHoliday->id,
            'name' => 'required|string|max:255',
            'religion' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $nationalHoliday->update($validated);

        return redirect()->route('admin.national-holidays.index')
            ->with('success', 'Libur nasional berhasil diperbarui.');
    }

    public function destroy(NationalHoliday $nationalHoliday)
    {
        $nationalHoliday->delete();

        return redirect()->route('admin.national-holidays.index')
            ->with('success', 'Libur nasional berhasil dihapus.');
    }
}
