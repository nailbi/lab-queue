{{-- Этот блок обновляется через AJAX каждые несколько секунд (динамический элемент). --}}
@php
    // Токен браузера: по нему помечаем «свою» строку и показываем кнопку выхода.
    $studentToken = session('student_token');
@endphp
@if ($subject->queueEntries->isEmpty())
    <p class="muted">В очереди пока никого нет. Будьте первым!</p>
@else
    <div class="table-scroll">
    <table>
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
                @php $isMine = $studentToken && $entry->student_token === $studentToken; @endphp
                <tr @class(['my-row' => $isMine])>
                    <td><span class="pos">{{ $entry->position }}</span></td>
                    <td>
                        {{ $entry->student_name }}
                        @if ($isMine)<span class="badge mine">Это вы</span>@endif
                    </td>
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
