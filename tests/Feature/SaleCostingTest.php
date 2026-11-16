<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\User;
use App\Support\Costing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleCostingTest extends TestCase
{
    use RefreshDatabase;

    private function cashier(): User
    {
        return User::factory()->create(['role' => 'cashier']);
    }

    private function product(float $cost = 150, float $qty = 10): Product
    {
        return Product::create([
            'name' => 'Turmeric 1kg', 'sku' => 'T-'.$cost,
            'cost_price' => $cost, 'sell_price' => 0,
            'quantity' => $qty, 'is_active' => true,
        ]);
    }

    public function test_sale_snapshots_costing_and_computes_margin_and_vat(): void
    {
        $product = $this->product(150, 10);

        $this->actingAs($this->cashier())->post('/sales', [
            'payment_method' => 'cash',
            'pad_type' => 'GRV', 'pad_number' => 'OF/BIP/CD/026',
            'vat_percent' => 15,
            'costs' => ['rent_cost' => 26.6, 'utility_cost' => 26.6, 'machine_cost' => 33.3, 'other_cost' => 26.6],
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 310.30,
                'vat' => 1,
            ]],
            'tax' => 93.09,
        ])->assertRedirect();

        $sale = \App\Models\Sale::firstOrFail();
        $this->assertEquals('GRV', $sale->pad_type);
        $this->assertEquals('OF/BIP/CD/026', $sale->pad_number);

        $item = SaleItem::firstOrFail();
        $this->assertEquals(150.0, (float) $item->unit_cost);
        $this->assertEquals(113.10, round((float) $item->other_costs, 2));
        // total cost 263.10 → margin = 310.30/263.10 - 1
        $this->assertEqualsWithDelta(17.94, (float) $item->margin_percent, 0.01);
        $this->assertEquals(15.0, (float) $item->vat_percent);
        $this->assertEquals(93.09, round((float) $item->vat_amount, 2));
        $this->assertEquals(8.0, (float) $product->fresh()->quantity);
    }

    public function test_item_costs_fall_back_to_order_level_then_defaults(): void
    {
        Setting::put('costing.rent_cost', 10);
        Setting::put('costing.utility_cost', 20);
        Setting::put('costing.machine_cost', 30);
        Setting::put('costing.other_cost', 40);
        $product = $this->product(100, 10);

        // No per-item or per-order costs → company defaults used.
        $this->actingAs($this->cashier())->post('/sales', [
            'payment_method' => 'cash',
            'pad_type' => 'GRV', 'pad_number' => 'OF/BIP/CD/026',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 250,
            ]],
        ])->assertRedirect();

        $item = SaleItem::firstOrFail();
        $this->assertEquals(10.0, (float) $item->rent_cost);
        $this->assertEquals(20.0, (float) $item->utility_cost);
        $this->assertEquals(30.0, (float) $item->machine_cost);
        $this->assertEquals(40.0, (float) $item->other_cost);
        // total cost = 200, price 250 → 25% margin
        $this->assertEquals(25.0, (float) $item->margin_percent);
        $this->assertEquals(0.0, (float) $item->vat_percent); // no VAT flag
    }

    public function test_per_order_costs_apply_when_item_has_none(): void
    {
        $product = $this->product(100, 10);

        $this->actingAs($this->cashier())->post('/sales', [
            'payment_method' => 'cash',
            'pad_type' => 'GRV', 'pad_number' => 'OF/BIP/CD/026',
            'costs' => ['rent_cost' => 5, 'utility_cost' => 5, 'machine_cost' => 5, 'other_cost' => 5],
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 120,
            ]],
        ])->assertRedirect();

        $item = SaleItem::firstOrFail();
        $this->assertEquals(20.0, (float) $item->other_costs);
        $this->assertEquals(0.0, round((float) $item->margin_percent, 2)); // 120 / 120 - 1
    }

    public function test_fractional_quantity_is_allowed(): void
    {
        $product = $this->product(100, 5);

        $this->actingAs($this->cashier())->post('/sales', [
            'payment_method' => 'cash',
            'pad_type' => 'GRV', 'pad_number' => 'OF/BIP/CD/026',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 0.5,
                'unit_price' => 200,
            ]],
        ])->assertRedirect();

        $this->assertEquals(0.5, (float) SaleItem::firstOrFail()->quantity);
        $this->assertEquals(4.5, (float) $product->fresh()->quantity);
    }

    public function test_create_page_shows_costing_defaults(): void
    {
        Setting::put('costing.rent_cost', 42.5);
        $this->product();

        $this->actingAs($this->cashier())->get('/sales/create')
            ->assertOk()
            ->assertSee('42.5');
    }

    public function test_admin_can_update_costing_defaults(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put('/settings/costing', [
            'rent_cost' => 30, 'utility_cost' => 31, 'machine_cost' => 32,
            'other_cost' => 33, 'margin_percent' => 20, 'vat_percent' => 15,
        ])->assertRedirect();

        $values = Costing::values();
        $this->assertEquals(30.0, $values['rent_cost']);
        $this->assertEquals(20.0, $values['margin_percent']);
    }

    public function test_cashier_cannot_update_costing_defaults(): void
    {
        $this->actingAs($this->cashier())->put('/settings/costing', [
            'rent_cost' => 30, 'utility_cost' => 31, 'machine_cost' => 32,
            'other_cost' => 33, 'margin_percent' => 20, 'vat_percent' => 15,
        ])->assertForbidden();
    }
}
