<?php

namespace App\Http\Controllers;

use App\Models\ScheduleEntry;
use Illuminate\Support\Carbon;

class ScheduleController extends Controller
{
    /** Месяцы в именительном падеже — для заголовка («Июнь»). */
    private const MONTHS_NOM = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    ];

    /** Месяцы в родительном падеже — для дат («18 июня»). */
    private const MONTHS_GEN = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
    ];

    /** Дни недели полностью (1 = понедельник) — для подписи «Сегодня». */
    private const WEEKDAYS_FULL = [
        1 => 'понедельник', 2 => 'вторник', 3 => 'среда', 4 => 'четверг',
        5 => 'пятница', 6 => 'суббота', 7 => 'воскресенье',
    ];

    /**
     * Публичная страница расписания: три вида в одном месте —
     * День и Неделя как таймлайн (с линией текущего времени),
     * Семестр как календарь месяца. Чётность недели не учитываем:
     * показываем все пары как обычные недели.
     */
    public function index()
    {
        $entries = ScheduleEntry::orderBy('day_of_week')
            ->orderBy('time_start')
            ->get();

        $byDay = $entries->groupBy('day_of_week');

        $now      = now();
        $todayIso = (int) $now->isoWeekday();

        // --- Диапазон таймлайна по реальным парам (с запасом, fallback 9–17). ---
        $startMin = $entries->min(fn (ScheduleEntry $e) => $e->startMinutes());
        $endMin   = $entries->max(fn (ScheduleEntry $e) => $e->endMinutes());

        $startHour = $startMin !== null ? intdiv((int) $startMin, 60) : 9;
        $endHour   = $endMin !== null ? (int) ceil($endMin / 60) : 17;
        if ($endHour - $startHour < 2) {
            $endHour = $startHour + 2;
        }

        // --- Текущая неделя: понедельник…суббота с датами. ---
        $weekStart = $now->copy()->startOfWeek();
        $weekDays  = [];
        for ($i = 0; $i < 6; $i++) {
            $weekDays[] = [
                'date' => $weekStart->copy()->addDays($i),
                'iso'  => $i + 1,
            ];
        }

        // --- Календарь текущего месяца: колонки Пн…Сб (воскресенья пропускаем). ---
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();
        $cursor     = $monthStart->copy()->startOfWeek();
        $monthWeeks = [];
        while ($cursor <= $monthEnd) {
            $row = [];
            for ($i = 0; $i < 6; $i++) {
                $row[] = $cursor->copy()->addDays($i);
            }
            $monthWeeks[] = $row;
            $cursor->addWeek();
        }

        return view('schedule', [
            'byDay'      => $byDay,
            'todayIso'   => $todayIso,
            'todayDate'  => $now->copy(),
            'weekDays'   => $weekDays,
            'monthWeeks' => $monthWeeks,
            'monthStart' => $monthStart,
            'startHour'  => $startHour,
            'endHour'    => $endHour,
            'pxPerHour'  => 64,
            'dayLabel'   => $this->dayLabel($now),
            'weekLabel'  => $this->weekLabel($weekStart),
            'monthLabel' => self::MONTHS_NOM[(int) $now->month],
        ]);
    }

    /** Подпись дня: «четверг, 18 июня». */
    private function dayLabel(Carbon $day): string
    {
        return self::WEEKDAYS_FULL[(int) $day->isoWeekday()]
            .', '.$day->day.' '.self::MONTHS_GEN[(int) $day->month];
    }

    /** Диапазон недели: «15–20 июня» или «29 июня – 4 июля» на стыке месяцев. */
    private function weekLabel(Carbon $weekStart): string
    {
        $start = $weekStart->copy();
        $end   = $weekStart->copy()->addDays(5);

        if ($start->month === $end->month) {
            return $start->day.'–'.$end->day.' '.self::MONTHS_GEN[(int) $end->month];
        }

        return $start->day.' '.self::MONTHS_GEN[(int) $start->month]
            .' – '.$end->day.' '.self::MONTHS_GEN[(int) $end->month];
    }
}
