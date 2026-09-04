<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\RecoveryPhraseService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // Every account gets a 12-word recovery phrase. Only a hash is ever
        // stored; the plaintext is held in the session long enough to be shown
        // once on the post-registration reveal screen.
        $recoveryPhrase = app(RecoveryPhraseService::class)->generate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'recovery_phrase_hash' => app(RecoveryPhraseService::class)->hash($recoveryPhrase),
        ]);

        Session::put('pending_recovery_phrase', $recoveryPhrase);

        // Give the fresh account its demo wallets, sample activity and
        // portfolio history so the dashboard is alive from the first login.
        app(WalletService::class)->provision($user);

        return $user;
    }
}
