@extends('layouts.app')

@section('title', $subject->name)

@section('content')
    <p class="muted"><a href="{{ route('home') }}">← Все предметы</a></p>
    <h1>{{ $subject->name }}</h1>
    <p class="sub">Лабораторных работ по предмету: {{ $subject->labs->count() }}</p>

    @if ($subject->description)
        {{-- Описание предмета — раздел "статьи" из задания. --}}
        <div class="card">{!! nl2br(e($subject->description)) !!}</div>
    @endif

    <div class="card">
        <div class="row">
            <h2 style="margin:0">Текущая очередь</h2>
            <span class="muted" id="updated">обновляется автоматически</span>
        </div>
        {{-- Сюда AJAX подгружает свежий список. aria-live — чтобы скринридер
             объявлял изменения очереди без перезагрузки страницы. --}}
        <div id="queue-list" style="margin-top:12px" aria-live="polite">
            @include('partials.queue_list', ['subject' => $subject])
        </div>
    </div>

    @if ($myEntry)
        {{-- Студент уже в очереди: повторная запись закрыта, можно только выйти. --}}
        <div class="card my-entry">
            <h2 style="margin-top:0">Вы в очереди</h2>
            <p>
                {{ $myEntry->student_name }}, ваша позиция —
                <span class="pos">{{ $myEntry->position }}</span>.
                Записаться в очередь можно только один раз.
            </p>
            <form method="POST" action="{{ route('subjects.leave', [$subject, $myEntry]) }}"
                  onsubmit="return confirm('Выйти из очереди? Своё место вы потеряете.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn danger">Выйти из очереди</button>
            </form>
        </div>
    @else
        <div class="card">
            <h2 style="margin-top:0">Записаться в очередь</h2>
            @if (! $subject->registration_open)
                <p class="muted">Запись в очередь закрыта старостой. Дождитесь, пока её снова откроют.</p>
            @elseif ($subject->labs->isEmpty())
                <p class="muted">Староста ещё не добавил список лабораторных работ — запись пока недоступна.</p>
            @else
                <form method="POST" action="{{ route('subjects.join', $subject) }}">
                    @csrf
                    <label for="student_name">Ваше имя</label>
                    <input type="text" id="student_name" name="student_name" value="{{ old('student_name') }}" required>

                    <label>Какие работы хотите защитить</label>
                    <div class="lab-picker">
                        @foreach ($subject->labs as $lab)
                            <label class="lab-chip">
                                <input type="checkbox" name="labs[]" value="{{ $lab->id }}"
                                       @checked(in_array($lab->id, old('labs', [])))>
                                <span>{{ $lab->title }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="muted lab-hint">Нажмите на кнопки нужных лабораторных — можно выбрать несколько.</p>

                    <div style="margin-top:16px">
                        <button type="submit" class="btn">Встать в очередь</button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    <script>
        // Динамический элемент: автообновление списка очереди без перезагрузки страницы.
        // Обновляем DOM только при реальном изменении и анимируем диффы (FLIP + fade).
        (function () {
            const queueUrl = "{{ route('subjects.queue', $subject) }}";
            const listEl   = document.getElementById('queue-list');
            const baseTitle = document.title;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

            function versionOf(node) {
                const w = node.querySelector('.table-scroll');
                return w ? w.dataset.version : 'empty';
            }

            function stamp() {
                const now = new Date();
                document.getElementById('updated').textContent =
                    'обновлено в ' + now.toLocaleTimeString('ru-RU');
            }

            // Меняем заголовок вкладки, когда подошла ваша очередь (видно с другой вкладки).
            function syncTitle() {
                document.title = listEl.querySelector('tr.is-turn') ? '🔔 Ваша очередь!' : baseTitle;
            }

            async function refreshQueue() {
                let html;
                try {
                    const res = await fetch(queueUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) return;
                    html = await res.text();
                } catch (e) { return; } // тихо игнорируем сетевые сбои

                const tmp = document.createElement('div');
                tmp.innerHTML = html;

                // Ничего не изменилось — только отметим время и выйдем (без перерисовки).
                if (versionOf(tmp) === versionOf(listEl)) { stamp(); return; }

                const animate = !reduce.matches;

                // Запоминаем позиции существующих строк до подмены (для FLIP).
                const oldRects = {};
                listEl.querySelectorAll('tr[data-key]').forEach(function (tr) {
                    oldRects[tr.dataset.key] = tr.getBoundingClientRect().top;
                });
                const newKeys = new Set(
                    [].map.call(tmp.querySelectorAll('tr[data-key]'), function (t) { return t.dataset.key; })
                );
                const leaving = [].filter.call(
                    listEl.querySelectorAll('tr[data-key]'),
                    function (t) { return !newKeys.has(t.dataset.key); }
                );

                function commit() {
                    listEl.innerHTML = html;
                    if (animate) {
                        listEl.querySelectorAll('tr[data-key]').forEach(function (tr) {
                            const key = tr.dataset.key;
                            if (key in oldRects) {
                                // FLIP: сдвиг с прежнего места на новое.
                                const dy = oldRects[key] - tr.getBoundingClientRect().top;
                                if (dy) {
                                    tr.style.transition = 'none';
                                    tr.style.transform = 'translateY(' + dy + 'px)';
                                    requestAnimationFrame(function () {
                                        tr.style.transition = '';
                                        tr.style.transform = '';
                                    });
                                }
                            } else {
                                // Новая строка — мягко появляется.
                                tr.classList.add('row-enter');
                                requestAnimationFrame(function () {
                                    requestAnimationFrame(function () { tr.classList.remove('row-enter'); });
                                });
                            }
                        });
                    }
                    syncTitle();
                    stamp();
                }

                // Сначала даём уходящим строкам растаять, затем подменяем содержимое.
                if (animate && leaving.length) {
                    leaving.forEach(function (tr) { tr.classList.add('row-leave'); });
                    setTimeout(commit, 200);
                } else {
                    commit();
                }
            }

            syncTitle(); // на случай, если вы уже первый при загрузке
            setInterval(refreshQueue, 5000); // каждые 5 секунд
        })();
    </script>
@endsection
