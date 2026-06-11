<x-guest-layout>
    <h1>Вход для старосты</h1>
    <p class="muted" style="margin:0 0 20px">Введите 4-значный код доступа.</p>

    @if ($errors->any())
        <div class="errors">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="otp-form">
        @csrf

        {{-- Реальное значение, которое уходит на сервер. --}}
        <input type="hidden" name="code" id="otp-code">

        {{-- 4 поля ввода кода. --}}
        <div class="otp" id="otp" role="group" aria-label="Код из 4 цифр">
            <input class="otp-input" type="text" inputmode="numeric" pattern="\d*"
                   maxlength="1" autocomplete="one-time-code" aria-label="Цифра 1" autofocus>
            <input class="otp-input" type="text" inputmode="numeric" pattern="\d*"
                   maxlength="1" aria-label="Цифра 2">
            <input class="otp-input" type="text" inputmode="numeric" pattern="\d*"
                   maxlength="1" aria-label="Цифра 3">
            <input class="otp-input" type="text" inputmode="numeric" pattern="\d*"
                   maxlength="1" aria-label="Цифра 4">
            {{-- Квадрат с галочкой: появляется при верном коде после слияния цифр. --}}
            <div class="otp-success" id="otp-success" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" width="30" height="30">
                    <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor"
                          stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        <div class="checkbox-row">
            <input type="checkbox" id="remember_me" name="remember">
            <label for="remember_me" style="margin:0">Запомнить меня</label>
        </div>

        <button type="submit" class="btn" id="otp-submit">Войти</button>
    </form>

    <script>
        (function () {
            const form    = document.getElementById('otp-form');
            const wrap     = document.getElementById('otp');
            const hidden   = document.getElementById('otp-code');
            const inputs   = Array.from(wrap.querySelectorAll('.otp-input'));

            // Собрать значение из всех полей в скрытый input.
            function sync() {
                hidden.value = inputs.map(i => i.value).join('');
            }

            // Все 4 поля заполнены?
            function isComplete() {
                return inputs.every(i => i.value.length === 1);
            }

            let busy = false;

            // Показ ошибки без перезагрузки страницы.
            function showError(message) {
                wrap.classList.remove('otp-error');
                void wrap.offsetWidth; // перезапуск анимации
                wrap.classList.add('otp-error');
                inputs.forEach(i => { i.value = ''; i.disabled = false; });
                sync();
                inputs[0].focus();
                let box = document.getElementById('otp-js-error');
                if (!box) {
                    box = document.createElement('div');
                    box.className = 'errors';
                    box.id = 'otp-js-error';
                    form.parentNode.insertBefore(box, form);
                }
                box.textContent = message;
            }

            // Анимация успеха: цифры сливаются в квадрат с галочкой, затем уходим на сервер.
            function playSuccessAndSubmit() {
                const box = document.getElementById('otp-js-error');
                if (box) box.remove();
                inputs.forEach(i => { i.blur(); i.disabled = true; });
                wrap.classList.add('otp-merge');
                // Даём анимации отыграть, затем настоящий вход.
                setTimeout(() => {
                    inputs.forEach(i => i.disabled = false); // disabled-поля не уходят в POST
                    form.requestSubmit();
                }, 850);
            }

            // Авто-проверка: как только введены все цифры — тихо проверяем код.
            async function maybeSubmit() {
                if (!isComplete() || busy) return;
                sync();
                busy = true;
                try {
                    const res = await fetch("{{ route('login.check') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ code: hidden.value }),
                    });
                    const data = await res.json();
                    if (data.ok) {
                        playSuccessAndSubmit();
                        return; // busy не сбрасываем — идёт отправка
                    }
                    showError(data.message || 'Неверный код.');
                } catch (e) {
                    // Сеть подвела — отправляем форму обычным способом.
                    form.requestSubmit();
                    return;
                }
                busy = false;
            }

            inputs.forEach((input, idx) => {
                input.addEventListener('input', () => {
                    // Оставляем только цифры.
                    input.value = input.value.replace(/\D/g, '').slice(0, 1);
                    sync();
                    if (input.value && idx < inputs.length - 1) {
                        inputs[idx + 1].focus();
                    }
                    maybeSubmit();
                });

                input.addEventListener('keydown', (e) => {
                    // Backspace на пустом поле — возврат к предыдущему.
                    if (e.key === 'Backspace' && !input.value && idx > 0) {
                        inputs[idx - 1].focus();
                    }
                    if (e.key === 'ArrowLeft' && idx > 0) inputs[idx - 1].focus();
                    if (e.key === 'ArrowRight' && idx < inputs.length - 1) inputs[idx + 1].focus();
                });

                // Вставка кода целиком в любое поле.
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const digits = (e.clipboardData.getData('text') || '')
                        .replace(/\D/g, '').slice(0, inputs.length).split('');
                    digits.forEach((d, i) => { if (inputs[i]) inputs[i].value = d; });
                    sync();
                    const next = Math.min(digits.length, inputs.length - 1);
                    inputs[next].focus();
                    maybeSubmit();
                });
            });

            // Если сервер вернул ошибку — подсветить поля и очистить для повторного ввода.
            @if ($errors->has('code'))
                wrap.classList.add('otp-error');
                inputs.forEach(i => i.value = '');
                sync();
                inputs[0].focus();
                wrap.addEventListener('animationend', () => wrap.classList.remove('otp-error'), { once: true });
            @endif
        })();
    </script>
</x-guest-layout>
