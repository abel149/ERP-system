<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\User;
use App\Support\Costing;
use Illuminate\Database\Seeder;

/**
 * Seeds the Brundo July price list (Supermarkets sheet).
 *
 * The tracked unit cost follows the workbook formula:
 *   cost_price = RM cost × (1 + impurity%) + packing total   (columns I + L)
 *   sell_price = (cost_price + other costs) × (1 + margin%)  (columns R + S)
 * so the price that leaves the sales form equals the price list.
 */
class PriceListSeeder extends Seeder
{
    /** code, name, amharic, gram, rm_cost/kg, impurity, packing total, category */
    private const ITEMS = [
        ['1038-2',  'Kerkede - Hibiscus', 'ከርከዴ', 1000, 1600.00, 0.10, 50, 'Teas'],
        ['1038-10', 'Chai Kimem - Tea Spice', 'ሻይ ቅመም', 1000, 3308.80, 0.00, 50, 'Teas'],
        ['1038-1',  'Chai - Black Tea', 'ጥቁር ሻይ', 1000, 400.00, 0.00, 50, 'Teas'],
        ['1036',    'Senafitch - Mustard', 'ሰናፍጭ', 1000, 340.00, 0.05, 50, 'Spices'],
        ['1023-3',  'Makulaya - Sauté Spice Mix', 'ማቁላያ', 1000, 1622.25, 0.00, 50, 'Essential Blends'],
        ['1023-1',  'Alicha Kimem - Mild Spice Blend', 'አልጫ ቅመም', 1000, 889.92, 0.00, 50, 'Essential Blends'],
        ['1023-2',  'Mekelesha - Finishing Spice Mix', 'መከለሻ', 1000, 1633.06, 0.00, 50, 'Essential Blends'],
        ['1001',    'Abish - Fenugreek Seed whole', 'አብሽ', 1000, 260.00, 0.05, 50, 'Spices'],
        ['1027',    'Korerima - Black Cardamom seed', 'ኮረሪማ', 1000, 3600.00, 0.05, 50, 'Spices'],
        ['1028',    'Kundo Berbere - Wild Black pepper', 'ቁንዶ በርበሬ', 1000, 1300.00, 0.05, 50, 'Spices'],
        ['1014',    'Dimbilal - Coriander', 'ድንብላል', 1000, 200.00, 0.05, 50, 'Spices'],
        ['1033',    'Netch Azmud - Caraway Seed', 'ነጭ አዝሙድ', 1000, 320.00, 0.05, 50, 'Spices'],
        ['1047',    'Tikur Azmud - Black Cumin', 'ጥቁር አዝሙድ', 1000, 710.00, 0.05, 50, 'Spices'],
        ['1021',    'Ird - Turmeric', 'እርድ', 1000, 150.00, 0.05, 50, 'Spices'],
        ['1025',    'Kirenfud - Cloves', 'ቁርንፉድ', 1000, 2450.00, 0.05, 50, 'Spices'],
        ['1024',    'Kemun - Cumin', null, 1000, 950.00, 0.05, 50, 'Spices'],
        ['1022',    'Kerefa - Cinnamon', 'ቀረፋ', 1000, 700.00, 0.05, 50, 'Spices'],
        ['1038-11', 'Hail - Green Cardamon Pod', 'ሄል የሻይ ቅመም', 1000, 3500.00, 0.05, 50, 'Spices'],
        ['1038-3',  'Komomela - Chamomile', 'ካሞሜላ', 1000, 400.00, 0.05, 50, 'Teas'],
        ['1038-6',  'Tossegn - Ethiopian Thyme', 'ጦስኝ', 1000, 290.00, 0.20, 50, 'Teas'],
        ['1039',    'Netch Shinkurt - Dry Garlic Sliced', 'ነጭ ሽንኩርት', 1000, 1000.00, 0.05, 50, 'Onions & Ginger'],
        ['1039-3',  'Key Shinkurt - Dry Shallots Sliced (Yabesha)', 'ሽንኩርት', 1000, 800.00, 0.05, 50, 'Onions & Ginger'],
        ['1050',    'Zingibil - Dry Ginger Sliced', 'ዝንጅብል', 1000, 878.00, 0.05, 50, 'Onions & Ginger'],
        ['1031',    'Misser - Lentil (whole)', 'ምስር', 1000, 320.00, 0.05, 50, 'Legumes'],
        ['1031-2',  'Misser - Lentil (split)', 'ምስር', 1000, 295.00, 0.05, 50, 'Legumes'],
        ['1004-2',  'Ater - Pea (split)', 'አተር', 1000, 135.00, 0.05, 50, 'Legumes'],
        ['1040',    'Shimbera - Chickpea (whole)', 'ሽምብራ', 1000, 110.00, 0.05, 50, 'Legumes'],
        ['1040-2',  'Shimbera - Chickpea (flour)', 'ሽምብራ', 1000, 210.00, 0.05, 50, 'Legumes'],
        ['1006',    'Bakela - Fava Beans (whole)', 'ባቄላ', 1000, 180.00, 0.05, 50, 'Legumes'],
        ['1006-2',  'Bakela - Fava Beans (split)', 'ባቄላ', 1000, 230.00, 0.05, 50, 'Legumes'],
        ['1041',    'Shouro Mitten - Spicy Legume Blend', 'ሽሮ ምጥን', 1000, 395.00, 0.00, 50, 'Legumes'],
        ['1041-4',  'Shouro Alicha - Mild Legume Blend', 'ሽሮ አልጫ', 1000, 204.00, 0.00, 50, 'Legumes'],
        ['1005',    'Awaze - Red Chili Pepper Blend Powder', 'አዋዜ', 1000, 750.00, 0.00, 50, 'Chili Peppers'],
        ['1032',    'Mitmita - Bird Eye Chili Pepper Blend', 'ሚጥሚጣ', 1000, 542.21, 0.00, 50, 'Chili Peppers'],
        ['1008',    'Berbere - Hot Chili Pepper Blend', 'በርበሬ', 1000, 948.07, 0.00, 50, 'Chili Peppers'],
        ['1002',    'Afrinje - Pepper Seed Blend', 'አፍርንጅ', 1000, 505.16, 0.00, 50, 'Chili Peppers'],
        ['1008-2',  'Wekete Berbere - Crushed Red Pepper flakes', 'በርበሬ ውቅጥ', 1000, 950.00, 0.05, 50, 'Chili Peppers'],
        ['1018',    'Timiz - Long Pepper', 'ጥምዝ', 1000, 2450.00, 0.05, 50, 'Spices'],
        ['1048-2',  'Gewez', 'ገውዝ', 1000, 1700.00, 0.05, 50, 'Spices'],
    ];

