@extends('layouts.app')

@section('title', $subject->name)

@section('content')
    <p class="muted"><a href="{{ route('home') }}">← Все предметы</a></p>
    <h1>{{ $subject->name }}</h1>
    <p class="sub">Лабораторных работ по предмету: {{ $subject->lab_count }}</p>

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

    <div class="card">
        <h2 style="margin-top:0">Записаться в очередь</h2>
        <form method="POST" action="{{ route('subjects.join', $subject) }}">
            @csrf
            <label for="student_name">Ваше имя</label>
            <input type="text" id="student_name" name="student_name" value="{{ old('student_name') }}" required>

            <label for="lab_titles">Названия лабораторных работ (каждая с новой строки)</label>
            <textarea id="lab_titles" name="lab_titles" required>{{ old('lab_titles') }}</textarea>

            <label for="labs_to_pass">Сколько работ хотите защитить</label>
            <input type="number" id="labs_to_pass" name="labs_to_pass" min="1" max="100" value="{{ old('labs_to_pass', 1) }}" required>

            <div style="margin-top:16px">
                <button type="submit" class="btn">Встать в очередь</button>
            </div>
        </form>
    </div>

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
