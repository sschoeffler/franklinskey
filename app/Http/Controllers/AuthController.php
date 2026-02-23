<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Bot protection
        if ($request->filled('website') || $request->filled('phone_number')) {
            return redirect('/');
        }
        if ($request->filled('_ts') && (time() - (int) $request->_ts) < 3) {
            return redirect('/');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput(['email' => $request->email]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Bot protection
        if ($request->filled('website') || $request->filled('phone_number')) {
            return redirect('/');
        }
        if ($request->filled('_ts') && (time() - (int) $request->_ts) < 3) {
            return redirect('/');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check for existing stub user (pre-created before signup)
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Link to existing stub account
            $user->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        }

        Auth::login($user, true);

        return redirect('/dashboard');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'name' => $user->name ?: $googleUser->getName(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(32)),
            ]);
        }

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
