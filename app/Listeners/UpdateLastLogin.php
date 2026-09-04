<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UpdateLastLogin
{
    /**
     * Record when and from where the user signed in.
     */
    public function handle(Login $event): void
    {
        $request = app(Request::class);

        $event->user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ])->save();
    }
}
