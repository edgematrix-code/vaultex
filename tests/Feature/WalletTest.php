<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class WalletTest extends TestCase
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

    private function provisionedUser(): User
    {
        $user = User::factory()->create();

        $this->app->make(WalletService::class)->provision($user);

        return $user;
    }

    public function test_guests_are_redirected_away_from_wallet_pages()
    {
        $this->get('/wallet')->assertRedirect(route('login'));
        $this->get('/transactions')->assertRedirect(route('login'));
    }

    public function test_registration_provisions_the_full_wallet_state()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertCount(6, $user->wallets);
        $this->assertCount(6, $user->transactions);
        $this->assertCount(31, $user->portfolioSnapshots()->get());
        $this->assertCount(4, $user->notificationPreferences);

        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'chain' => 'btc']);
        $this->assertNotSame('', $user->wallets()->where('chain', 'btc')->firstOrFail()->address);
    }

    public function test_wallet_overview_lists_every_provisioned_chain()
    {
        $this->actingAs($this->provisionedUser());

        $this->get('/wallet')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('wallet/Overview')
                ->has('balances', 6)
                ->where('balances.0.chain', 'btc')
                ->where('balances.5.chain', 'trx')
            );
    }

    public function test_transactions_can_be_listed_and_are_scoped_to_the_user()
    {
        $user = $this->provisionedUser();
        $this->actingAs($user);

        $transaction = $user->transactions()->firstOrFail();

        $this->get('/transactions')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('transactions/Index')
                ->has('transactions', 6)
            );

        $this->get("/transactions/{$transaction->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('transactions/Show')
                ->where('transaction.id', $transaction->id)
            );

        // Another user must never see this transaction.
        $other = $this->provisionedUser();
        $this->actingAs($other);

        $this->get("/transactions/{$transaction->id}")->assertNotFound();
    }

    public function test_a_withdrawal_is_recorded_and_the_balance_is_reduced()
    {
        $user = $this->provisionedUser();
        $this->actingAs($user);

        $destination = 'bc1q'.str_repeat('a', 38);

        $response = $this->post('/wallet/withdraw', [
            'chain' => 'btc',
            'destination' => $destination,
            'amount' => '0.1',
        ]);

        $transaction = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->orderByDesc('id')
            ->firstOrFail();

        $response->assertRedirect(route('transactions.show', $transaction));

        $this->assertSame('pending', $transaction->status);
        $this->assertSame($destination, $transaction->to_address);
        $this->assertEquals(0.1, (float) $transaction->amount);
        $this->assertGreaterThan(6, $transaction->id);

        $wallet = Wallet::where('user_id', $user->id)->where('chain', 'btc')->firstOrFail();
        $this->assertEquals(0.42184 - 0.1, (float) $wallet->balance);
    }

    public function test_withdrawals_beyond_the_balance_are_rejected()
    {
        $user = $this->provisionedUser();
        $this->actingAs($user);

        $response = $this->post('/wallet/withdraw', [
            'chain' => 'eth',
            'destination' => '0x'.str_repeat('a', 40),
            'amount' => '9999',
        ]);

        $response->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('transactions', 6);
    }

    public function test_withdrawal_requires_a_valid_otp_when_two_factor_is_enabled()
    {
        $user = $this->provisionedUser();
        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->app->instance(TwoFactorAuthenticationProvider::class, new class
        {
            public function verify(string $secret, string $code): bool
            {
                return $code === '123456';
            }
        });

        $this->actingAs($user);

        $payload = [
            'chain' => 'btc',
            'destination' => 'bc1q'.str_repeat('b', 38),
            'amount' => '0.05',
        ];

        // Wrong code: nothing is recorded.
        $this->post('/wallet/withdraw', [...$payload, 'otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertDatabaseCount('transactions', 6);

        // Correct code: the withdrawal goes through.
        $this->post('/wallet/withdraw', [...$payload, 'otp' => '123456'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('transactions', 7);
    }

    public function test_notification_preferences_can_be_updated()
    {
        $user = $this->provisionedUser();
        $this->actingAs($user);

        $preferences = [
            ['event' => 'deposit_received', 'email' => false, 'inApp' => true],
            ['event' => 'withdrawal_sent', 'email' => true, 'inApp' => false],
            ['event' => 'new_device_login', 'email' => true, 'inApp' => true],
            ['event' => 'price_alerts', 'email' => true, 'inApp' => true],
        ];

        $this->patch(route('notifications.update'), ['preferences' => $preferences])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'event' => 'deposit_received',
            'email' => 0,
            'in_app' => 1,
        ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'event' => 'price_alerts',
            'email' => 1,
            'in_app' => 1,
        ]);
    }
}
