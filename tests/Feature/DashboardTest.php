<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'bitcoin' => ['usd' => 97412.50, 'usd_24h_change' => 2.14],
                'ethereum' => ['usd' => 3412.20, 'usd_24h_change' => -1.02],
                'binancecoin' => ['usd' => 612.40, 'usd_24h_change' => 4.62],
                'tron' => ['usd' => 0.1642, 'usd_24h_change' => -0.38],
                'tether' => ['usd' => 1.00, 'usd_24h_change' => 0.01],
                'usd-coin' => ['usd' => 1.00, 'usd_24h_change' => 0.0],
            ]),
        ]);
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('balances', 6)
                ->has('transactions', 6)
                ->has('history', 31)
                ->where('security.twoFactorEnabled', false)
                ->where('security.nonCustodial', true)
            );
    }

    public function test_dashboard_balances_are_sourced_from_the_database()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('balances.0.chain', 'btc')
                ->where('balances.0.balance', 0.42184)
                ->where('balances.0.usdValue', round(0.42184 * 97412.50, 2))
                ->where('balances.0.priceUsd', 97412.50)
            );
    }
}
