{{-- Этот блок обновляется через AJAX каждые несколько секунд (динамический элемент). --}}
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
            </tr>
        </thead>
        <tbody>
            @foreach ($subject->queueEntries as $entry)
                <tr>
                    <td><span class="pos">{{ $entry->position }}</span></td>
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
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
@endif
