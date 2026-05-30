<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Очередь на сдачу лабораторных')</title>
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header>
        <div class="wrap">
            <a href="{{ route('home') }}" class="brand">
                {{-- Положите файл логотипа в public/images/logo.png --}}
                <img src="{{ asset('images/logo.png') }}" alt="Московский Политех"
                     onerror="this.style.display='none'">
                <span class="brand-text">Очередь на сдачу лаб</span>
            </a>
            <nav>
                @auth
                    <a href="{{ route('admin.subjects.index') }}">Панель старосты</a>
                @else
                    <a href="{{ route('login') }}">Вход для старосты</a>
                @endauth
            </nav>
        </div>
    </header>
    <main>
        <div class="wrap">
            @if (session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="errors">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
