<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function index(Request $request)
    {
        $supportedGroups = NavigationItem::supportedGroups();
        $activeGroup = $request->get('group', 'mobile_bottom');
        if (!array_key_exists($activeGroup, $supportedGroups)) {
            $activeGroup = 'mobile_bottom';
        }

        $allPanels = NavigationItem::orderBy('sort_order', 'asc')->get()->groupBy('group');

        return view('admin.navigation.index', compact('supportedGroups', 'activeGroup', 'allPanels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|in:mobile_bottom,mobile_food,mobile_services,mobile_used,mobile_shorts,mobile_drawer',
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

        $item = NavigationItem::create($validated);
        NavigationItem::clearAllNavigationCache();

        return redirect()->route('admin.navigation.index', ['group' => $item->group])
            ->with('success', 'Navigation item "' . $validated['title'] . '" added successfully to ' . ($validated['group']) . '!');
    }

    public function update(Request $request, NavigationItem $item)
    {
        $validated = $request->validate([
            'group' => 'required|string|in:mobile_bottom,mobile_food,mobile_services,mobile_used,mobile_shorts,mobile_drawer',
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
        NavigationItem::clearAllNavigationCache();

        return redirect()->route('admin.navigation.index', ['group' => $item->group])
            ->with('success', 'Navigation item "' . $item->title . '" updated successfully!');
    }

    public function destroy(NavigationItem $item)
    {
        $title = $item->title;
        $group = $item->group;
        $item->delete();
        NavigationItem::clearAllNavigationCache();

        return redirect()->route('admin.navigation.index', ['group' => $group])
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

        NavigationItem::clearAllNavigationCache();

        return response()->json(['success' => true, 'message' => 'Navigation ordering updated!']);
    }

    public function resetDefaults(Request $request)
    {
        $group = $request->input('group');
        if ($group && in_array($group, array_keys(NavigationItem::supportedGroups()))) {
            NavigationItem::where('group', $group)->delete();
        } else {
            NavigationItem::truncate();
        }

        (new \Database\Seeders\NavigationSeeder())->run();
        NavigationItem::clearAllNavigationCache();

        return redirect()->route('admin.navigation.index', ['group' => $group ?: 'mobile_bottom'])
            ->with('success', 'Navigation panel reset to platform defaults successfully!');
    }
}
