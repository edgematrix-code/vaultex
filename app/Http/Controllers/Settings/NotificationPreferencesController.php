<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferencesController extends Controller
{
    /**
     * Render the notification settings page.
     */
    public function edit(Request $request, WalletService $wallets): Response
    {
        return Inertia::render('settings/Notifications', [
            'preferences' => $wallets->notificationPreferences($request->user()),
        ]);
    }

    /**
     * Persist the notification preference toggles.
     */
    public function update(Request $request, WalletService $wallets): RedirectResponse
    {
        $validated = $request->validate([
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.*.event' => ['required', 'string', Rule::in(WalletService::PREFERENCE_EVENTS)],
            'preferences.*.email' => ['required', 'boolean'],
            'preferences.*.inApp' => ['required', 'boolean'],
        ]);

        $wallets->updateNotificationPreferences($request->user(), $validated['preferences']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Notification preferences updated.',
        ]);

        return redirect()->back();
    }
}
