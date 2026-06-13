<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * Панель старосты: расписание по дням + форма добавления пары.
     */
    public function index()
    {
        $entries = ScheduleEntry::orderBy('day_of_week')
            ->orderBy('time_start')
            ->get()
            ->groupBy('day_of_week');

        return view('admin.schedule', [
            'days'      => ScheduleEntry::DAYS,
            'types'     => ScheduleEntry::TYPES,
            'weekTypes' => ScheduleEntry::WEEK_TYPES,
            'entries'   => $entries,
        ]);
    }

    /**
     * Добавить пару в расписание.
     */
    public function store(Request $request)
    {
        ScheduleEntry::create($this->validated($request));

        return back()->with('success', 'Пара добавлена в расписание.');
    }

    /**
     * Изменить пару.
     */
    public function update(Request $request, ScheduleEntry $entry)
    {
        $entry->update($this->validated($request));

        return back()->with('success', 'Пара обновлена.');
    }

    /**
     * Удалить пару.
     */
    public function destroy(ScheduleEntry $entry)
    {
        $entry->delete();

        return back()->with('success', 'Пара удалена из расписания.');
    }

    /**
     * Правила валидации пары (общие для создания и редактирования).
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'day_of_week' => ['required', 'integer', Rule::in(array_keys(ScheduleEntry::DAYS))],
            'time_start'  => ['required', 'date_format:H:i'],
            'time_end'    => ['required', 'date_format:H:i'],
            'subject'     => ['required', 'string', 'max:255'],
            'room'        => ['nullable', 'string', 'max:255'],
            'teacher'     => ['nullable', 'string', 'max:255'],
            'type'        => ['required', Rule::in(array_keys(ScheduleEntry::TYPES))],
            'week_type'   => ['nullable', Rule::in(array_keys(ScheduleEntry::WEEK_TYPES))],
        ]);
    }
}
