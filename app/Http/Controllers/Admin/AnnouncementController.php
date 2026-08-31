<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with('creator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif' || $request->status === '1');
        }

        if ($request->filled('is_important')) {
            $query->where('is_important', $request->boolean('is_important'));
        }

        $announcements = $query->orderBy('is_important', 'desc')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::where('is_active', true)->count(),
            'important' => Announcement::where('is_important', true)->count(),
        ];

        return view('announcements.index', compact('announcements', 'stats'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:penting,perusahaan,acara,kebijakan,libur',
            'snippet' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|max:3072', // 3MB max
            'is_important' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'publish_date' => 'nullable|date',
        ]);

        $validated['is_important'] = $request->boolean('is_important');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['created_by'] = Auth::id();

        if (empty($validated['snippet'])) {
            $validated['snippet'] = Str::limit(strip_tags($validated['content']), 140);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('announcements', 'public');
            $validated['image'] = $path;
        }

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman baru berhasil dipublikasikan.');
    }

    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:penting,perusahaan,acara,kebijakan,libur',
            'snippet' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|max:3072',
            'is_important' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'publish_date' => 'nullable|date',
        ]);

        $validated['is_important'] = $request->boolean('is_important');
        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['snippet'])) {
            $validated['snippet'] = Str::limit(strip_tags($validated['content']), 140);
        }

        if ($request->hasFile('image')) {
            if ($announcement->image && Storage::disk('public')->exists($announcement->image)) {
                Storage::disk('public')->delete($announcement->image);
            }
            $path = $request->file('image')->store('announcements', 'public');
            $validated['image'] = $path;
        }

        if ($request->boolean('remove_image')) {
            if ($announcement->image && Storage::disk('public')->exists($announcement->image)) {
                Storage::disk('public')->delete($announcement->image);
            }
            $validated['image'] = null;
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image && Storage::disk('public')->exists($announcement->image)) {
            Storage::disk('public')->delete($announcement->image);
        }

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}