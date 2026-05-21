@props(['title' => 'Autodelen'])
<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Autodelen">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" type="image/svg+xml" href="/icon.svg">
    <link rel="apple-touch-icon" href="/icon.svg">
    <title>{{ $title }}</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-full bg-slate-50 text-slate-900 antialiased" style="padding-bottom: env(safe-area-inset-bottom);">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6 sm:py-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-base font-semibold text-slate-900 sm:text-lg">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 11l1.5-4.5A2 2 0 018.4 5h7.2a2 2 0 011.9 1.5L19 11M5 11h14M5 11v6a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-6M7 14h.01M17 14h.01"/>
                    </svg>
                </span>
                Autodelen
            </a>

            @auth
            <div class="flex items-center gap-2 sm:gap-3">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="" class="h-8 w-8 rounded-full ring-1 ring-slate-200">
                @endif
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-500">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" aria-label="Afmelden"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50 sm:px-3 sm:py-1.5 sm:text-sm sm:font-medium sm:text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 sm:hidden">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">Afmelden</span>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
        @if(session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-800">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
