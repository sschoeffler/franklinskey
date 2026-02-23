@extends('layouts.app')

@section('title', "Sign Up — Franklin's Key")

@section('content')
<div class="max-w-sm mx-auto px-4 py-16">

    <div class="text-center mb-8">
        <div class="text-4xl mb-3">&#x1F5DD;</div>
        <h1 class="text-2xl font-bold bg-gradient-to-r from-amber-400 to-yellow-300 bg-clip-text text-transparent">Create Account</h1>
    </div>

    <form method="POST" action="/register" class="space-y-4">
        @csrf
        <input type="hidden" name="_ts" value="{{ time() }}">
        <div style="position:absolute;left:-9999px;">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
            <input type="text" name="phone_number" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus maxlength="100" class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition">
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition">
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">Password</label>
            <input type="password" name="password" required minlength="8" class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition">
        </div>

        <div>
            <label class="block text-xs text-gray-400 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required minlength="8" class="w-full px-4 py-3 bg-white/[0.04] border border-white/[0.08] rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/30 transition">
        </div>

        @if($errors->any())
        <div class="text-sm text-red-400">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <button type="submit" class="w-full px-4 py-3 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-lg transition">Create Account</button>
    </form>

    <div class="my-6 flex items-center gap-3">
        <div class="flex-1 border-t border-white/[0.06]"></div>
        <span class="text-xs text-gray-500">or</span>
        <div class="flex-1 border-t border-white/[0.06]"></div>
    </div>

    <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 text-sm font-semibold text-gray-900 bg-white rounded-lg hover:bg-gray-100 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
        Sign up with Google
    </a>

    <p class="text-center text-sm text-gray-500 mt-6">
        Already have an account? <a href="/login" class="text-amber-400 hover:text-amber-300 transition">Sign in</a>
    </p>
</div>
@endsection
