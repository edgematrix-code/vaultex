<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported chains
    |--------------------------------------------------------------------------
    |
    | Every chain a user wallet can be provisioned for. `coin_id` maps the
    | chain to the identifier CoinGecko uses for live pricing lookups.
    |
    */

    'chains' => [
        'btc' => [
            'coin_id' => 'bitcoin',
            'symbol' => 'BTC',
            'initial_balance' => 0.42184,
            'seed_price' => 97412.50,
            'seed_change' => 2.14,
        ],
        'eth' => [
            'coin_id' => 'ethereum',
            'symbol' => 'ETH',
            'initial_balance' => 4.8821,
            'seed_price' => 3412.20,
            'seed_change' => -1.02,
        ],
        'usdt' => [
            'coin_id' => 'tether',
            'symbol' => 'USDT',
            'initial_balance' => 12480.55,
            'seed_price' => 1.00,
            'seed_change' => 0.01,
        ],
        'bsc' => [
            'coin_id' => 'binancecoin',
            'symbol' => 'BNB',
            'initial_balance' => 18.204,
            'seed_price' => 612.40,
            'seed_change' => 4.62,
        ],
        'usdc' => [
            'coin_id' => 'usd-coin',
            'symbol' => 'USDC',
            'initial_balance' => 6204.10,
            'seed_price' => 1.00,
            'seed_change' => 0.0,
        ],
        'trx' => [
            'coin_id' => 'tron',
            'symbol' => 'TRX',
            'initial_balance' => 42180.0,
            'seed_price' => 0.1642,
            'seed_change' => -0.38,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Withdrawal fee estimation
    |--------------------------------------------------------------------------
    |
    | Fees are an estimate shown to the user before confirmation and stored on
    | the resulting transaction in USD.
    |
    */

    'network_fee_rate' => 0.006,

    'network_fee_min_usd' => 0.50,

];
