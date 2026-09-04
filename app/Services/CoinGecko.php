<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the CoinGecko "simple price" API.
 *
 * Prices are cached for a short window so page loads don't hammer the free
 * API. When the API is unreachable the caller falls back to the last known
 * prices stored on each wallet row.
 */
class CoinGecko
{
    /**
     * Number of seconds price responses stay cached.
     */
    public const CACHE_TTL = 60;

    /**
     * Cache key holding the most recent successful price response.
     */
    public const CACHE_KEY = 'coingecko.simple.prices';

    /**
     * Fetch live prices for every supported chain.
     *
     * @return array<string, array{priceUsd: float, change24hPct: float}>
     */
    public function prices(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                return $this->fetch();
            } catch (\Throwable) {
                // Return an empty set on failure; callers fall back to stored prices.
                return [];
            }
        });
    }

    /**
     * Perform the actual HTTP request against CoinGecko.
     *
     * @return array<string, array{priceUsd: float, change24hPct: float}>
     */
    private function fetch(): array
    {
        $ids = [];

        foreach ($this->chains() as $config) {
            $coinId = (string) ($config['coin_id'] ?? '');

            if ($coinId !== '') {
                $ids[] = $coinId;
            }
        }

        $response = Http::timeout(5)
            ->acceptJson()
            ->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => implode(',', $ids),
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true',
            ]);

        $response->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            return [];
        }

        $prices = [];

        foreach ($this->chains() as $chain => $config) {
            $coin = $payload[(string) ($config['coin_id'] ?? '')] ?? null;

            if (! is_array($coin)) {
                continue;
            }

            $prices[$chain] = [
                'priceUsd' => (float) ($coin['usd'] ?? 0),
                'change24hPct' => (float) ($coin['usd_24h_change'] ?? 0),
            ];
        }

        return $prices;
    }

    /**
     * Normalized view of the supported chain configuration.
     *
     * @return array<string, array<string, mixed>>
     */
    private function chains(): array
    {
        $chains = (array) config('wallet.chains');

        return array_map(static fn ($config): array => (array) $config, $chains);
    }
}
