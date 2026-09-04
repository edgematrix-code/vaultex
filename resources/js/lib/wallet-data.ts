import type { AssetBalance, Chain, ChainId } from '@/types/wallet';

/**
 * Static chain registry and pure formatting helpers.
 *
 * Dynamic wallet data (balances, prices, transactions, history, security)
 * is served by the Laravel backend through Inertia page props — see the
 * Dashboard / Wallet / Transactions controllers and routes/web.php.
 */

export const CHAINS: Record<ChainId, Chain> = {
    btc: {
        id: 'btc',
        name: 'Bitcoin',
        symbol: 'BTC',
        network: 'Bitcoin',
        color: '#F2A93B',
        decimals: 8,
    },
    eth: {
        id: 'eth',
        name: 'Ethereum',
        symbol: 'ETH',
        network: 'Ethereum',
        color: '#8FA3D9',
        decimals: 6,
    },
    bsc: {
        id: 'bsc',
        name: 'BNB Smart Chain',
        symbol: 'BNB',
        network: 'BNB Smart Chain',
        color: '#E7B65C',
        decimals: 6,
    },
    trx: {
        id: 'trx',
        name: 'TRON',
        symbol: 'TRX',
        network: 'TRON',
        color: '#E2564F',
        decimals: 4,
    },
    usdt: {
        id: 'usdt',
        name: 'Tether USD',
        symbol: 'USDT',
        network: 'Ethereum',
        color: '#3FBF8F',
        decimals: 2,
    },
    usdc: {
        id: 'usdc',
        name: 'USD Coin',
        symbol: 'USDC',
        network: 'Ethereum',
        color: '#4C8DD9',
        decimals: 2,
    },
};

export const CHAIN_LIST: Chain[] = Object.values(CHAINS);

export function getPortfolioTotal(balances: Pick<AssetBalance, 'usdValue'>[]) {
    return balances.reduce((sum, b) => sum + b.usdValue, 0);
}

export function getPortfolioChangePct(
    balances: Pick<AssetBalance, 'change24hPct' | 'usdValue'>[],
    total: number,
) {
    const weighted = balances.reduce(
        (sum, b) => sum + b.change24hPct * b.usdValue,
        0,
    );
    return total > 0 ? weighted / total : 0;
}

const EXPLORERS: Record<ChainId, string> = {
    btc: 'https://mempool.space/tx/',
    eth: 'https://etherscan.io/tx/',
    bsc: 'https://bscscan.com/tx/',
    trx: 'https://tronscan.org/#/transaction/',
    usdt: 'https://etherscan.io/tx/',
    usdc: 'https://etherscan.io/tx/',
};

export function explorerUrl(chain: ChainId, txHash: string) {
    return `${EXPLORERS[chain]}${txHash}`;
}

export function formatUsd(value: number, opts: Intl.NumberFormatOptions = {}) {
    // Charts may request 0 decimals (e.g. whole-dollar portfolio totals); the
    // minimum must never exceed the maximum or Intl.NumberFormat throws.
    const maximumFractionDigits = opts.maximumFractionDigits ?? 2;

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: Math.min(2, maximumFractionDigits),
        maximumFractionDigits,
        ...opts,
    }).format(value);
}

export function formatCrypto(value: number, decimals = 4) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: decimals,
    }).format(value);
}

export function formatPct(value: number) {
    const sign = value > 0 ? '+' : '';
    return `${sign}${value.toFixed(2)}%`;
}

export function truncateAddress(address: string, lead = 6, tail = 6) {
    if (address.length <= lead + tail + 3) return address;
    return `${address.slice(0, lead)}…${address.slice(-tail)}`;
}

export function formatRelativeTime(iso: string) {
    const date = new Date(iso);
    const diffMs = Date.now() - date.getTime();
    const diffMin = Math.round(diffMs / 60_000);
    if (diffMin < 1) return 'just now';
    if (diffMin < 60) return `${diffMin}m ago`;
    const diffHr = Math.round(diffMin / 60);
    if (diffHr < 24) return `${diffHr}h ago`;
    const diffDay = Math.round(diffHr / 24);
    if (diffDay < 30) return `${diffDay}d ago`;
    return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
