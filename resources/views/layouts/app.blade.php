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
    <header>
        <div class="wrap">
            <a href="{{ route('home') }}" class="brand">
                {{-- Положите файл логотипа в public/images/logo.png --}}
                <img src="{{ asset('images/logo.png') }}" alt="Московский Политех"
                     onerror="this.style.display='none'">
                <span class="brand-text">Очередь на сдачу лаб</span>
            </a>
            <nav>
                <button type="button" id="theme-toggle" class="theme-toggle" title="Сменить тему" aria-label="Сменить тему">🌙</button>
                @auth
                    <a href="{{ route('admin.subjects.index') }}">Панель старосты</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn sm ghost">Выйти</button>
                    </form>
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
