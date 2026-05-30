<x-guest-layout>
    <h1>Вход для старосты</h1>
    <p class="muted" style="margin:0 0 16px">Введите ваши данные для входа в панель.</p>

    @if ($errors->any())
        <div class="errors">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>

        <div class="checkbox-row">
            <input type="checkbox" id="remember_me" name="remember">
            <label for="remember_me" style="margin:0">Запомнить меня</label>
        </div>

        <button type="submit" class="btn">Войти</button>
    </form>
</x-guest-layout>
