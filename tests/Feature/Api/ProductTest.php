<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_unauthenticated_user_cannot_list_products(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_can_list_active_products(): void
    {
        Product::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        // Inactive product should not appear
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonPath('total', 3);
    }

    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Special Widget',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Other Product',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?search=Special');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.name', 'Special Widget');
    }

    public function test_can_search_products_by_sku(): void
    {
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'sku'       => 'UNIQUE-SKU-999',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?search=UNIQUE-SKU-999');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);
    }

    public function test_can_paginate_products(): void
    {
        Product::factory()->count(25)->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('per_page', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id, 'name' => $product->name]);
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products/999999');

        $response->assertStatus(404);
    }

    public function test_can_create_product(): void
    {
        $payload = [
            'name'        => 'New Test Product',
            'sku'         => 'TEST-SKU-001',
            'price'       => 25000,
            'cost_price'  => 12000,
            'unit'        => 'pcs',
            'track_stock' => true,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name'  => 'New Test Product',
                'sku'   => 'TEST-SKU-001',
            ]);

        $this->assertDatabaseHas('products', [
            'name'      => 'New Test Product',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    public function test_create_product_validates_price_is_numeric(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/products', [
                'name'  => 'Test',
                'price' => 'not-a-number',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_create_product_validates_negative_price(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/products', [
                'name'  => 'Test',
                'price' => -100,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'price'     => 10000,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/products/{$product->id}", [
                'price'     => 15000,
                'is_active' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['is_active' => false]);

        $this->assertDatabaseHas('products', [
            'id'        => $product->id,
            'price'     => 15000,
            'is_active' => false,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_products_are_scoped_to_authenticated_tenant(): void
    {
        // Create product for a different tenant
        $otherTenant  = Tenant::factory()->create();
        $otherProduct = Product::factory()->create([
            'tenant_id' => $otherTenant->id,
            'is_active' => true,
        ]);

        // Create product for current tenant
        $ownProduct = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $ownProduct->id);
    }
}
