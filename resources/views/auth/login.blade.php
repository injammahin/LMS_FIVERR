@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    {{-- ✅ Animated Gradient + Glass Login (gradient changes on refresh) --}}
    <div class="min-h-screen flex items-center justify-center px-4 py-10 relative overflow-hidden bg-slate-950">
        {{-- Background layers --}}
        <div id="bgGradient" class="absolute inset-0 opacity-95"></div>

        {{-- soft glow blobs --}}
        <div class="pointer-events-none absolute -top-28 -left-24 h-72 w-72 rounded-full blur-3xl opacity-30"
            style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.9), transparent 60%);"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-24 h-72 w-72 rounded-full blur-3xl opacity-25"
            style="background: radial-gradient(circle at 70% 70%, rgba(255,255,255,.7), transparent 60%);"></div>

        {{-- subtle grid --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.08]" style="background-image:
                                    linear-gradient(to right, rgba(255,255,255,.20) 1px, transparent 1px),
                                    linear-gradient(to bottom, rgba(255,255,255,.20) 1px, transparent 1px);
                                    background-size: 42px 42px;"></div>

        {{-- Floating particles --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <span class="particle p1"></span>
            <span class="particle p2"></span>
            <span class="particle p3"></span>
            <span class="particle p4"></span>
            <span class="particle p5"></span>
        </div>

        {{-- Card --}}
        <div class="relative w-full max-w-md">
            {{-- glass border glow --}}
            <div class="absolute -inset-[1px] rounded-3xl bg-white/20 blur-md opacity-40"></div>

            <div
                class="relative rounded-3xl border border-white/15 bg-white/10 backdrop-blur-2xl shadow-[0_20px_70px_rgba(0,0,0,.45)] overflow-hidden">
                {{-- top shine --}}
                <div class="pointer-events-none absolute -top-32 left-1/2 h-64 w-64 -translate-x-1/2 rounded-full blur-3xl opacity-25"
                    style="background: radial-gradient(circle, rgba(255,255,255,.85), transparent 60%);"></div>

                <div class="p-7 sm:p-8">
                    {{-- Header --}}
                    <div class="flex flex-col items-center text-center">
                        <img src="{{ asset('img/logo_lms.jpg') }}" alt="Logo" class="h-16 w-auto mb-3 object-contain">
                        <p class="text-sm text-white/70">
                            Log in to continue to your portal
                        </p>
                    </div>

                    {{-- Divider --}}
                    <div class="my-6 h-px bg-white/10"></div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        {{-- Email or Username --}}
                        <div>
                            <x-input-label for="login" value="Email or Username" class="text-white/80" />
                            <div class="mt-2 relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-dark/55">
                                    <i class="fa-regular fa-user"></i>
                                </span>
                                <x-text-input id="login" class="block w-full rounded-2xl border border-white/10 bg-white/10 text-white placeholder:text-white/40 pl-10 pr-3 py-3
                                                               focus:ring-2 focus:ring-white/20 focus:border-white/20"
                                    type="text" name="login" :value="old('login')" required autofocus
                                    autocomplete="username" placeholder="Enter email or username" />
                            </div>
                            <x-input-error :messages="$errors->get('login')" class="mt-2 text-sm text-red-300" />
                        </div>

                        {{-- Password --}}
                        <div x-data="{ show:false }">
                            <x-input-label for="password" :value="__('Password')" class="text-white/80" />
                            <div class="mt-2 relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-dark/55">
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <input id="password" class="block w-full rounded-2xl border border-white/10 bg-white/10 text-white placeholder:text-white/40 pl-10 pr-11 py-3
                                                               focus:ring-2 focus:ring-white/20 focus:border-white/20"
                                    :type="show ? 'text' : 'password'" name="password" required
                                    autocomplete="current-password" placeholder="Enter your password" />

                                <button type="button" @click="show=!show"
                                    class="absolute inset-y-0 right-3 flex items-center text-dark/55 hover:text-dark transition">
                                    <i x-show="!show" class="fa-regular fa-eye"></i>
                                    <i x-show="show" class="fa-regular fa-eye-slash"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-300" />
                        </div>

                        {{-- Remember / Forgot --}}
                        <div class="flex items-center justify-between gap-3">
                            <label for="remember_me" class="inline-flex items-center text-white/80 text-sm select-none">
                                <input id="remember_me" type="checkbox"
                                    class="rounded border-white/20 bg-white/10 text-white shadow-sm focus:ring-white/20"
                                    name="remember">
                                <span class="ml-2">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm text-white/80 hover:text-white underline underline-offset-4"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot?') }}
                                </a>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="group relative w-full overflow-hidden rounded-2xl px-5 py-3 text-sm font-semibold text-white
                                                           shadow-[0_12px_28px_rgba(0,0,0,.35)] border border-white/10"
                            id="loginBtn">
                            <span class="absolute inset-0 opacity-90" id="btnGradient"></span>
                            <span class="absolute inset-0 opacity-0 group-hover:opacity-100 transition"
                                style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.35), transparent 55%);"></span>

                            <span class="relative inline-flex items-center justify-center gap-2">
                                <span>{{ __('Log in') }}</span>
                                <i
                                    class="fa-solid fa-arrow-right text-[12px] opacity-90 group-hover:translate-x-0.5 transition"></i>
                            </span>
                        </button>

                        {{-- small helper line --}}
                        <p class="text-xs text-white/55 text-center pt-1">
                            Tip: You can login using your <span class="text-white/80 font-semibold">email</span> or <span
                                class="text-white/80 font-semibold">username</span>.
                        </p>
                    </form>
                </div>
            </div>
        </div>

        {{-- Styles --}}
        <style>
            /* Animated gradient background */
            #bgGradient {
                background: radial-gradient(1200px circle at 20% 20%, rgba(255, 255, 255, .16), transparent 55%),
                    radial-gradient(900px circle at 80% 80%, rgba(255, 255, 255, .10), transparent 55%),
                    linear-gradient(120deg, #0b1220, #0b1220);
                animation: bgShift 10s ease-in-out infinite alternate;
            }

            @keyframes bgShift {
                0% {
                    filter: hue-rotate(0deg) saturate(110%);
                    transform: scale(1.02);
                }

                100% {
                    filter: hue-rotate(18deg) saturate(130%);
                    transform: scale(1.05);
                }
            }

            /* particles */
            .particle {
                position: absolute;
                width: 10px;
                height: 10px;
                border-radius: 9999px;
                background: rgba(255, 255, 255, .22);
                filter: blur(.5px);
                animation: floatUp 10s linear infinite;
                opacity: .75;
            }

            .p1 {
                left: 10%;
                bottom: -20px;
                animation-duration: 11s;
                transform: scale(1.1);
            }

            .p2 {
                left: 28%;
                bottom: -30px;
                animation-duration: 13s;
                transform: scale(.9);
            }

            .p3 {
                left: 55%;
                bottom: -25px;
                animation-duration: 10s;
                transform: scale(1.3);
            }

            .p4 {
                left: 72%;
                bottom: -35px;
                animation-duration: 14s;
                transform: scale(.85);
            }

            .p5 {
                left: 88%;
                bottom: -22px;
                animation-duration: 12s;
                transform: scale(1.0);
            }

            @keyframes floatUp {
                0% {
                    transform: translateY(0) translateX(0) scale(1);
                    opacity: .0;
                }

                10% {
                    opacity: .75;
                }

                60% {
                    opacity: .55;
                }

                100% {
                    transform: translateY(-880px) translateX(40px) scale(1.35);
                    opacity: 0;
                }
            }
        </style>

        {{-- Scripts (random gradient + matching button/logo chip) --}}
        <script>
            (function () {
                // A few high-quality gradient palettes
                const palettes = [
                    { bg: ['#0ea5e9', '#8b5cf6', '#111827'], btn: ['#22c55e', '#06b6d4'] },  // aqua/violet
                    { bg: ['#22c55e', '#14b8a6', '#0b1220'], btn: ['#a855f7', '#6366f1'] },  // emerald/teal
                    { bg: ['#f97316', '#ec4899', '#111827'], btn: ['#0ea5e9', '#6366f1'] },  // orange/pink
                    { bg: ['#a855f7', '#06b6d4', '#0b1220'], btn: ['#f97316', '#ef4444'] },  // purple/cyan
                    { bg: ['#60a5fa', '#34d399', '#0b1220'], btn: ['#8b5cf6', '#06b6d4'] },  // blue/green
                ];

                const pick = palettes[Math.floor(Math.random() * palettes.length)];

                const bg = document.getElementById('bgGradient');
                const btn = document.getElementById('btnGradient');
                const chip = document.getElementById('logoChip');

                // Background: layered gradients
                bg.style.background = `
                                        radial-gradient(1200px circle at 20% 20%, rgba(255,255,255,.16), transparent 55%),
                                        radial-gradient(900px circle at 80% 80%, rgba(255,255,255,.10), transparent 55%),
                                        linear-gradient(120deg, ${pick.bg[0]}, ${pick.bg[1]}, ${pick.bg[2]})
                                    `;

                // Button gradient
                btn.style.background = `linear-gradient(90deg, ${pick.btn[0]}, ${pick.btn[1]})`;

                // Logo chip gradient
                chip.style.background = `linear-gradient(135deg, ${pick.btn[0]}, ${pick.btn[1]})`;
            })();
        </script>
    </div>
@endsection