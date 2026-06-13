@extends('layouts.app')

@section('title', 'Расписание семестра')

@section('content')
    <h1>Расписание семестра</h1>
    <p class="sub">Расписание пар по дням недели. Ведёт староста.</p>

    @php
        // Сегодняшний день недели в формате 1=Пн … 7=Вс (для подсветки «Сегодня»).
        $today = (int) now()->isoWeekday();
    @endphp

    @forelse ($days as $num => $dayName)
        @php $dayEntries = $entries->get($num); @endphp

        <div class="card schedule-day @if($num === $today) my-entry @endif">
            <div class="row">
                <strong class="schedule-day-name">
                    {{ $dayName }}
                    @if($num === $today)<span class="badge mine">Сегодня</span>@endif
                </strong>
                <span class="badge">
                    @if($dayEntries)
                        {{ trans_choice(':count пара|:count пары|:count пар', $dayEntries->count(), ['count' => $dayEntries->count()]) }}
                    @else
                        Нет пар
                    @endif
                </span>
            </div>

            @if($dayEntries)
                @foreach($dayEntries as $entry)
                    <div class="schedule-row">
                        <div class="schedule-time">{{ $entry->time_start }}–{{ $entry->time_end }}</div>
                        <div class="schedule-info">
                            <div class="schedule-subject">
                                {{ $entry->subject }}
                                @if($entry->weekTypeLabel())
                                    <span class="badge">{{ $entry->weekTypeLabel() }}</span>
                                @endif
                            </div>
                            @if($entry->room || $entry->teacher)
                                <div class="muted">
                                    {{ collect([$entry->room ? 'ауд. '.$entry->room : null, $entry->teacher])->filter()->implode(' · ') }}
                                </div>
                            @endif
                        </div>
                        <span class="badge @if($entry->type === 'lab') present @endif">{{ $entry->typeLabel() }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    @empty
        <div class="card">Расписание пока не заполнено.</div>
    @endforelse
@endsection