    private const STOCK_KG = 50;      // opening stock per product (kg)
    private const RM_STOCK_KG = 100;  // opening raw material stock

    public function run(): void
    {
        $userId = User::value('id');
        $d = Costing::DEFAULTS;
        $otherPerUnit = $d['rent_cost'] + $d['utility_cost'] + $d['machine_cost'] + $d['other_cost'];
        $margin = $d['margin_percent'] / 100;

        $categories = collect(self::ITEMS)->pluck(7)->unique()
            ->mapWithKeys(fn ($name) => [$name => Category::firstOrCreate(['name' => $name])]);

        foreach (self::ITEMS as [$code, $name, $amharic, $gram, $rmCost, $impurity, $packing, $group]) {
            // Workbook columns I + L: materials-after-impurity + packing per stock unit.
            $unitCost = round($rmCost * (1 + $impurity) + $packing, 2);
            // Workbook column T: total cost + margin.
            $sellPrice = round(($unitCost + $otherPerUnit) * (1 + $margin), 2);

            // Products are stocked in kg — the price list is per 1000 g (1 kg),
            // so the unit price is the price per kg and stays consistent with
            // raw material issues and production output, which are weighed in kg.
            $product = Product::firstOrCreate(['sku' => $code], [
                'category_id'     => $categories[$group]->id,
                'name'            => trim($name),
                'description'     => $amharic,
                'cost_price'      => $unitCost,
                'sell_price'      => $sellPrice,
                'quantity'        => self::STOCK_KG,
                'is_active'       => true,
                'is_nc'           => false,
            ]);

            if ($product->wasRecentlyCreated) {
                $product->stockMovements()->create([
                    'user_id' => $userId, 'type' => 'in',
                    'quantity' => self::STOCK_KG, 'quantity_after' => self::STOCK_KG,
                    'note' => 'Opening stock (price list seed)',
                ]);
            }

            // Same item as a purchasable raw material so the
            // material → production → FG → sale cost chain works end-to-end.
            RawMaterial::firstOrCreate(['sku' => 'RM-'.$code], [
                'category_id' => $categories[$group]->id,
                'name'        => trim($name).' (raw)',
                'description' => $amharic,
                'quantity'    => self::RM_STOCK_KG,
                'unit'        => 'kg',
                'cost_price'  => $rmCost,
                'min_stock'   => 10,
                'status'      => 'active',
            ]);
        }
    }
}
