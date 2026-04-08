<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashierAuthTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Outlet $outlet;
    private User $cashier;
    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'   => 'Test Store',
            'slug'   => 'test-store',
            'email'  => 'store@test.com',
            'status' => 'active',
            'plan'   => 'professional',
        ]);

        $this->outlet = Outlet::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Main Outlet',
            'code'      => 'MAIN',
            'is_active' => true,
        ]);

        // Create permissions and roles
        $this->seedPermissions();

        $this->cashier = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'John Cashier',
            'email'     => 'cashier@test.com',
            'password'  => Hash::make('password'),
            'pin'       => Hash::make('123456'),
            'is_active' => true,
        ]);
        $this->cashier->assignRole('cashier');

        $this->supervisor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Jane Supervisor',
            'email'     => 'supervisor@test.com',
            'password'  => Hash::make('password'),
            'pin'       => Hash::make('654321'),
            'is_active' => true,
        ]);
        $this->supervisor->assignRole('manager');
    }

    private function seedPermissions(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view products', 'create orders', 'view orders', 'view payments',
            'create customers', 'manage orders', 'authorize refund', 'authorize void',
            'view reports', 'view cashier reports', 'edit orders',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);

        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashierRole->syncPermissions(['view products', 'create orders', 'view orders', 'view payments', 'create customers']);

        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            'view products', 'create orders', 'view orders', 'view payments',
            'manage orders', 'authorize refund', 'authorize void', 'view reports',
            'view cashier reports', 'edit orders',
        ]);
    }

    // ── PIN Login Tests ──────────────────────────────────────────────────────

    public function test_cashier_can_login_with_pin(): void
    {
        $response = $this->postJson('/api/auth/login-pin', [
            'email' => 'cashier@test.com',
            'pin'   => '123456',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'roles', 'has_pin']]);

        $this->assertEquals('John Cashier', $response->json('user.name'));
    }

    public function test_pin_login_fails_with_wrong_pin(): void
    {
        $response = $this->postJson('/api/auth/login-pin', [
            'email' => 'cashier@test.com',
            'pin'   => '000000',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('pin');
    }

    public function test_pin_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/auth/login-pin', [
            'email' => 'nonexistent@test.com',
            'pin'   => '123456',
        ]);

        $response->assertUnprocessable();
    }

    public function test_pin_login_requires_6_digit_pin(): void
    {
        $response = $this->postJson('/api/auth/login-pin', [
            'email' => 'cashier@test.com',
            'pin'   => '123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('pin');
    }

    public function test_inactive_user_cannot_login_with_pin(): void
    {
        $this->cashier->update(['is_active' => false]);

        $response = $this->postJson('/api/auth/login-pin', [
            'email' => 'cashier@test.com',
            'pin'   => '123456',
        ]);

        $response->assertUnprocessable();
    }

    // ── Standard Login Tests ─────────────────────────────────────────────────

    public function test_login_response_includes_has_pin(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'cashier@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.has_pin', true);
    }

    // ── Set PIN Tests ────────────────────────────────────────────────────────

    public function test_authenticated_user_can_set_pin(): void
    {
        $userWithoutPin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $userWithoutPin->assignRole('cashier');

        $response = $this->actingAs($userWithoutPin, 'sanctum')
            ->postJson('/api/auth/set-pin', [
                'pin'              => '999888',
                'pin_confirmation' => '999888',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'PIN set successfully.');

        $userWithoutPin->refresh();
        $this->assertTrue($userWithoutPin->verifyPin('999888'));
    }

    public function test_set_pin_requires_confirmation(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/auth/set-pin', [
                'pin'              => '111111',
                'pin_confirmation' => '222222',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('pin_confirmation');
    }

    public function test_set_pin_requires_6_digits(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/auth/set-pin', [
                'pin'              => 'abcdef',
                'pin_confirmation' => 'abcdef',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('pin');
    }

    // ── Cashier Switch Tests ─────────────────────────────────────────────────

    public function test_switch_cashier_with_valid_pin(): void
    {
        $response = $this->postJson('/api/auth/switch-cashier', [
            'user_id' => $this->cashier->id,
            'pin'     => '123456',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.name', 'John Cashier');
    }

    public function test_switch_cashier_fails_with_wrong_pin(): void
    {
        $response = $this->postJson('/api/auth/switch-cashier', [
            'user_id' => $this->cashier->id,
            'pin'     => '000000',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('pin');
    }

    public function test_switch_cashier_fails_with_inactive_user(): void
    {
        $this->cashier->update(['is_active' => false]);

        $response = $this->postJson('/api/auth/switch-cashier', [
            'user_id' => $this->cashier->id,
            'pin'     => '123456',
        ]);

        $response->assertUnprocessable();
    }

    // ── List Cashiers Tests ──────────────────────────────────────────────────

    public function test_list_cashiers_returns_active_cashiers_with_pins(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/auth/cashiers');

        $response->assertOk()
            ->assertJsonStructure(['cashiers' => [['id', 'name', 'avatar']]]);

        $cashiers = $response->json('cashiers');
        $this->assertCount(2, $cashiers); // cashier + supervisor both have PINs
    }

    public function test_list_cashiers_excludes_users_without_pin(): void
    {
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
            'pin'       => null,
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/auth/cashiers');

        // Should still be 2 (the user without PIN is excluded)
        $this->assertCount(2, $response->json('cashiers'));
    }

    // ── Me Endpoint Tests ────────────────────────────────────────────────────

    public function test_me_endpoint_includes_has_pin(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('has_pin', true);
    }

    // ── Logout Tests ─────────────────────────────────────────────────────────

    public function test_logout_revokes_token(): void
    {
        $token = $this->cashier->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    // ── Shift Management Tests ───────────────────────────────────────────────

    public function test_cashier_can_start_shift(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/shifts/start', [
                'outlet_id'     => $this->outlet->id,
                'starting_cash' => 500000,
                'notes'         => 'Morning shift',
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Shift started successfully.')
            ->assertJsonPath('shift.is_active', true)
            ->assertJsonPath('shift.starting_cash', 500000);

        $this->assertDatabaseHas('cashier_shifts', [
            'user_id'   => $this->cashier->id,
            'outlet_id' => $this->outlet->id,
        ]);
    }

    public function test_cashier_cannot_start_duplicate_shift(): void
    {
        CashierShift::create([
            'tenant_id'     => $this->tenant->id,
            'user_id'       => $this->cashier->id,
            'outlet_id'     => $this->outlet->id,
            'started_at'    => now(),
            'starting_cash' => 0,
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/shifts/start', [
                'outlet_id' => $this->outlet->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('shift');
    }

    public function test_cashier_can_end_shift(): void
    {
        CashierShift::create([
            'tenant_id'     => $this->tenant->id,
            'user_id'       => $this->cashier->id,
            'outlet_id'     => $this->outlet->id,
            'started_at'    => now()->subHours(8),
            'starting_cash' => 500000,
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/shifts/end', [
                'ending_cash' => 1500000,
                'notes'       => 'End of shift',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Shift ended successfully.')
            ->assertJsonPath('shift.is_active', false)
            ->assertJsonPath('shift.ending_cash', 1500000);
    }

    public function test_end_shift_fails_when_no_active_shift(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/shifts/end', [
                'ending_cash' => 0,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('shift');
    }

    public function test_get_current_shift(): void
    {
        CashierShift::create([
            'tenant_id'     => $this->tenant->id,
            'user_id'       => $this->cashier->id,
            'outlet_id'     => $this->outlet->id,
            'started_at'    => now(),
            'starting_cash' => 300000,
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/shifts/current');

        $response->assertOk()
            ->assertJsonPath('shift.is_active', true);
    }

    public function test_get_current_shift_returns_null_when_no_active_shift(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/shifts/current');

        $response->assertOk()
            ->assertJsonPath('shift', null);
    }

    public function test_list_shifts(): void
    {
        CashierShift::create([
            'tenant_id'     => $this->tenant->id,
            'user_id'       => $this->cashier->id,
            'outlet_id'     => $this->outlet->id,
            'started_at'    => now()->subDays(1),
            'ended_at'      => now()->subDays(1)->addHours(8),
            'starting_cash' => 500000,
            'ending_cash'   => 1200000,
        ]);

        CashierShift::create([
            'tenant_id'     => $this->tenant->id,
            'user_id'       => $this->cashier->id,
            'outlet_id'     => $this->outlet->id,
            'started_at'    => now(),
            'starting_cash' => 500000,
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/shifts');

        $response->assertOk()
            ->assertJsonPath('total', 2);
    }

    // ── Supervisor Authorization Tests ───────────────────────────────────────

    public function test_supervisor_can_authorize_refund(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/supervisor/authorize', [
                'pin'    => '654321',
                'action' => 'refund',
            ]);

        $response->assertOk()
            ->assertJsonPath('authorized', true)
            ->assertJsonPath('supervisor', 'Jane Supervisor')
            ->assertJsonPath('action', 'refund');
    }

    public function test_supervisor_can_authorize_void(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/supervisor/authorize', [
                'pin'    => '654321',
                'action' => 'void',
            ]);

        $response->assertOk()
            ->assertJsonPath('authorized', true);
    }

    public function test_supervisor_auth_fails_with_invalid_pin(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/supervisor/authorize', [
                'pin'    => '000000',
                'action' => 'refund',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('pin');
    }

    public function test_supervisor_auth_fails_with_invalid_action(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/supervisor/authorize', [
                'pin'    => '654321',
                'action' => 'invalid_action',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('action');
    }

    public function test_cashier_pin_cannot_authorize_refund(): void
    {
        // The cashier doesn't have 'authorize refund' permission
        // but supervisor lookup only checks supervisor/manager roles,
        // and cashier role won't be matched.
        $response = $this->actingAs($this->supervisor, 'sanctum')
            ->postJson('/api/supervisor/authorize', [
                'pin'    => '123456',  // cashier's PIN
                'action' => 'refund',
            ]);

        // Should fail because only manager/admin roles are searched
        $response->assertUnprocessable();
    }

    // ── Cashier Report Tests ─────────────────────────────────────────────────

    public function test_sales_by_cashier_report(): void
    {
        // Create a completed order for the cashier
        Order::create([
            'tenant_id'      => $this->tenant->id,
            'outlet_id'      => $this->outlet->id,
            'user_id'        => $this->cashier->id,
            'cashier_name'   => 'John Cashier',
            'order_number'   => 'TEST-001',
            'status'         => 'completed',
            'subtotal'       => 100000,
            'tax_amount'     => 10000,
            'discount_amount' => 5000,
            'total_amount'   => 105000,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/reports/sales-by-cashier');

        $response->assertOk()
            ->assertJsonStructure(['report' => [['cashier_id', 'cashier_name', 'total_transactions', 'total_sales']]]);

        $report = $response->json('report');
        $this->assertEquals(1, $report[0]['total_transactions']);
        $this->assertEquals(105000, $report[0]['total_sales']);
        $this->assertEquals('John Cashier', $report[0]['cashier_name']);
    }

    public function test_sales_by_cashier_report_with_date_filter(): void
    {
        Order::create([
            'tenant_id'      => $this->tenant->id,
            'outlet_id'      => $this->outlet->id,
            'user_id'        => $this->cashier->id,
            'cashier_name'   => 'John Cashier',
            'order_number'   => 'TEST-002',
            'status'         => 'completed',
            'total_amount'   => 50000,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/reports/sales-by-cashier?date_from=' . now()->format('Y-m-d'));

        $response->assertOk();
        $this->assertCount(1, $response->json('report'));
    }

    public function test_shift_summary_report(): void
    {
        CashierShift::create([
            'tenant_id'     => $this->tenant->id,
            'user_id'       => $this->cashier->id,
            'outlet_id'     => $this->outlet->id,
            'started_at'    => now()->subHours(8),
            'ended_at'      => now(),
            'starting_cash' => 500000,
            'ending_cash'   => 1500000,
        ]);

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->getJson('/api/reports/shift-summary');

        $response->assertOk()
            ->assertJsonPath('total', 1);
    }

    // ── User Model PIN Tests ─────────────────────────────────────────────────

    public function test_user_can_set_and_verify_pin(): void
    {
        $user = User::factory()->create(['pin' => null]);

        $this->assertFalse($user->hasPin());
        $this->assertFalse($user->verifyPin('123456'));

        $user->setPin('123456');
        $user->refresh();

        $this->assertTrue($user->hasPin());
        $this->assertTrue($user->verifyPin('123456'));
        $this->assertFalse($user->verifyPin('654321'));
    }

    public function test_user_pin_is_hidden_from_serialization(): void
    {
        $array = $this->cashier->toArray();
        $this->assertArrayNotHasKey('pin', $array);
        $this->assertArrayNotHasKey('password', $array);
    }

    // ── Inactivity Middleware Tests ──────────────────────────────────────────

    public function test_active_session_is_not_locked(): void
    {
        $token = $this->cashier->createToken('test-token')->plainTextToken;

        // Touch the token to update last_used_at
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertOk();
    }
}
