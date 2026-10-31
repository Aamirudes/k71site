<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FloatingLauncherController extends Controller
{
    /**
     * Return default launcher items configuration.
     */
    public static function defaultItems(): array
    {
        return [
            [
                'id' => 'mart',
                'name' => 'K71Mart',
                'label' => 'Fashion & Mart',
                'url' => k71_vertical_url('main'),
                'color' => 'from-amber-500 to-orange-600',
                'icon_type' => 'svg',
                'image_url' => null,
                'is_active' => true,
            ],
            [
                'id' => 'feasto',
                'name' => 'Feasto',
                'label' => '10-Min Hyperlocal',
                'url' => route('feasto.index'),
                'color' => 'from-emerald-500 to-teal-600',
                'icon_type' => 'svg',
                'image_url' => null,
                'is_active' => true,
            ],
            [
                'id' => 'services',
                'name' => 'Services',
                'label' => 'Freelance & Gigs',
                'url' => k71_vertical_url('services'),
                'color' => 'from-indigo-500 to-violet-600',
                'icon_type' => 'svg',
                'image_url' => null,
                'is_active' => true,
            ],
            [
                'id' => 'used',
                'name' => 'Pre-Loved',
                'label' => 'Used Marketplace',
                'url' => k71_vertical_url('used'),
                'color' => 'from-cyan-500 to-blue-600',
                'icon_type' => 'svg',
                'image_url' => null,
                'is_active' => true,
            ],
            [
                'id' => 'shorts',
                'name' => 'Shorts',
                'label' => 'Video Commerce',
                'url' => k71_vertical_url('shorts'),
                'color' => 'from-rose-500 to-red-600',
                'icon_type' => 'svg',
                'image_url' => null,
                'is_active' => true,
            ],
            [
                'id' => 'fleet',
                'name' => 'Delivery',
                'label' => 'Earn as Fleet Partner',
                'url' => route('delivery.onboarding'),
                'color' => 'from-amber-400 to-yellow-500',
                'icon_type' => 'svg',
                'image_url' => null,
                'is_active' => true,
            ],
        ];
    }

    /**
     * Retrieve active launcher items with fallback to defaults.
     */
    public static function getLauncherItems(): array
    {
        $saved = Setting::get('floating_launcher_items', null);
        if (is_array($saved) && !empty($saved)) {
            return $saved;
        }

        return self::defaultItems();
    }

    /**
     * Display the Floating 6-in-1 Launcher Studio interface.
     */
    public function index()
    {
        $items = self::getLauncherItems();
        return view('admin.floating_launcher.index', compact('items'));
    }

    /**
     * Save updated floating launcher configuration.
     */
    public function save(Request $request)
    {
        $inputItems = $request->input('items', []);
        $currentItems = self::getLauncherItems();
        $processed = [];

        foreach ($inputItems as $index => $itemData) {
            $id = $itemData['id'] ?? ('item_' . $index);
            $name = trim($itemData['name'] ?? 'Item');
            $label = trim($itemData['label'] ?? $name);
            $url = trim($itemData['url'] ?? '/');
            $color = trim($itemData['color'] ?? 'from-zinc-500 to-zinc-700');
            $iconType = $itemData['icon_type'] ?? 'svg';
            $imageUrl = $itemData['image_url'] ?? null;
            $isActive = !empty($itemData['is_active']);

            // Handle file upload if present for this index
            if ($request->hasFile("items.{$index}.image_file")) {
                $file = $request->file("items.{$index}.image_file");
                if ($file && $file->isValid()) {
                    $path = $file->store('floating_launcher', 'public');
                    $imageUrl = '/storage/' . $path;
                    $iconType = 'image';
                }
            }

            $processed[] = [
                'id' => $id,
                'name' => $name,
                'label' => $label,
                'url' => $url,
                'color' => $color,
                'icon_type' => $iconType,
                'image_url' => $imageUrl,
                'is_active' => $isActive,
            ];
        }

        Setting::set('floating_launcher_items', $processed, 'navigation', 'json');

        return redirect()->route('admin.floating_launcher.index')
            ->with('success', 'Floating 6-in-1 Launcher navigation updated and published live!');
    }

    /**
     * Reset launcher items back to system defaults.
     */
    public function resetDefaults()
    {
        Setting::set('floating_launcher_items', self::defaultItems(), 'navigation', 'json');

        return redirect()->route('admin.floating_launcher.index')
            ->with('success', 'Floating 6-in-1 Launcher items restored to default presets.');
    }
}

