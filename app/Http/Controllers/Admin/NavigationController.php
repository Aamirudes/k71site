<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NavigationController extends Controller
{
    public function index()
    {
        $bottomItems = NavigationItem::where('group', 'mobile_bottom')
            ->orderBy('sort_order', 'asc')
            ->get();

        $drawerItems = NavigationItem::where('group', 'mobile_drawer')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.navigation.index', compact('bottomItems', 'drawerItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|in:mobile_bottom,mobile_drawer',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:150',
            'url' => 'required|string|max:255',
            'icon_type' => 'required|string|in:svg,image,emoji',
            'icon' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:2048',
            'badge' => 'nullable|string|max:30',
            'badge_color' => 'nullable|string|in:emerald,rose,indigo,amber,blue,purple,yellow',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'target' => 'nullable|string|in:_self,_blank',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('nav_icons', 'public');
            $validated['image_url'] = '/storage/' . $path;
            $validated['icon_type'] = 'image';
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        if (empty($validated['sort_order'])) {
            $maxOrder = NavigationItem::where('group', $validated['group'])->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        NavigationItem::create($validated);

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Navigation item "' . $validated['title'] . '" added successfully!');
    }

    public function update(Request $request, NavigationItem $item)
    {
        $validated = $request->validate([
            'group' => 'required|string|in:mobile_bottom,mobile_drawer',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:150',
            'url' => 'required|string|max:255',
            'icon_type' => 'required|string|in:svg,image,emoji',
            'icon' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:2048',
            'badge' => 'nullable|string|max:30',
            'badge_color' => 'nullable|string|in:emerald,rose,indigo,amber,blue,purple,yellow',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'target' => 'nullable|string|in:_self,_blank',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('nav_icons', 'public');
            $validated['image_url'] = '/storage/' . $path;
            $validated['icon_type'] = 'image';
        }

        $validated['is_active'] = $request->boolean('is_active', false);

        $item->update($validated);

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Navigation item "' . $item->title . '" updated successfully!');
    }

    public function destroy(NavigationItem $item)
    {
        $title = $item->title;
        $item->delete();

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Navigation item "' . $title . '" deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|exists:navigation_items,id',
        ]);

        foreach ($request->items as $index => $id) {
            NavigationItem::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        \Illuminate\Support\Facades\Cache::forget('nav_mobile_bottom');
        \Illuminate\Support\Facades\Cache::forget('nav_mobile_drawer');

        return response()->json(['success' => true, 'message' => 'Navigation ordering updated!']);
    }

    public function resetDefaults()
    {
        NavigationItem::where('group', 'mobile_bottom')->delete();
        (new \Database\Seeders\NavigationSeeder())->run();

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Mobile bottom navigation reset to premium platform defaults!');
    }
}
