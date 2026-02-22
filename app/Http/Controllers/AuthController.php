<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Find existing user by email (supports stub accounts created before signup)
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link Google ID to existing account
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'name' => $user->name ?: $googleUser->getName(),
            ]);
        } else {
            // Create new user
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(32)),
            ]);
        }

        // Migrate session-based projects to this user
        $sessionId = request()->cookie('fk_session_id');
        if ($sessionId) {
            \App\Models\Project::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
        }

        Auth::login($user, true);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
