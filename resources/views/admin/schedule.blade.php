@extends('layouts.app')

@section('title', 'Панель старосты — Расписание')

@section('content')
    <h1>Расписание</h1>
    <p class="sub">Добавляйте пары в расписание семестра. Чётность недели можно не указывать — тогда пара идёт каждую неделю.</p>

    {{-- Форма добавления пары --}}
    <div class="card">
        <h2 style="margin-top:0">Добавить пару</h2>
        <form method="POST" action="{{ route('admin.schedule.store') }}">
            @csrf

            <label for="day_of_week">День недели</label>
            <select id="day_of_week" name="day_of_week" required>
                @foreach($days as $num => $dayName)
                    <option value="{{ $num }}" @selected(old('day_of_week') == $num)>{{ $dayName }}</option>
                @endforeach
            </select>

            <div class="schedule-form-times">
                <div>
                    <label for="time_start">Начало</label>
                    <input type="time" id="time_start" name="time_start" value="{{ old('time_start', '09:00') }}" required>
                </div>
                <div>
                    <label for="time_end">Конец</label>
                    <input type="time" id="time_end" name="time_end" value="{{ old('time_end', '10:30') }}" required>
                </div>
            </div>

            <label for="subject">Предмет</label>
            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>

            <label for="room">Аудитория (необязательно)</label>
            <input type="text" id="room" name="room" value="{{ old('room') }}">

            <label for="teacher">Преподаватель (необязательно)</label>
            <input type="text" id="teacher" name="teacher" value="{{ old('teacher') }}">

            <label for="type">Тип занятия</label>
            <select id="type" name="type" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected(old('type') == $key)>{{ $label }}</option>
                @endforeach
            </select>

            <label for="week_type">Чётность недели</label>
            <select id="week_type" name="week_type">
                <option value="">Каждую неделю</option>
                @foreach($weekTypes as $key => $label)
                    <option value="{{ $key }}" @selected(old('week_type') == $key)>{{ $label }}</option>
                @endforeach
            </select>

            <div style="margin-top:16px">
                <button type="submit" class="btn">Добавить пару</button>
            </div>
        </form>
    </div>

    {{-- Существующие пары по дням --}}
    @forelse ($days as $num => $dayName)
        @php $dayEntries = $entries->get($num); @endphp
        @if($dayEntries)
            <div class="card">
                <h2 style="margin-top:0">{{ $dayName }}</h2>
                @foreach($dayEntries as $entry)
                    <div class="schedule-row schedule-admin-row">
                        <div class="schedule-time">{{ $entry->time_start }}–{{ $entry->time_end }}</div>
                        <div class="schedule-info">
                            <div class="schedule-subject">
                                {{ $entry->subject }}
                                <span class="badge @if($entry->type === 'lab') present @endif">{{ $entry->typeLabel() }}</span>
                                @if($entry->weekTypeLabel())<span class="badge">{{ $entry->weekTypeLabel() }}</span>@endif
                            </div>
                            @if($entry->room || $entry->teacher)
                                <div class="muted">
                                    {{ collect([$entry->room ? 'ауд. '.$entry->room : null, $entry->teacher])->filter()->implode(' · ') }}
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.schedule.destroy', $entry) }}" class="inline-form"
                              onsubmit="return confirm('Удалить пару «{{ $entry->subject }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn sm danger">Удалить</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    @empty
    @endforelse

    @if($entries->isEmpty())
        <div class="card">Пар пока нет. Добавьте первую через форму выше.</div>
    @endif
@endsection
