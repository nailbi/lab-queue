<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Очередь на сдачу лабораторных')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Roboto:wght@400;500;700&display=swap&subset=cyrillic">
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        // Применяем сохранённый ручной выбор до рендера, чтобы не было вспышки.
        // Если выбора нет — системная тема применяется через CSS media query автоматически.
        var saved = localStorage.getItem('theme');
        if (saved) document.documentElement.setAttribute('data-theme', saved);
    </script>
</head>
<body>
    @include('partials.preloader', ['variant' => 'globe'])
    <header>
        <div class="wrap">
            <a href="{{ route('home') }}" class="brand">
                {{-- Положите файл логотипа в public/images/logo.png --}}
                <img src="{{ asset('images/logo.png') }}" alt="Московский Политех"
                     onerror="this.style.display='none'">
                <span class="brand-text">Очередь на сдачу лаб</span>
            </a>
            <nav>
                <a href="{{ route('schedule') }}"
                   @class(['nav-btn', 'is-active' => request()->routeIs('schedule')])>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <span>Расписание</span>
                </a>
                <button type="button" id="theme-toggle" class="theme-toggle" title="Сменить тему" aria-label="Сменить тему">🌙</button>
                @auth
                    <a href="{{ route('admin.subjects.index') }}"
                       @class(['nav-btn', 'is-active' => request()->routeIs('admin.subjects.*')])>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="8" y="3" width="8" height="4" rx="1"/>
                            <path d="M9 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3"/>
                        </svg>
                        <span>Панель старосты</span>
                    </a>
                    <a href="{{ route('admin.schedule.index') }}"
                       @class(['nav-btn', 'is-active' => request()->routeIs('admin.schedule.*')])>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <span>Ред. расписание</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn sm ghost">Выйти</button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       @class(['nav-btn', 'is-active' => request()->routeIs('login')])>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                        </svg>
                        <span>Вход для старосты</span>
                    </a>
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
    <script>
        (function () {
            const root = document.documentElement;
            const btn = document.getElementById('theme-toggle');
            const mq = window.matchMedia('(prefers-color-scheme: dark)');

            function isDark() {
                var saved = root.getAttribute('data-theme');
                if (saved === 'dark') return true;
                if (saved === 'light') return false;
                return mq.matches; // нет ручного выбора — смотрим систему
            }

            function updateIcon() {
                btn.textContent = isDark() ? '☀️' : '🌙';
            }

            updateIcon();

            // Если системная тема меняется и нет ручного выбора — реагируем.
            mq.addEventListener('change', function () {
                if (!localStorage.getItem('theme')) updateIcon();
            });

            btn.addEventListener('click', function () {
                var next = isDark() ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                root.setAttribute('data-theme', next);
                updateIcon();
            });
        })();
    </script>
</body>
</html>
