<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userOf(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/')->assertOk();
    }

    public function test_admin_reaches_everything(): void
    {
        $admin = $this->userOf('admin');

        foreach (['/dashboard', '/users', '/sales', '/sales/create', '/products', '/customers', '/categories'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_cashier_gets_sales_but_not_inventory_or_users(): void
    {
        $cashier = $this->userOf('cashier');

        foreach (['/sales', '/sales/create', '/customers'] as $path) {
            $this->actingAs($cashier)->get($path)->assertOk();
        }
        foreach (['/users', '/products', '/categories'] as $path) {
            $this->actingAs($cashier)->get($path)->assertForbidden();
        }
    }

    public function test_inventory_gets_products_but_not_sales_or_users(): void
    {
        $keeper = $this->userOf('inventory');

        foreach (['/products', '/categories'] as $path) {
            $this->actingAs($keeper)->get($path)->assertOk();
        }
        foreach (['/users', '/sales', '/customers'] as $path) {
            $this->actingAs($keeper)->get($path)->assertForbidden();
        }
    }
}
