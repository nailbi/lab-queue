@extends('layouts.app')

@section('title', 'Очередь — ' . $subject->name)

@section('content')
    <p class="muted"><a href="{{ route('admin.subjects.index') }}">← К предметам</a></p>
    <h1>Очередь: {{ $subject->name }}</h1>
    <p class="sub">Регулируйте порядок, отмечайте явку и удаляйте записи.</p>

    @if ($subject->queueEntries->isEmpty())
        <div class="card">В очереди пока никого нет.</div>
    @else
        <div class="card">
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Студент</th>
                        <th>Лабораторные</th>
                        <th>Кол-во</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subject->queueEntries as $entry)
                        <tr>
                            <td>
                                <span class="pos">{{ $entry->position }}</span>
                                <div class="actions" style="margin-top:6px">
                                    {{-- Подвинуть вверх --}}
                                    <form method="POST" action="{{ route('admin.queue.move', [$subject, $entry]) }}" class="inline-form">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="direction" value="up">
                                        <button class="btn sm ghost" title="Вверх">↑</button>
                                    </form>
                                    {{-- Подвинуть вниз --}}
                                    <form method="POST" action="{{ route('admin.queue.move', [$subject, $entry]) }}" class="inline-form">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="direction" value="down">
                                        <button class="btn sm ghost" title="Вниз">↓</button>
                                    </form>
                                </div>
                            </td>
                            <td>{{ $entry->student_name }}</td>
                            <td>
                                @foreach ($entry->labTitlesArray() as $title)
                                    <div>{{ $title }}</div>
                                @endforeach
                            </td>
                            <td>{{ $entry->labs_to_pass }}</td>
                            <td>
                                <span class="badge
                                    @if($entry->status==='passed') ok
                                    @elseif($entry->status==='present') present
                                    @elseif($entry->status==='absent') absent
                                    @endif">
                                    {{ $entry->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    {{-- Отметить явку / статус --}}
                                    <form method="POST" action="{{ route('admin.queue.status', [$subject, $entry]) }}" class="inline-form">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="present">
                                        <button class="btn sm warn">Пришёл</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.queue.status', [$subject, $entry]) }}" class="inline-form">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="passed">
                                        <button class="btn sm ok">Сдал</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.queue.status', [$subject, $entry]) }}" class="inline-form">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="absent">
                                        <button class="btn sm ghost">Не явился</button>
                                    </form>
                                    {{-- Удалить из очереди --}}
                                    <form method="POST" action="{{ route('admin.queue.destroy', [$subject, $entry]) }}" class="inline-form"
                                          onsubmit="return confirm('Удалить запись из очереди?')">
                                        @csrf @method('DELETE')
                                        <button class="btn sm danger">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
@endsection
