{{-- Этот блок обновляется через AJAX каждые несколько секунд (динамический элемент). --}}
@php
    // Токен браузера: по нему помечаем «свою» строку и показываем кнопку выхода.
    $studentToken = session('student_token');
@endphp
@if ($subject->queueEntries->isEmpty())
    {{-- Пустое состояние — лист с line-art штампом. --}}
    <div class="empty-state">
        <svg class="empty-ill" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="14" y="8" width="36" height="48" rx="2"/>
            <path d="M22 22h20M22 32h20M22 42h12"/>
        </svg>
        <p class="muted">В очереди пока никого нет. Будьте первым!</p>
    </div>
@else
    {{-- data-version меняется при любом изменении состава/порядка/статусов — по нему
         JS на странице решает, нужно ли перерисовывать список и анимировать. --}}
    @php
        $version = $subject->queueEntries
            ->map(fn ($e) => $e->id.':'.$e->position.':'.$e->status)
            ->implode('|');
    @endphp
    <div class="table-scroll" data-version="{{ $version }}">
    <table class="queue-table">
        <thead>
            <tr>
                <th>№</th>
                <th>Студент</th>
                <th>Лабораторные</th>
                <th>Кол-во</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subject->queueEntries as $entry)
                @php
                    $isMine = $studentToken && $entry->student_token === $studentToken;
                    $isTurn = $isMine && (int) $entry->position === 1;
                    // Класс статуса для бейджа: wait | present | ok (сдал) | absent.
                    $statusClass = match ($entry->status) {
                        'passed'  => 'ok',
                        'present' => 'present',
                        'absent'  => 'absent',
                        default   => 'wait',
                    };
                @endphp
                <tr data-key="{{ $entry->id }}" @class(['my-row' => $isMine, 'is-turn' => $isTurn])>
                    <td data-label="№"><span class="pos">{{ $entry->position }}</span></td>
                    <td data-label="Студент">
                        {{ $entry->student_name }}
                        @if ($isMine)<span class="badge mine">Это вы</span>@endif
                    </td>
                    <td data-label="Лабораторные">
                        @foreach ($entry->labTitlesArray() as $title)
                            <div>{{ $title }}</div>
                        @endforeach
                    </td>
                    <td data-label="Кол-во">{{ $entry->labs_to_pass }}</td>
                    <td data-label="Статус">
                        <span class="badge {{ $statusClass }}">{{ $entry->statusLabel() }}</span>
                    </td>
                    <td data-label="">
                        @if ($isMine)
                            {{-- Выйти из очереди может только сам студент (своя запись). --}}
                            <form method="POST" action="{{ route('subjects.leave', [$subject, $entry]) }}" class="inline-form"
                                  onsubmit="return confirm('Выйти из очереди? Своё место вы потеряете.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn sm danger">Выйти</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
@endif
