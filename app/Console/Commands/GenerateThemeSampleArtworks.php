<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateThemeSampleArtworks extends Command
{
    protected $signature = 'k71:generate-theme-samples';
    protected $description = 'Generate bespoke art-directed SVG preview plates for empty-store theme previews (never replaces real vendor photos)';

    public function handle(): int
    {
        $dir = public_path('images/theme-samples');
        File::ensureDirectoryExists($dir);

        $plates = [
            'fashion-editorial' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1500" width="1200" height="1500">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#e8e2d8"/><stop offset="100%" stop-color="#cfc5b6"/></linearGradient>
    <linearGradient id="coat" x1="0" y1="0" x2="0.4" y2="1"><stop offset="0%" stop-color="#272420"/><stop offset="100%" stop-color="#11100e"/></linearGradient>
  </defs>
  <rect width="1200" height="1500" fill="url(#bg)"/>
  <rect x="80" y="80" width="1040" height="1340" fill="none" stroke="#1c1917" stroke-opacity="0.16" stroke-width="2"/>
  <path d="M430 340 L600 290 L770 340 L840 1260 L645 1260 L600 940 L555 1260 L360 1260 Z" fill="url(#coat)"/>
  <path d="M515 318 L600 540 L685 318" fill="none" stroke="#e8e2d8" stroke-width="5" stroke-opacity="0.5"/>
  <line x1="600" y1="540" x2="600" y2="940" stroke="#e8e2d8" stroke-width="2" stroke-opacity="0.28"/>
  <circle cx="628" cy="640" r="8" fill="#c8a96e"/>
  <circle cx="628" cy="720" r="8" fill="#c8a96e"/>
  <text x="120" y="150" font-family="Georgia,serif" font-size="22" letter-spacing="8" fill="#1c1917" opacity="0.55">ATELIER STUDY 01</text>
  <text x="120" y="1370" font-family="system-ui,sans-serif" font-size="16" letter-spacing="5" fill="#1c1917" opacity="0.45">SAMPLE PREVIEW ARTWORK</text>
</svg>
SVG,
            'fashion-runway' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1500" width="1200" height="1500">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#16161a"/><stop offset="100%" stop-color="#09090b"/></linearGradient>
    <radialGradient id="spot" cx="50%" cy="35%" r="55%"><stop offset="0%" stop-color="#ffffff" stop-opacity="0.18"/><stop offset="100%" stop-color="#09090b" stop-opacity="0"/></radialGradient>
  </defs>
  <rect width="1200" height="1500" fill="url(#bg)"/>
  <rect width="1200" height="1500" fill="url(#spot)"/>
  <polygon points="360,1420 520,280 680,280 840,1420" fill="#f5f3ef" fill-opacity="0.06"/>
  <path d="M470 360 Q600 310 730 360 L785 1190 Q600 1230 415 1190 Z" fill="#ebe7e0"/>
  <path d="M510 355 L600 660 L690 355" fill="#121215"/>
  <line x1="120" y1="120" x2="1080" y2="120" stroke="#ffffff" stroke-opacity="0.18"/>
  <text x="120" y="170" font-family="system-ui,sans-serif" font-size="18" letter-spacing="10" fill="#ffffff" opacity="0.55">RUNWAY ARCHIVE · SAMPLE</text>
</svg>
SVG,
            'sneaker-vault' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900" width="1200" height="900">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#15171e"/><stop offset="100%" stop-color="#090a0f"/></linearGradient>
    <linearGradient id="accent" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#ff4d00"/><stop offset="100%" stop-color="#ff8a00"/></linearGradient>
  </defs>
  <rect width="1200" height="900" fill="url(#bg)"/>
  <g stroke="#ffffff" stroke-opacity="0.07" stroke-width="1">
    <line x1="0" y1="680" x2="1200" y2="680"/><line x1="0" y1="220" x2="1200" y2="220"/>
    <line x1="200" y1="0" x2="200" y2="900"/><line x1="1000" y1="0" x2="1000" y2="900"/>
  </g>
  <ellipse cx="600" cy="655" rx="370" ry="38" fill="#000000" opacity="0.65"/>
  <path d="M235 585 Q245 480 355 445 L515 350 Q575 390 650 390 L795 365 Q870 420 945 505 Q975 550 960 595 L235 595 Z" fill="#f4f4f6"/>
  <path d="M230 580 L965 580 Q970 622 930 632 L265 632 Q225 625 230 580 Z" fill="#d4d4dc"/>
  <path d="M430 510 L760 445 L790 485 L445 550 Z" fill="url(#accent)"/>
  <rect x="330" y="596" width="140" height="20" rx="10" fill="#18181b"/>
  <text x="90" y="110" font-family="monospace" font-size="20" letter-spacing="6" fill="#ffffff" opacity="0.55">PROTO-04 // FOOTWEAR STUDY</text>
  <text x="90" y="820" font-family="monospace" font-size="16" letter-spacing="4" fill="#ff6b2b" opacity="0.8">SAMPLE PREVIEW SPECIMEN</text>
</svg>
SVG,
            'horology-watch' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1200" width="1200" height="1200">
  <defs>
    <radialGradient id="bg" cx="50%" cy="46%" r="60%"><stop offset="0%" stop-color="#1d222e"/><stop offset="100%" stop-color="#07090d"/></radialGradient>
    <linearGradient id="gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#e5c884"/><stop offset="100%" stop-color="#9e7a34"/></linearGradient>
  </defs>
  <rect width="1200" height="1200" fill="url(#bg)"/>
  <rect x="520" y="110" width="160" height="980" rx="22" fill="#161920" stroke="#c8a45d" stroke-opacity="0.35" stroke-width="2"/>
  <circle cx="600" cy="600" r="275" fill="#0d1016" stroke="url(#gold)" stroke-width="18"/>
  <circle cx="600" cy="600" r="245" fill="none" stroke="#ffffff" stroke-opacity="0.16" stroke-width="2" stroke-dasharray="4 17.5"/>
  <circle cx="600" cy="600" r="195" fill="none" stroke="#c8a45d" stroke-opacity="0.35" stroke-width="1.5"/>
  <circle cx="600" cy="495" r="44" fill="none" stroke="#ffffff" stroke-opacity="0.24" stroke-width="1.5"/>
  <circle cx="600" cy="705" r="44" fill="none" stroke="#ffffff" stroke-opacity="0.24" stroke-width="1.5"/>
  <line x1="600" y1="600" x2="600" y2="430" stroke="#f5f3ee" stroke-width="9" stroke-linecap="round"/>
  <line x1="600" y1="600" x2="735" y2="520" stroke="url(#gold)" stroke-width="6" stroke-linecap="round"/>
  <circle cx="600" cy="600" r="14" fill="#c8a45d"/>
  <text x="600" y="100" text-anchor="middle" font-family="monospace" font-size="18" letter-spacing="8" fill="#c8a45d" opacity="0.7">CALIBRE 41MM · SAMPLE</text>
</svg>
SVG,
            'jewelry-macro' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1200" width="1200" height="1200">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#f8f3eb"/><stop offset="100%" stop-color="#e9dfd0"/></linearGradient>
    <linearGradient id="ring" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#e5c782"/><stop offset="50%" stop-color="#b88c3a"/><stop offset="100%" stop-color="#f2dc9f"/></linearGradient>
  </defs>
  <rect width="1200" height="1200" fill="url(#bg)"/>
  <ellipse cx="600" cy="830" rx="260" ry="32" fill="#8b7355" opacity="0.18"/>
  <circle cx="600" cy="630" r="190" fill="none" stroke="url(#ring)" stroke-width="34"/>
  <polygon points="600,320 675,405 600,485 525,405" fill="#ffffff" stroke="#c8a96e" stroke-width="3"/>
  <polygon points="600,320 638,405 600,485 562,405" fill="#f3efe8" opacity="0.7"/>
  <text x="600" y="1090" text-anchor="middle" font-family="Georgia,serif" font-size="22" letter-spacing="9" fill="#3a2f24" opacity="0.55">FINE OBJECT STUDY · SAMPLE</text>
</svg>
SVG,
            'tech-hardware' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900" width="1200" height="900">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0b101b"/><stop offset="100%" stop-color="#141d2f"/></linearGradient>
    <linearGradient id="chassis" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#2c3548"/><stop offset="100%" stop-color="#141923"/></linearGradient>
  </defs>
  <rect width="1200" height="900" fill="url(#bg)"/>
  <rect x="230" y="190" width="740" height="490" rx="34" fill="url(#chassis)" stroke="#60a5fa" stroke-opacity="0.35" stroke-width="2"/>
  <circle cx="430" cy="435" r="155" fill="#0d1118" stroke="#94a3b8" stroke-opacity="0.35" stroke-width="3"/>
  <circle cx="430" cy="435" r="88" fill="#182234" stroke="#38bdf8" stroke-width="2"/>
  <circle cx="430" cy="435" r="22" fill="#38bdf8"/>
  <rect x="650" y="295" width="245" height="90" rx="12" fill="#090d14" stroke="#38bdf8" stroke-opacity="0.4"/>
  <path d="M680 340 L710 340 L725 315 L745 365 L765 325 L785 352 L805 340 L865 340" fill="none" stroke="#38bdf8" stroke-width="3"/>
  <circle cx="715" cy="505" r="34" fill="#1e293b" stroke="#94a3b8" stroke-opacity="0.45"/>
  <circle cx="825" cy="505" r="34" fill="#1e293b" stroke="#94a3b8" stroke-opacity="0.45"/>
  <text x="230" y="140" font-family="monospace" font-size="18" letter-spacing="6" fill="#93c5fd" opacity="0.7">HARDWARE SPECIMEN // 01</text>
</svg>
SVG,
            'furniture-interior' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1400 900" width="1400" height="900">
  <defs>
    <linearGradient id="wall" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f2ece3"/><stop offset="100%" stop-color="#e3d9ca"/></linearGradient>
    <linearGradient id="floor" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#d8cbb8"/><stop offset="100%" stop-color="#c3b29a"/></linearGradient>
  </defs>
  <rect width="1400" height="630" fill="url(#wall)"/>
  <rect y="630" width="1400" height="270" fill="url(#floor)"/>
  <path d="M460 150 A240 240 0 0 1 940 150 L940 630 L460 630 Z" fill="#ebe3d7" stroke="#b9a992" stroke-width="2"/>
  <rect x="440" y="420" width="520" height="175" rx="44" fill="#5e4633"/>
  <rect x="490" y="340" width="420" height="115" rx="34" fill="#735740"/>
  <rect x="485" y="595" width="20" height="80" fill="#2e2219"/>
  <rect x="895" y="595" width="20" height="80" fill="#2e2219"/>
  <text x="90" y="110" font-family="Georgia,serif" font-size="22" letter-spacing="8" fill="#3a2d22" opacity="0.55">SPATIAL INTERIOR · SAMPLE</text>
</svg>
SVG,
            'marble-stone' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1400 900" width="1400" height="900">
  <defs>
    <linearGradient id="slab" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#f5f3ef"/><stop offset="50%" stop-color="#e6e2da"/><stop offset="100%" stop-color="#d7d1c6"/></linearGradient>
  </defs>
  <rect width="1400" height="900" fill="url(#slab)"/>
  <path d="M-40 220 C260 180 420 420 760 360 C1020 315 1180 180 1440 240" fill="none" stroke="#8a7e6d" stroke-width="5" opacity="0.35"/>
  <path d="M-40 640 C280 540 520 740 890 610 C1120 530 1260 640 1440 590" fill="none" stroke="#6b6154" stroke-width="8" opacity="0.22"/>
  <path d="M180 -20 C360 280 580 460 880 920" fill="none" stroke="#a69882" stroke-width="3" opacity="0.35"/>
  <rect x="70" y="70" width="1260" height="760" fill="none" stroke="#1c1917" stroke-opacity="0.16" stroke-width="1.5"/>
  <text x="110" y="135" font-family="monospace" font-size="18" letter-spacing="6" fill="#292524" opacity="0.6">ARCHITECTURAL STONE SLAB · 20MM · SAMPLE</text>
</svg>
SVG,
            'botanical-beauty' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1350" width="1200" height="1350">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#f2f4ec"/><stop offset="100%" stop-color="#dfe6d5"/></linearGradient>
    <linearGradient id="glass" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#35483a"/><stop offset="50%" stop-color="#4e6855"/><stop offset="100%" stop-color="#27362b"/></linearGradient>
  </defs>
  <rect width="1200" height="1350" fill="url(#bg)"/>
  <circle cx="600" cy="680" r="360" fill="#ffffff" opacity="0.45"/>
  <rect x="465" y="450" width="270" height="540" rx="52" fill="url(#glass)"/>
  <rect x="535" y="340" width="130" height="115" rx="14" fill="#1c241e"/>
  <rect x="575" y="265" width="50" height="80" rx="22" fill="#1c241e"/>
  <rect x="500" y="560" width="200" height="250" rx="8" fill="#f7f5ef"/>
  <line x1="530" y1="620" x2="670" y2="620" stroke="#27362b" stroke-width="2" opacity="0.45"/>
  <text x="600" y="680" text-anchor="middle" font-family="Georgia,serif" font-size="24" fill="#1c241e">BOTANICAL</text>
  <text x="600" y="720" text-anchor="middle" font-family="monospace" font-size="14" letter-spacing="4" fill="#4e6855">30ML · SAMPLE</text>
</svg>
SVG,
            'gourmet-coffee' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1000" width="1200" height="1000">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#f4eadc"/><stop offset="100%" stop-color="#dfccb3"/></linearGradient>
  </defs>
  <rect width="1200" height="1000" fill="url(#bg)"/>
  <rect x="380" y="190" width="440" height="630" rx="24" fill="#231712"/>
  <rect x="430" y="310" width="340" height="310" fill="#f7f1e6"/>
  <circle cx="600" cy="430" r="64" fill="#b45309" opacity="0.88"/>
  <line x1="470" y1="530" x2="730" y2="530" stroke="#231712" stroke-width="2"/>
  <text x="600" y="575" text-anchor="middle" font-family="monospace" font-size="18" letter-spacing="6" fill="#231712">SMALL BATCH · SAMPLE</text>
</svg>
SVG,
            'fresh-grocery' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900" width="1200" height="900">
  <rect width="1200" height="900" fill="#f3f8f2"/>
  <circle cx="520" cy="470" r="195" fill="#166534" opacity="0.9"/>
  <circle cx="715" cy="510" r="145" fill="#ea580c" opacity="0.9"/>
  <circle cx="620" cy="330" r="95" fill="#eab308" opacity="0.9"/>
  <text x="600" y="810" text-anchor="middle" font-family="system-ui,sans-serif" font-size="20" font-weight="700" letter-spacing="6" fill="#14532d" opacity="0.7">FRESH MARKET HARVEST · SAMPLE</text>
</svg>
SVG,
            'graphic-art' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1000" width="1200" height="1000">
  <rect width="1200" height="1000" fill="#111111"/>
  <rect x="80" y="80" width="520" height="840" fill="#1d4ed8"/>
  <circle cx="790" cy="470" r="260" fill="#facc15"/>
  <rect x="520" y="590" width="540" height="250" fill="#ffffff"/>
  <text x="560" y="735" font-family="system-ui,sans-serif" font-size="52" font-weight="900" letter-spacing="-2" fill="#111111">EDITION 01</text>
  <text x="120" y="150" font-family="monospace" font-size="18" letter-spacing="5" fill="#ffffff">GRAPHIC OBJECT · SAMPLE</text>
</svg>
SVG,
        ];

        foreach ($plates as $name => $svg) {
            File::put($dir . DIRECTORY_SEPARATOR . $name . '.svg', trim($svg) . "\n");
        }

        $this->info('Generated ' . count($plates) . ' bespoke art-directed SVG sample plates in public/images/theme-samples/.');
        return self::SUCCESS;
    }
}
