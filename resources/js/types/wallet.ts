export type ChainId = 'btc' | 'eth' | 'bsc' | 'trx' | 'usdt' | 'usdc';

export type Chain = {
    id: ChainId;
    name: string;
    symbol: string;
    network: string;
    /** Tailwind-safe hex used for chain glyph + chart series */
    color: string;
    decimals: number;
};

export type AssetBalance = {
    chain: ChainId;
    address: string;
    balance: number;
    usdValue: number;
    priceUsd: number;
    change24hPct: number;
};

export type TransactionStatus = 'completed' | 'pending' | 'failed';
export type TransactionType = 'deposit' | 'withdrawal' | 'internal';

export type Transaction = {
    id: string;
    chain: ChainId;
    type: TransactionType;
    status: TransactionStatus;
    amount: number;
    usdValue: number;
    fee: number;
    txHash: string;
    fromAddress: string;
    toAddress: string;
    createdAt: string;
    confirmedAt: string | null;
    note?: string;
};

export type PortfolioPoint = {
    label: string;
    value: number;
};

export type SecurityStatus = {
    twoFactorEnabled: boolean;
    lastLogin: string;
    lastLoginDevice: string;
    nonCustodial: true;
};
