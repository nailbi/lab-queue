<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * AJAX-проверка кода перед отправкой формы (для анимации успеха).
     * Использует тот же rate-limiter, что и логин, чтобы не дать перебирать код.
     */
    public function check(Request $request): JsonResponse
    {
        $key = Str::transliterate('login|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Слишком много попыток. Подождите '.RateLimiter::availableIn($key).' сек.',
            ], 429);
        }

        $code = (string) $request->input('code');

        if (! preg_match('/^\d{4}$/', $code) || ! hash_equals((string) config('auth.admin_code'), $code)) {
            RateLimiter::hit($key);

            return response()->json(['ok' => false, 'message' => 'Неверный код.']);
        }

        // Не очищаем лимитер здесь — это сделает сам логин после успешного входа.
        return response()->json(['ok' => true]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.subjects.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
