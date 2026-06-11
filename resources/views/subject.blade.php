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
        {{-- Сюда AJAX подгружает свежий список. --}}
        <div id="queue-list" style="margin-top:12px">
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
            @if ($subject->labs->isEmpty())
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
        const queueUrl = "{{ route('subjects.queue', $subject) }}";
        async function refreshQueue() {
            try {
                const res = await fetch(queueUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) {
                    document.getElementById('queue-list').innerHTML = await res.text();
                    const now = new Date();
                    document.getElementById('updated').textContent =
                        'обновлено в ' + now.toLocaleTimeString('ru-RU');
                }
            } catch (e) { /* тихо игнорируем сетевые сбои */ }
        }
        setInterval(refreshQueue, 5000); // каждые 5 секунд
    </script>
@endsection
