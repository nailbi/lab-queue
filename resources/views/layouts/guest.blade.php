<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход для старосты</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Roboto:wght@400;500;700&display=swap&subset=cyrillic">
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        // Сохранённый ручной выбор темы; без него работает системная тема через CSS.
        var saved = localStorage.getItem('theme');
        if (saved) document.documentElement.setAttribute('data-theme', saved);
    </script>
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-logo">
            <a href="{{ route('home') }}" style="text-decoration:none">
                <span class="brand-text">Очередь на сдачу лаб</span>
            </a>
        </div>
        <div class="auth-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
