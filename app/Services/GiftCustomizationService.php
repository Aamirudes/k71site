<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GiftCustomizationService
{
    /**
     * Default list of supported calligraphy / engraving fonts.
     */
    public const SUPPORTED_FONTS = [
        'Classic Serif' => ['label' => 'Classic Serif', 'font_family' => 'Cormorant Garamond, serif'],
        'Modern Script' => ['label' => 'Modern Script', 'font_family' => 'Playfair Display, cursive'],
        'Bold Minimal' => ['label' => 'Bold Minimal', 'font_family' => 'Space Grotesk, sans-serif'],
        'Vintage Engraving' => ['label' => 'Vintage Engraving', 'font_family' => 'Cinzel, Georgia, serif'],
        'Casual Handwritten' => ['label' => 'Casual Handwritten', 'font_family' => 'Dancing Script, cursive'],
    ];

    /**
     * Default list of supported engraving / foil colors.
     */
    public const SUPPORTED_COLORS = [
        'Gold Foil' => ['name' => 'Gold Foil', 'hex' => '#D4AF37'],
        'Silver Shimmer' => ['name' => 'Silver Shimmer', 'hex' => '#C0C0C0'],
        'Midnight Black' => ['name' => 'Midnight Black', 'hex' => '#18181B'],
        'Rose Gold' => ['name' => 'Rose Gold', 'hex' => '#B76E79'],
        'Emerald Green' => ['name' => 'Emerald Green', 'hex' => '#047857'],
        'Deep Crimson' => ['name' => 'Deep Crimson', 'hex' => '#881337'],
    ];

    /**
     * Validate and calculate customization selections server-side.
     * Enforces strict pricing integrity, validates char limits, uploads photos safely,
     * and returns structured snapshot and pricing deltas.
     */
    public function validateAndCalculate(
        Product $product,
        ?array $input,
        ?UploadedFile $photoFile = null
    ): array {
        if (!$product->isGiftCustomizable()) {
            return [
                'is_customized' => false,
                'snapshot' => null,
                'additional_paise' => 0,
            ];
        }

        $cfg = $product->getGiftCustomizationConfig();
        $input = is_array($input) ? $input : [];

        $customText = trim((string) ($input['custom_text'] ?? ''));
        $font = trim((string) ($input['font'] ?? ''));
        $color = trim((string) ($input['color'] ?? ''));
        $giftWrap = filter_var($input['gift_wrap'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $greetingCard = filter_var($input['greeting_card'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $cardMessage = trim((string) ($input['greeting_card_message'] ?? ''));

        // If no customization option was engaged at all, treat as standard purchase if text is optional
        $hasCustomizationEngagement = $customText !== '' || !empty($photoFile) || $giftWrap || $greetingCard;

        if (!$hasCustomizationEngagement) {
            // Check if text customization is mandatory
            if (!empty($cfg['allow_custom_text']) && !empty($cfg['is_text_required'])) {
                throw new \DomainException('Personalized text is required for this customizable gift.');
            }

            return [
                'is_customized' => false,
                'snapshot' => null,
                'additional_paise' => 0,
            ];
        }

        $additionalPaise = 0;
        $snapshot = [
            'type' => 'gift_personalization',
            'custom_text' => null,
            'font' => null,
            'color' => null,
            'photo_url' => null,
            'photo_path' => null,
            'gift_wrap' => false,
            'gift_wrap_price_paise' => 0,
            'greeting_card' => false,
            'greeting_card_price_paise' => 0,
            'greeting_card_message' => null,
            'production_days' => (int) ($cfg['production_days'] ?? 2),
            'total_customization_paise' => 0,
        ];

        // 1. Custom Text Validation & Pricing
        if ($customText !== '') {
            $charLimit = (int) ($cfg['text_char_limit'] ?? 40);
            if (mb_strlen($customText) > $charLimit) {
                throw new \DomainException("Personalized text cannot exceed {$charLimit} characters.");
            }
            $snapshot['custom_text'] = mb_substr(strip_tags($customText), 0, $charLimit);
            $textPrice = max(0, (int) ($cfg['text_price_paise'] ?? 0));
            $additionalPaise += $textPrice;
        }

        // 2. Font Selection
        if (!empty($cfg['allow_font_selection'])) {
            $allowedFonts = (array) ($cfg['available_fonts'] ?? array_keys(self::SUPPORTED_FONTS));
            if ($font !== '' && in_array($font, $allowedFonts, true)) {
                $snapshot['font'] = $font;
            } else {
                $snapshot['font'] = $allowedFonts[0] ?? 'Classic Serif';
            }
        }

        // 3. Color / Foil Selection
        if (!empty($cfg['allow_color_selection'])) {
            $allowedColors = (array) ($cfg['available_colors'] ?? array_keys(self::SUPPORTED_COLORS));
            if ($color !== '' && in_array($color, $allowedColors, true)) {
                $snapshot['color'] = $color;
            } else {
                $snapshot['color'] = $allowedColors[0] ?? 'Gold Foil';
            }
        }

        // 4. Photo Upload Handling
        if ($photoFile && !empty($cfg['allow_photo_upload'])) {
            // Validate MIME type & file size (max 10MB)
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            if (!in_array($photoFile->getMimeType(), $allowedMimes, true)) {
                throw new \DomainException('Uploaded photo must be a valid image (JPEG, PNG, or WebP).');
            }
            if ($photoFile->getSize() > 10 * 1024 * 1024) {
                throw new \DomainException('Uploaded photo size cannot exceed 10MB.');
            }

            $filename = 'gift_' . Str::random(24) . '.' . ($photoFile->guessExtension() ?: 'jpg');
            $path = $photoFile->storeAs('customer_customizations', $filename, 'public');

            $snapshot['photo_path'] = $path;
            $snapshot['photo_url'] = Storage::disk('public')->url($path);

            $photoPrice = max(0, (int) ($cfg['photo_price_paise'] ?? 0));
            $additionalPaise += $photoPrice;
        } elseif (!empty($input['existing_photo_url'])) {
            // Preserve existing uploaded photo url if updating
            $snapshot['photo_url'] = filter_var($input['existing_photo_url'], FILTER_VALIDATE_URL) ?: null;
            $snapshot['photo_path'] = $input['existing_photo_path'] ?? null;
        }

        // 5. Gift Wrap Option
        if ($giftWrap && !empty($cfg['allow_gift_wrap'])) {
            $wrapPrice = max(0, (int) ($cfg['gift_wrap_price_paise'] ?? 4900));
            $snapshot['gift_wrap'] = true;
            $snapshot['gift_wrap_price_paise'] = $wrapPrice;
            $additionalPaise += $wrapPrice;
        }

        // 6. Greeting Card Option
        if ($greetingCard && !empty($cfg['allow_greeting_card'])) {
            $cardPrice = max(0, (int) ($cfg['greeting_card_price_paise'] ?? 2900));
            $snapshot['greeting_card'] = true;
            $snapshot['greeting_card_price_paise'] = $cardPrice;
            $snapshot['greeting_card_message'] = mb_substr(strip_tags($cardMessage), 0, 300);
            $additionalPaise += $cardPrice;
        }

        $snapshot['total_customization_paise'] = $additionalPaise;

        return [
            'is_customized' => true,
            'snapshot' => $snapshot,
            'additional_paise' => $additionalPaise,
        ];
    }

    /**
     * Compare two customization snapshots to determine if they represent the exact same personalization.
     * Two gifts with different names, fonts, photos, or cards must NEVER merge into the same cart item.
     */
    public function areCustomizationsEqual(?array $a, ?array $b): bool
    {
        if (empty($a) && empty($b)) {
            return true;
        }
        if (empty($a) || empty($b)) {
            return false;
        }

        $fields = [
            'custom_text',
            'font',
            'color',
            'photo_url',
            'gift_wrap',
            'greeting_card',
            'greeting_card_message',
        ];

        foreach ($fields as $field) {
            $valA = $a[$field] ?? null;
            $valB = $b[$field] ?? null;

            if (is_string($valA) && is_string($valB)) {
                if (trim($valA) !== trim($valB)) {
                    return false;
                }
            } elseif ($valA !== $valB) {
                return false;
            }
        }

        return true;
    }
}
