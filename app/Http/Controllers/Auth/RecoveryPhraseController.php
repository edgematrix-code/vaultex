<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RecoveryPhraseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RecoveryPhraseController extends Controller
{
    /**
     * Sign the user in using their 12-word recovery phrase.
     */
    public function store(Request $request, RecoveryPhraseService $service): SymfonyResponse
    {
        $validated = $request->validate([
            'phrase' => ['required', 'string', 'max:512'],
        ]);

        $phrase = $service->normalize($validated['phrase']);

        $user = User::query()
            ->whereNotNull('recovery_phrase_hash')
            ->get()
            ->first(fn (User $candidate) => $service->verify($candidate, $phrase));

        if (! $user) {
            throw ValidationException::withMessages([
                'phrase' => __('That recovery phrase does not match any account.'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // An Inertia location response (409 + X-Inertia-Location) makes the
        // client perform a real navigation to the dashboard, bypassing the
        // kit's JSON-form machinery entirely.
        return Inertia::location(route('dashboard'));
    }

    /**
     * Show the freshly generated recovery phrase once, right after signup.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $phrase = $request->session()->get('pending_recovery_phrase');

        if (! is_string($phrase) || trim($phrase) === '') {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/RecoveryPhrase', [
            'words' => preg_split('/\s+/', trim($phrase)) ?: [],
        ]);
    }

    /**
     * Acknowledge that the user saved their recovery phrase.
     */
    public function acknowledge(Request $request): SymfonyResponse
    {
        $request->session()->forget('pending_recovery_phrase');

        // 409 + X-Inertia-Location → the client navigates to the dashboard.
        return Inertia::location(route('dashboard'));
    }
}
