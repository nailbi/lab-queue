@extends('layouts.app')

@section('title', 'Панель старосты — Предметы')

@section('content')
    <h1>Предметы</h1>
    <p class="sub">Создавайте предметы, добавляйте кнопки лабораторных, переходите к управлению очередью.</p>

    <div class="card">
        <h2 style="margin-top:0">Добавить предмет</h2>
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            <label for="name">Название предмета</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>

            <label for="description">Описание (необязательно)</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>

            <div style="margin-top:16px">
                <button type="submit" class="btn">Создать предмет</button>
            </div>
        </form>
    </div>

    @forelse ($subjects as $subject)
        <div class="card">
            {{-- Форма редактирования предмета --}}
            <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                @csrf
                @method('PUT')
                <label>Название</label>
                <input type="text" name="name" value="{{ $subject->name }}" required>
                <label>Описание</label>
                <textarea name="description">{{ $subject->description }}</textarea>
                <div style="margin-top:14px">
                    <button type="submit" class="btn sm">Сохранить</button>
                </div>
            </form>

            {{-- Кнопки лабораторных: то, что студенты видят на странице записи. --}}
            <div class="lab-admin" style="margin-top:12px; border-top:1px solid var(--line); padding-top:12px">
                <label style="margin-top:0">Лабораторные работы ({{ $subject->labs->count() }})</label>
                <div class="lab-picker">
                    @foreach ($subject->labs as $lab)
                        <span class="lab-chip static">
                            <span>{{ $lab->title }}</span>
                            <form method="POST" action="{{ route('admin.labs.destroy', [$subject, $lab]) }}" class="inline-form"
                                  onsubmit="return confirm('Удалить «{{ $lab->title }}»? Кнопка исчезнет со страницы записи.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="lab-remove" title="Удалить">&times;</button>
                            </form>
                        </span>
                    @endforeach
                    <form method="POST" action="{{ route('admin.labs.store', $subject) }}" class="inline-form">
                        @csrf
                        <button type="submit" class="lab-chip add">+ Добавить лабораторную</button>
                    </form>
                </div>
            </div>

            {{-- Отдельные действия: перейти к очереди / удалить предмет --}}
            <div class="actions" style="margin-top:12px; border-top:1px solid var(--line); padding-top:12px">
                <a href="{{ route('admin.queue.show', $subject) }}" class="btn sm ghost">
                    Управлять очередью ({{ $subject->queue_entries_count }})
                </a>
                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline-form"
                      onsubmit="return confirm('Удалить предмет вместе со всей очередью?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn sm danger">Удалить предмет</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card">Предметов пока нет. Создайте первый выше.</div>
    @endforelse
@endsection
