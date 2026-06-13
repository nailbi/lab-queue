<?php

namespace App\Http\Controllers;

use App\Models\ScheduleEntry;

class ScheduleController extends Controller
{
    /**
     * Публичная страница расписания семестра.
     * Пары сгруппированы по дням недели и отсортированы по времени.
     * Пока староста ничего не добавил — показываем пустые дни.
     */
    public function index()
    {
        $entries = ScheduleEntry::orderBy('day_of_week')
            ->orderBy('time_start')
            ->get()
            ->groupBy('day_of_week');

        return view('schedule', [
            'days'    => ScheduleEntry::DAYS,
            'entries' => $entries,
        ]);
    }
}
