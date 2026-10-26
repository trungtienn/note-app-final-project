<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')
            @if (auth()->check() && !auth()->user()->hasVerifiedEmail())
            <div class="bg-amber-500 border-b border-amber-600 text-white px-4 py-3 shadow-sm">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra hộp thư email (<strong>{{ auth()->user()->email }}</strong>) để hoàn tất kích hoạt.</span>
                    </div>
                    
                    @if (session('status') == 'verification-link-sent')
                        <span class="text-xs bg-amber-600 px-2.5 py-1 rounded-full text-white">Đã gửi lại link kích hoạt!</span>
                    @else
                        <form method="POST" action="{{ route('verification.send') }}" class="inline">
                            @csrf
                            <button type="submit" class="underline hover:text-amber-100 text-xs sm:text-sm font-semibold transition">
                                Gửi lại email kích hoạt
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
