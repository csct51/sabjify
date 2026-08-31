<?php

namespace App\Enums;

enum DeliverySlot: string
{
    case Morning = 'morning';
    case Evening = 'evening';

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Morning — 8 AM to 12 PM',
            self::Evening => 'Evening — 6 PM to 9 PM',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Morning => 'Morning (8–12)',
            self::Evening => 'Evening (6–9)',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Morning->value => self::Morning->label(),
            self::Evening->value => self::Evening->label(),
        ];
    }
}
