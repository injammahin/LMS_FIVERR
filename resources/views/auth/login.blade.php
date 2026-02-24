@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="w-full max-w-md mx-auto mt-12 bg-white p-8 rounded-xl shadow-2xl border border-gray-200">
        <h2 class="text-3xl font-semibold text-center text-gray-800 mb-6">Login to Your Account</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-6">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input 
                    id="email" 
                    class="block mt-2 w-full border-b-2 border-gray-300 bg-transparent text-gray-900 focus:ring-0 focus:border-teal-500 py-3 px-4" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="Enter your email" 
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500" />
            </div>

            <!-- Password -->
            <div class="mb-6">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input 
                    id="password" 
                    class="block mt-2 w-full border-b-2 border-gray-300 bg-transparent text-gray-900 focus:ring-0 focus:border-teal-500 py-3 px-4" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="Enter your password" 
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
            </div>

            <!-- Remember Me -->
            <div class="block mb-4">
                <label for="remember_me" class="inline-flex items-center text-gray-800">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-500" name="remember">
                    <span class="ml-2 text-sm">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Forgot Password Link -->
            <div class="flex justify-between items-center mb-6">
                @if (Route::has('password.request'))
                    <a class="text-sm text-teal-500 hover:text-teal-700 underline" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="px-6 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-lg focus:ring-2 focus:ring-teal-300">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
@endsection