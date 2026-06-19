@extends('layouts.app')

@section('title', 'Расписание')

@section('content')
    <h1>Расписание</h1>

    @php
        $rangeStartMin = $startHour * 60;
        $bodyHeight    = ($endHour - $startHour) * $pxPerHour;
        $todayEntries  = $byDay->get($todayIso);
        $shortDays     = ['', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        // id предмета очереди по названию пары (или null, если очереди нет).
        $subjectId     = fn ($name) => $subjectIds[mb_strtolower(trim((string) $name))] ?? null;
    @endphp

    <div id="schedule-app">
        <div class="sch-toolbar">
            <div class="sch-seg" role="tablist" aria-label="Вид расписания">
                <button type="button" role="tab" data-view="day">День</button>
                <button type="button" role="tab" data-view="week" aria-selected="true">Неделя</button>
                <button type="button" role="tab" data-view="sem">Семестр</button>
            </div>
        </div>

        {{-- ===== ДЕНЬ — таймлайн только сегодняшнего дня ===== --}}
        <div data-panel="day" class="sch-hide">
            <div class="sch-range"><b>Сегодня</b> · {{ $dayLabel }}</div>
            <div class="sch-scroll">
                <div class="sch-cal">
                    <div class="sch-dh one">
                        <div class="sch-gx"></div>
                        <div class="sch-d tod">
                            <small>{{ $shortDays[$todayIso] ?? 'Сегодня' }}</small>
                            <span class="sch-num">{{ $todayDate->day }}</span>
                        </div>
                    </div>
                    <div class="sch-body one" data-start="{{ $rangeStartMin }}" data-px="{{ $pxPerHour }}"
                         style="height:{{ $bodyHeight }}px; --row:{{ $pxPerHour }}px">
                        <div class="sch-gut">
                            @for($h = $startHour; $h <= $endHour; $h++)
                                <span class="sch-t" style="top:{{ ($h - $startHour) * $pxPerHour }}px">{{ sprintf('%02d:00', $h) }}</span>
                            @endfor
                        </div>
                        <div class="sch-col">
                            @foreach($todayEntries ?? [] as $e)
                                @php
                                    $top = round(($e->startMinutes() - $rangeStartMin) / 60 * $pxPerHour);
                                    $hgt = max(round(($e->endMinutes() - $e->startMinutes()) / 60 * $pxPerHour), 24);
                                    $sid = $subjectId($e->subject);
                                @endphp
                                <a @if($sid) href="{{ route('subjects.show', $sid) }}" @endif class="sch-ev @if($sid) link @endif @if($e->type === 'lab') lab @endif" style="top:{{ $top }}px; height:{{ $hgt }}px">
                                    <div class="sch-et">{{ $e->time_start }}–{{ $e->time_end }}</div>
                                    <div class="sch-es">{{ $e->subject }}</div>
                                    @if($e->room || $e->teacher)
                                        <div class="sch-er">{{ collect([$e->room ? 'ауд. '.$e->room : null, $e->teacher])->filter()->implode(' · ') }}</div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        <div class="sch-now"><span class="sch-nt">--:--</span><span class="sch-nr"></span></div>
                    </div>
                </div>
            </div>
            @if(! $todayEntries || $todayEntries->isEmpty())
                <p class="sch-empty">Сегодня пар нет.</p>
            @endif
        </div>

        {{-- ===== НЕДЕЛЯ — таймлайн текущей недели ===== --}}
        <div data-panel="week">
            <div class="sch-range"><b>Неделя</b> · {{ $weekLabel }}</div>
            <div class="sch-scroll">
                <div class="sch-cal">
                    <div class="sch-dh">
                        <div class="sch-gx"></div>
                        @foreach($weekDays as $wd)
                            <div class="sch-d @if($wd['iso'] === $todayIso) tod @endif">
                                <small>{{ $shortDays[$wd['iso']] }}</small>
                                <span class="sch-num">{{ $wd['date']->day }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="sch-body" data-start="{{ $rangeStartMin }}" data-px="{{ $pxPerHour }}"
                         style="height:{{ $bodyHeight }}px; --row:{{ $pxPerHour }}px">
                        <div class="sch-gut">
                            @for($h = $startHour; $h <= $endHour; $h++)
                                <span class="sch-t" style="top:{{ ($h - $startHour) * $pxPerHour }}px">{{ sprintf('%02d:00', $h) }}</span>
                            @endfor
                        </div>
                        @foreach($weekDays as $wd)
                            <div class="sch-col">
                                @foreach($byDay->get($wd['iso']) ?? [] as $e)
                                    @php
                                        $top = round(($e->startMinutes() - $rangeStartMin) / 60 * $pxPerHour);
                                        $hgt = max(round(($e->endMinutes() - $e->startMinutes()) / 60 * $pxPerHour), 24);
                                        $sid = $subjectId($e->subject);
                                    @endphp
                                    <a @if($sid) href="{{ route('subjects.show', $sid) }}" @endif class="sch-ev @if($sid) link @endif @if($e->type === 'lab') lab @endif" style="top:{{ $top }}px; height:{{ $hgt }}px">
                                        <div class="sch-et">{{ $e->time_start }}–{{ $e->time_end }}</div>
                                        <div class="sch-es">{{ $e->subject }}</div>
                                        @if($e->room)<div class="sch-er">ауд. {{ $e->room }}</div>@endif
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                        <div class="sch-now"><span class="sch-nt">--:--</span><span class="sch-nr"></span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== СЕМЕСТР — календарь месяца (без таймлайна) ===== --}}
        <div data-panel="sem" class="sch-hide">
            <div class="sch-wkh">
                @foreach(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'] as $i => $dn)
                    <span @class(['tod' => ($i + 1) === $todayIso])>{{ $dn }}</span>
                @endforeach
            </div>
            <div class="sch-mlabel">{{ $monthLabel }}</div>
            <div class="sch-scroll">
                <div class="sch-grid">
                    @foreach($monthWeeks as $week)
                        @foreach($week as $date)
                            @php
                                $inMonth     = $date->isSameMonth($monthStart);
                                $dayEntries  = $inMonth ? $byDay->get((int) $date->isoWeekday()) : null;
                                $isToday     = $date->isSameDay($todayDate);
                            @endphp
                            <div class="sch-cell @if(! $inMonth) out @endif">
                                @if($isToday)
                                    <span class="sch-dn todc">{{ $date->day }}</span>
                                @else
                                    <div class="sch-dn">{{ sprintf('%02d', $date->day) }}</div>
                                @endif
                                @foreach($dayEntries ?? [] as $e)
                                    @php $sid = $subjectId($e->subject); @endphp
                                    <a @if($sid) href="{{ route('subjects.show', $sid) }}" @endif class="sch-mev @if($sid) link @endif @if($e->type === 'lab') lab @endif"><b>{{ $e->time_start }}</b> {{ $e->subject }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($byDay->isEmpty())
        <div class="card" style="margin-top:16px">Расписание пока не заполнено. Его ведёт староста.</div>
    @endif

    <script>
        (function () {
            const root = document.getElementById('schedule-app');
            if (! root) return;

            // Переключение вкладок День / Неделя / Семестр.
            const tabs   = root.querySelectorAll('.sch-seg button');
            const panels = root.querySelectorAll('[data-panel]');
            tabs.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    tabs.forEach(function (b) { b.setAttribute('aria-selected', b === btn ? 'true' : 'false'); });
                    panels.forEach(function (p) { p.classList.toggle('sch-hide', p.dataset.panel !== btn.dataset.view); });
                    placeNow();
                });
            });

            // Линия текущего времени на таймлайнах (День и Неделя).
            const bodies = root.querySelectorAll('.sch-body');
            function placeNow() {
                const now  = new Date();
                const mins = now.getHours() * 60 + now.getMinutes();
                const text = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                bodies.forEach(function (body) {
                    const line = body.querySelector('.sch-now');
                    if (! line) return;
                    const start = parseInt(body.dataset.start, 10);
                    const px    = parseInt(body.dataset.px, 10);
                    const max   = body.clientHeight;
                    let top = (mins - start) / 60 * px;
                    if (top < 0) top = 0;
                    if (top > max) top = max;
                    line.style.top = top + 'px';
                    const label = line.querySelector('.sch-nt');
                    if (label) label.textContent = text;
                });
            }
            placeNow();
            setInterval(placeNow, 30000);
        })();
    </script>
@endsection
