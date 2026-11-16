<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Company-wide pricing inputs used on sales orders. Values are stored in the
 * settings table under the `costing.*` prefix; the constants below are the
 * built-in defaults taken from the standard price-list workbook and are used
 * until an admin overrides them.
 */
class Costing
{
    /** @var array<string, float> */
    public const DEFAULTS = [
        'rent_cost'      => 26.60,
        'utility_cost'   => 26.60,
        'machine_cost'   => 33.30,
        'other_cost'     => 26.60,
        'margin_percent' => 18.00,
        'vat_percent'    => 15.00,
    ];

    public const LABELS = [
        'rent_cost'      => 'Rent',
        'utility_cost'   => 'Utility',
        'machine_cost'   => 'Machine time (MT)',
        'other_cost'     => 'Other costs (OC)',
        'margin_percent' => 'Default margin',
        'vat_percent'    => 'VAT',
    ];

    /** Costs that make up the "other costs" part of an item's total cost. */
    public const COST_KEYS = ['rent_cost', 'utility_cost', 'machine_cost', 'other_cost'];

    /** @return array<string, float> */
    public static function values(): array
    {
        $stored = Setting::whereIn('key', array_map(fn ($k) => "costing.$k", array_keys(self::DEFAULTS)))
            ->pluck('value', 'key');

        return collect(self::DEFAULTS)
            ->mapWithKeys(fn ($default, $key) => [$key => (float) ($stored["costing.$key"] ?? $default)])
            ->all();
    }

    public static function put(array $values): void
    {
        foreach (self::DEFAULTS as $key => $default) {
            Setting::put("costing.$key", (float) ($values[$key] ?? $default));
        }
    }
}
