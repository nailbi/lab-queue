@extends('layouts.app')

@section('title', 'Выбор предмета')

@section('content')
    <h1>Выберите предмет</h1>
    <p class="sub">Выберите предмет, по которому хотите защищать лабораторные работы, и встаньте в очередь.</p>

    @forelse ($subjects as $subject)
        <a href="{{ route('subjects.show', $subject) }}" class="card card-link">
            <div class="row">
                <div>
                    <strong>{{ $subject->name }}</strong>
                    <div class="muted">Лабораторных работ: {{ $subject->lab_count }}</div>
                </div>
                <span class="badge">В очереди: {{ $subject->queue_entries_count }}</span>
            </div>
        </a>
    @empty
        <div class="card">Пока нет ни одного предмета. Староста ещё не добавил предметы.</div>
    @endforelse
@endsection
