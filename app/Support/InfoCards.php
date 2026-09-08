<?php

namespace App\Support;

use App\Models\InfoCard;
use Illuminate\Support\Facades\Schema;

class InfoCards
{
    /**
     * Default copy (also the fallback when nothing is configured).
     *
     * @var array<int, array{icon: string, title: string, subtitle: string}>
     */
    public const DEFAULTS = [
        ['icon' => 'truck', 'title' => 'On-time delivery', 'subtitle' => 'As per slot'],
        ['icon' => 'carrot', 'title' => 'Freshly packed', 'subtitle' => 'Handpicked daily'],
        ['icon' => 'shield-check', 'title' => 'Secure payment', 'subtitle' => 'COD & online'],
        ['icon' => 'package', 'title' => 'Easy order tracking', 'subtitle' => ''],
        ['icon' => 'shopping-basket', 'title' => 'Weekly basket', 'subtitle' => 'To save time and efforts'],
        ['icon' => 'salad', 'title' => 'Health basket', 'subtitle' => 'With healthy recipe'],
    ];

    /**
     * Lucide icons selectable in admin. Every name here is registered in
     * resources/js/app.js, so picking one never needs a frontend build.
     *
     * @var array<int, string>
     */
    public const ICONS = [
        'apple',
        'banknote',
        'badge-check',
        'carrot',
        'chef-hat',
        'citrus',
        'credit-card',
        'gift',
        'headset',
        'leaf',
        'moon',
        'package',
        'salad',
        'shield-check',
        'shopping-basket',
        'sprout',
        'star',
        'store',
        'sun',
        'truck',
        'utensils',
    ];

    /**
     * Options for the admin icon picker.
     *
     * @return array<int, array{value: string, text: string, search: string}>
     */
    public static function iconOptions(): array
    {
        return array_map(
            fn (string $icon) => ['value' => $icon, 'text' => $icon, 'search' => 'icon'],
            self::ICONS
        );
    }

    /**
     * Cards from the info_cards table merged over defaults (missing rows
     * fall back so fresh installs render current copy with zero seed data).
     *
     * @return array<int, array{icon: string, title: string, subtitle: string}>
     */
    public static function all(): array
    {
        $stored = [];

        if (Schema::hasTable('info_cards')) {
            $stored = InfoCard::query()->orderBy('position')->get()->keyBy('position')->all();
        }

        $cards = [];

        foreach (self::DEFAULTS as $index => $default) {
            /** @var InfoCard|null $row */
            $row = $stored[$index + 1] ?? null;

            if ($row === null) {
                $cards[] = $default;

                continue;
            }

            $title = trim($row->title);
            $subtitle = trim((string) $row->subtitle);

            $cards[] = [
                'icon' => in_array($row->icon, self::ICONS, true) ? $row->icon : $default['icon'],
                'title' => $title !== '' ? mb_substr($title, 0, 60) : $default['title'],
                'subtitle' => mb_substr($subtitle, 0, 80),
            ];
        }

        return $cards;
    }
}
