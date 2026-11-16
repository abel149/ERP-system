<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\Production;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function material(string $name, float $qty, float $price): RawMaterial
    {
        return RawMaterial::create([
            'name' => $name, 'sku' => strtoupper($name), 'unit' => 'kg',
            'quantity' => $qty, 'cost_price' => $price, 'min_stock' => 0, 'status' => 'active',
        ]);
    }

    public function test_screens_render_for_the_right_roles(): void
    {
        $this->material('Tea', 100, 50);
        $production = User::factory()->create(['role' => 'production']);
        $store = User::factory()->create(['role' => 'inventory']);

        $this->actingAs($production)->get('/productions')->assertOk();
        $this->actingAs($production)->get('/productions/create')->assertOk();
        $this->actingAs($production)->get('/nc')->assertOk();
        $this->actingAs($production)->get('/issues/create')->assertForbidden();

        $this->actingAs($store)->get('/issues/create')->assertOk()->assertSee('Tea');
        $this->actingAs($store)->get('/raw-materials')->assertOk()->assertSee('5,000.00');
        $this->actingAs($store)->get('/productions/create')->assertForbidden();
    }

    public function test_full_flow_issue_submit_push_rework(): void
    {
        $tea = $this->material('Tea', 100, 50);        // 5,000
        $cardamom = $this->material('Cardamom', 10, 200); // 2,000
        $fg = Product::create(['name' => 'Spiced tea', 'sku' => 'ST100', 'cost_price' => 0, 'sell_price' => 20,
            'quantity' => 0, 'is_active' => true]);
        $store = User::factory()->create(['role' => 'inventory']);
        $manager = User::factory()->create(['role' => 'production']);

        // Store issues both ingredients on one issue
        $this->actingAs($store)->post('/issues', [
            'items' => [
                ['raw_material_id' => $tea->id, 'quantity' => 100],
                ['raw_material_id' => $cardamom->id, 'quantity' => 10],
            ],
            'pad_type' => 'GRV', 'pad_number' => 'P-1', 'intended_product_id' => $fg->id,
        ])->assertRedirect('/issues');
        $issue = Issue::firstOrFail();
        $this->assertSame(7000.0, $issue->total_value);
        $this->assertEquals(0, $tea->fresh()->quantity);

        // Step 1: submit purity and impurity — nothing in stock yet
        $this->actingAs($manager)->get('/productions/create?issue_id='.$issue->id)->assertOk()->assertSee($issue->issue_number);
        $this->actingAs($manager)->post('/productions', [
            'issue_ids' => [$issue->id], 'product_id' => $fg->id,
            'output_quantity' => 90, 'non_conforming' => 15, 'packing_cost' => 100,
            'pad_type' => 'GRV', 'pad_number' => 'P-2',
        ])->assertRedirect();
        $batch = Production::firstOrFail();
        $this->assertSame('recorded', $batch->status);
        $this->assertEquals(0, $fg->fresh()->quantity);
        $this->actingAs($manager)->get("/productions/{$batch->id}")->assertOk()->assertSee('Push pure output to FG');

        // Step 2: push purity to FG — 90 kg is stocked as 90 kg;
        // NC keeps its weight share of cost.
        $this->actingAs($manager)->post("/productions/{$batch->id}/complete")->assertRedirect();
        $batch->refresh();
        $this->assertSame('in_rework', $batch->status);
        $this->assertEquals(90, $fg->fresh()->quantity);
        $this->assertSame('kg', $fg->fresh()->stock_unit);
        $this->assertEqualsWithDelta(7100 * 15 / 105, (float) $batch->nc_value, 0.01);
        $this->assertEqualsWithDelta(7100 / 105, (float) $batch->cost_per_unit, 0.001);

        // Cannot push twice
        $this->actingAs($manager)->post("/productions/{$batch->id}/complete")->assertSessionHasErrors('complete');
        $this->assertEquals(90, $fg->fresh()->quantity);

        // Step 3: rework — recovered to FG (same product, same issue), final NC to NC stock
        $this->actingAs($manager)->get("/productions/{$batch->id}/rework")->assertOk()->assertSee($issue->issue_number);
        $this->actingAs($manager)->post("/productions/{$batch->id}/rework", [
            'rework_output' => 10, 'rework_nc' => 4, 'rework_packing_cost' => 20,
            'nc_product_name' => 'Spiced tea NC', 'nc_sell_price' => 30,
        ])->assertRedirect("/productions/{$batch->id}");

        $batch->refresh();
        $this->assertSame('completed', $batch->status);
        $this->assertEquals(100, $fg->fresh()->quantity);

        $nc = Product::where('is_nc', true)->firstOrFail();
        $this->assertEquals(4, $nc->quantity);
        $this->assertEquals(30, $nc->sell_price);
        $this->assertSame(1, $batch->ncLots()->count());

        // Every kilo-birr is accounted for: FG value + NC value = full batch cost
        $fgValue = $batch->stock_quantity * $batch->cost_per_unit + $batch->rework_stock_quantity * $batch->rework_cost_per_unit;
        $ncValue = (float) $batch->ncLots()->first()->cost_value;
        $this->assertEqualsWithDelta(7120, $fgValue + $ncValue, 0.05);

        $this->actingAs($manager)->get("/productions/{$batch->id}")->assertOk()->assertSee('Rework results');
        $this->actingAs($manager)->get('/nc')->assertOk()->assertSee('Spiced tea NC');
    }

    public function test_receipt_blends_cost_by_weighted_average(): void
    {
        $fg = Product::create(['name' => 'Kerkede', 'sku' => '1001', 'cost_price' => 1810,
            'sell_price' => 2269.26, 'quantity' => 100, 'is_active' => true]);

        $fg->receive(50, 1900, 'production', 'Clean output of PR-00001', 'PR-00001');

        $fg->refresh();
        $this->assertEquals(150, $fg->quantity);
        // (100*1810 + 50*1900) / 150
        $this->assertEqualsWithDelta(1840.0, (float) $fg->cost_price, 0.01);
        $movement = $fg->stockMovements()->latest('id')->first();
        $this->assertEqualsWithDelta(1900.0, (float) $movement->unit_cost, 0.01);
    }

    public function test_new_product_gets_sell_price_from_costing_defaults(): void
    {
        $tea = $this->material('Tea', 100, 50); // issue 100 kg @ 50 = 5,000
        $manager = User::factory()->create(['role' => 'production']);
        $this->actingAs(User::factory()->create(['role' => 'inventory']))->post('/issues', [
            'items' => [['raw_material_id' => $tea->id, 'quantity' => 100]],
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
        ]);

        $this->actingAs($manager)->post('/productions', [
            'issue_ids' => [Issue::first()->id], 'new_product_name' => 'Tea bulk',
            'output_quantity' => 90, 'non_conforming' => 10, 'packing_cost' => 100,
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
        ])->assertRedirect();
        $batch = Production::firstOrFail();

        $this->actingAs($manager)->post("/productions/{$batch->id}/complete")->assertRedirect();

        $fg = Product::where('sku', 'like', 'FG-%')->firstOrFail();
        $this->assertEquals(90, $fg->quantity);
        // unit cost = (5000 + 100) / (90 + 10) = 51/kg; sell = (51 + 113.10) * 1.18
        $this->assertEqualsWithDelta(51.0, (float) $fg->cost_price, 0.01);
        $this->assertEqualsWithDelta(193.64, (float) $fg->sell_price, 0.01);
    }

    public function test_priced_product_keeps_its_sell_price(): void
    {
        $tea = $this->material('Tea', 100, 50);
        $fg = Product::create(['name' => 'Tea bulk', 'sku' => 'TB', 'cost_price' => 0,
            'sell_price' => 555.55, 'quantity' => 0, 'is_active' => true]);
        $manager = User::factory()->create(['role' => 'production']);
        $this->actingAs(User::factory()->create(['role' => 'inventory']))->post('/issues', [
            'items' => [['raw_material_id' => $tea->id, 'quantity' => 100]],
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
        ]);
        $this->actingAs($manager)->post('/productions', [
            'issue_ids' => [Issue::first()->id], 'product_id' => $fg->id,
            'output_quantity' => 100, 'non_conforming' => 0, 'packing_cost' => 0,
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
        ])->assertRedirect();
        $batch = Production::firstOrFail();
        $this->actingAs($manager)->post("/productions/{$batch->id}/complete")->assertRedirect();

        $this->assertEqualsWithDelta(555.55, (float) $fg->fresh()->sell_price, 0.001);
    }

    public function test_packaging_type_prices_packing_by_exact_count(): void
    {
        $tea = $this->material('Tea', 100, 50); // 100 kg @ 50 = 5,000
        $fg = Product::create(['name' => 'Tea bulk', 'sku' => 'TB', 'cost_price' => 0,
            'sell_price' => 0, 'quantity' => 0, 'is_active' => true]);
        $bag = PackagingType::create(['name' => '5 kg bag', 'capacity_kg' => 5, 'unit_cost' => 250, 'is_active' => true]);
        $manager = User::factory()->create(['role' => 'production']);
        $this->actingAs(User::factory()->create(['role' => 'inventory']))->post('/issues', [
            'items' => [['raw_material_id' => $tea->id, 'quantity' => 100]],
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
        ]);

        // 97 kg in 5 kg bags = 19.4 packs exactly -> 19.4 x 250 = 4,850
        $this->actingAs($manager)->post('/productions', [
            'issue_ids' => [Issue::first()->id], 'product_id' => $fg->id,
            'output_quantity' => 97, 'non_conforming' => 3,
            'packaging_type_id' => $bag->id,
            'pad_type' => 'GRV', 'pad_number' => 'P-2',
        ])->assertRedirect();

        $batch = Production::firstOrFail();
        $this->assertSame($bag->id, $batch->packaging_type_id);
        $this->assertEqualsWithDelta(19.4, (float) $batch->package_count, 0.001);
        $this->assertEqualsWithDelta(4850.0, (float) $batch->packing_cost, 0.01);
        // Packing folds into the batch cost shared by output + NC
        $this->assertEqualsWithDelta(5000 + 4850, (float) $batch->total_direct_cost, 0.01);
    }

    public function test_rework_packaging_uses_recovered_output(): void
    {
        $tea = $this->material('Tea', 100, 50);
        $fg = Product::create(['name' => 'Tea bulk', 'sku' => 'TB', 'cost_price' => 0,
            'sell_price' => 0, 'quantity' => 0, 'is_active' => true]);
        $sack = PackagingType::create(['name' => '50 kg sack', 'capacity_kg' => 50, 'unit_cost' => 2500, 'is_active' => true]);
        $manager = User::factory()->create(['role' => 'production']);
        $this->actingAs(User::factory()->create(['role' => 'inventory']))->post('/issues', [
            'items' => [['raw_material_id' => $tea->id, 'quantity' => 100]],
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
        ]);
        $this->actingAs($manager)->post('/productions', [
            'issue_ids' => [Issue::first()->id], 'product_id' => $fg->id,
            'output_quantity' => 80, 'non_conforming' => 20, 'packing_cost' => 0,
            'pad_type' => 'GRV', 'pad_number' => 'P-2',
        ]);
        $batch = Production::firstOrFail();
        $this->actingAs($manager)->post("/productions/{$batch->id}/complete");

        // 15 kg recovered in 50 kg sacks = 0.3 pack -> 0.3 x 2500 = 750
        $this->actingAs($manager)->post("/productions/{$batch->id}/rework", [
            'rework_output' => 15, 'rework_nc' => 5,
            'rework_packaging_type_id' => $sack->id,
            'nc_product_name' => 'Tea NC', 'nc_sell_price' => 10,
        ])->assertRedirect("/productions/{$batch->id}");

        $batch->refresh();
        $this->assertSame($sack->id, $batch->rework_packaging_type_id);
        $this->assertEqualsWithDelta(0.3, (float) $batch->rework_package_count, 0.001);
        $this->assertEqualsWithDelta(750.0, (float) $batch->rework_packing_cost, 0.01);
    }

    public function test_packaging_types_managed_by_admin_and_inventory(): void
    {
        $inventory = User::factory()->create(['role' => 'inventory']);
        $production = User::factory()->create(['role' => 'production']);

        $this->actingAs($inventory)->get('/packaging-types')->assertOk();
        $this->actingAs($inventory)->post('/packaging-types', [
            'name' => '10 kg bag', 'capacity_kg' => 10, 'unit_cost' => 500,
        ])->assertRedirect();
        $this->assertDatabaseHas('packaging_types', ['name' => '10 kg bag', 'capacity_kg' => '10.00']);

        $this->actingAs($production)->get('/packaging-types')->assertForbidden();
        $this->actingAs($production)->post('/packaging-types', [
            'name' => 'Bad', 'capacity_kg' => 1, 'unit_cost' => 1,
        ])->assertForbidden();
        $this->assertSame(1, PackagingType::count());
    }

    public function test_packaging_cost_survives_later_type_price_changes(): void
    {
        $bag = PackagingType::create(['name' => '5 kg bag', 'capacity_kg' => 5, 'unit_cost' => 250, 'is_active' => true]);
        $batch = Production::create([
            'production_number' => 'PR-T1', 'status' => 'completed',
            'pad_type' => 'GRV', 'pad_number' => 'P-1',
            'material_cost' => 1000, 'packing_cost' => 4850,
            'packaging_type_id' => $bag->id, 'package_count' => 19.4,
            'output_quantity' => 97, 'non_conforming' => 3,
        ]);

        $bag->update(['unit_cost' => 999]); // supplier price changes later

        $batch->refresh();
        $this->assertEqualsWithDelta(4850.0, (float) $batch->packing_cost, 0.01);
        $this->assertEqualsWithDelta(19.4, (float) $batch->package_count, 0.001);
    }

    public function test_output_plus_nc_cannot_exceed_input(): void
    {
        $tea = $this->material('Tea', 50, 10);
        $fg = Product::create(['name' => 'Tea bulk', 'sku' => 'TB', 'cost_price' => 0, 'sell_price' => 0, 'quantity' => 0, 'is_active' => true]);
        $this->actingAs(User::factory()->create(['role' => 'inventory']))->post('/issues', [
            'items' => [['raw_material_id' => $tea->id, 'quantity' => 50]], 'pad_type' => 'GRV', 'pad_number' => 'X',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'production']))->post('/productions', [
            'issue_ids' => [Issue::first()->id], 'product_id' => $fg->id,
            'output_quantity' => 45, 'non_conforming' => 10, 'packing_cost' => 0,
            'pad_type' => 'GRV', 'pad_number' => 'Y',
        ])->assertSessionHasErrors('output_quantity');

        $this->assertSame(0, Production::count());
        $this->assertSame('issued', Issue::first()->status);
    }
}
