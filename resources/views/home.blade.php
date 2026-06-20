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
                    <div class="muted">Лабораторных работ: {{ $subject->labs_count }}</div>
                </div>
                <span class="badge">В очереди: {{ $subject->queue_entries_count }}</span>
            </div>
        </a>
    @empty
        <div class="card">
            <div class="empty-state">
                <svg class="empty-ill" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 10h21l9 9v35a2 2 0 0 1-2 2H18a2 2 0 0 1-2-2V12a2 2 0 0 1 2-2z"/>
                    <path d="M38 10v10h10M32 30v14M25 37h14"/>
                </svg>
                <p class="muted">Пока нет ни одного предмета. Староста ещё не добавил предметы.</p>
            </div>
        </div>
    @endforelse
@endsection
