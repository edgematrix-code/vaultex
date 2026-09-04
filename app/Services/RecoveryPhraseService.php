<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Config;

class RecoveryPhraseService
{
    private const WORD_COUNT = 12;

    /**
     * The canonical BIP-39 English wordlist, shipped with the repo.
     */
    private const WORDLIST_PATH = __DIR__.'/../../database/data/bip39-english.txt';

    /** @var array<int, string>|null */
    private ?array $words = null;

    /**
     * Generate a fresh 12-word BIP-39 recovery phrase.
     */
    public function generate(): string
    {
        $words = $this->wordlist();

        $phrase = [];
        for ($i = 0; $i < self::WORD_COUNT; $i++) {
            $phrase[] = $words[random_int(0, count($words) - 1)];
        }

        return implode(' ', $phrase);
    }

    /**
     * Deterministic hash used for fast account lookup and safe storage.
     * The phrase itself is never stored in plain text.
     */
    public function hash(string $phrase): string
    {
        return hash_hmac('sha256', $this->normalize($phrase), (string) Config::get('app.key'));
    }

    /**
     * Verify that a submitted phrase belongs to the given user.
     */
    public function verify(User $user, string $phrase): bool
    {
        if (! $user->recovery_phrase_hash) {
            return false;
        }

        return hash_equals($user->recovery_phrase_hash, $this->hash($phrase));
    }

    /**
     * @return array<int, string>
     */
    public function wordlist(): array
    {
        if ($this->words === null) {
            $contents = file(self::WORDLIST_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($contents === false || count($contents) !== 2048) {
                throw new \RuntimeException('BIP-39 English wordlist is missing or malformed.');
            }

            $this->words = array_values(array_map('trim', $contents));
        }

        return $this->words;
    }

    /**
     * Canonical form: trimmed, lowercased, single spaces between words.
     */
    public function normalize(string $phrase): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $phrase) ?? ''));
    }
}
