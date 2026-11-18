<?php

namespace App\Console\Commands;

use App\Services\Storefront\ThemeMarketplaceRegistry;
use Illuminate\Console\Command;

class GenerateStorefrontThemeAssets extends Command
{
    protected $signature = 'k71:generate-theme-css';
    protected $description = 'Generate individual scoped CSS files in public/css/themes/{themeId}.css for all 50 bespoke storefront themes';

    public function handle(ThemeMarketplaceRegistry $registry): int
    {
        $dir = public_path('css/themes');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach (glob($dir . '/*.css') ?: [] as $old) {
            @unlink($old);
        }

        $count = 0;
        foreach ($registry->getNewThemes() as $id => $m) {
            $pal = $m['palette'];
            $lay = $m['layout'];
            $mot = $m['motion'];
            $cat = $m['category'];
            $isDark = !empty($pal['is_dark']);

            $lines = [
                "/* K71 Bespoke Storefront Theme: {$m['name']} ({$id}) | Category: {$cat} | v{$m['version']} */",
                ".k71-sf-theme[data-k71-theme=\"{$id}\"] {",
                "  --theme-accent-soft: color-mix(in srgb, {$pal['accent']} 12%, transparent);",
                "  --theme-card-shadow: " . ($isDark ? '0 18px 42px rgba(0, 0, 0, 0.45)' : '0 10px 28px rgba(0, 0, 0, 0.05)') . ";",
                "}",
            ];

            if (in_array($lay['header_template'], ['heritage-framed', 'magazine-masthead'], true)) {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-editorial-rule { border-top: 3px double var(--t-border); border-bottom: 1px solid var(--t-border); }";
            }

            if ($lay['hero_template'] === 'diagonal-performance') {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-diagonal-stage { clip-path: polygon(0 0, 100% 0, 100% 93%, 0 100%); }";
            }

            if (in_array($id, ['street-culture', 'pop-form', 'color-theory', 'mono-tech'], true)) {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell { border: 1.5px solid var(--t-text); background: var(--t-surface); box-shadow: 4px 4px 0 var(--t-text); transition: transform 220ms ease, box-shadow 220ms ease; }";
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--t-accent); }";
            } elseif ($isDark) {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell { border: 1px solid var(--t-border); background: linear-gradient(180deg, var(--t-surface) 0%, var(--t-bg) 100%); transition: border-color 240ms ease, box-shadow 240ms ease; }";
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell:hover { border-color: var(--t-accent); box-shadow: var(--theme-card-shadow); }";
            } elseif ($lay['card_template'] === 'gallery-plinth') {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell { border: none; background: transparent; }";
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-frame { border: 1px solid var(--t-border); background: var(--t-surface); padding: 0.75rem; }";
            } elseif (in_array($lay['card_template'], ['apothecary-ritual', 'gourmet-pantry'], true)) {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell { border: 1px solid var(--t-border); background: var(--t-surface); box-shadow: var(--theme-card-shadow); }";
            } else {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"] .k71-card-shell { border: 1px solid var(--t-border); background: var(--t-surface); }";
            }

            // Motion profile specific reveal curves
            $profile = $mot['profile'] ?? 'quiet-gallery';
            if ($profile === 'runway-directional') {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"].k71-js-ready:not([data-motion-intensity=\"reduced\"]) [data-k71-reveal] { transform: translateX(-16px); }";
            } elseif ($profile === 'obsidian-curtain') {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"].k71-js-ready:not([data-motion-intensity=\"reduced\"]) [data-k71-reveal] { transform: translateY(22px) scale(0.985); }";
            } elseif ($profile === 'pop-elastic') {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"].k71-js-ready:not([data-motion-intensity=\"reduced\"]) [data-k71-reveal] { transform: scale(0.95) translateY(14px); }";
            } elseif ($profile === 'utility-instant') {
                $lines[] = ".k71-sf-theme[data-k71-theme=\"{$id}\"].k71-js-ready:not([data-motion-intensity=\"reduced\"]) [data-k71-reveal] { transform: translateY(6px); }";
            }

            file_put_contents($dir . '/' . $id . '.css', implode("\n", $lines) . "\n");
            $count++;
        }

        $this->info("Generated {$count} individual per-theme CSS files in public/css/themes/");
        return self::SUCCESS;
    }
}
