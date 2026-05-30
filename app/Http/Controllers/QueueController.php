<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\QueueEntry;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Главная страница: список предметов со счётчиками очереди.
     */
    public function index()
    {
        $subjects = Subject::withCount('queueEntries')->orderBy('name')->get();

        return view('home', compact('subjects'));
    }

    /**
     * Детальная страница предмета: описание + текущая очередь + форма записи.
     */
    public function show(Subject $subject)
    {
        $subject->load('queueEntries');

        return view('subject', compact('subject'));
    }

    /**
     * Динамический элемент: отдаёт только список очереди (для AJAX-автообновления).
     */
    public function queue(Subject $subject)
    {
        $subject->load('queueEntries');

        return view('partials.queue_list', compact('subject'));
    }

    /**
     * Студент записывается в очередь.
     * Сам вводит имя, названия лаб и их количество.
     */
    public function store(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'lab_titles'   => ['required', 'string', 'max:2000'],
            'labs_to_pass' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        // Позиция = текущая длина очереди + 1 (встаёт в конец).
        $nextPosition = (int) $subject->queueEntries()->max('position') + 1;

        $subject->queueEntries()->create([
            'student_name' => $data['student_name'],
            'lab_titles'   => $data['lab_titles'],
            'labs_to_pass' => $data['labs_to_pass'],
            'position'     => $nextPosition,
            'status'       => 'waiting',
        ]);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Вы записаны в очередь!');
    }

    /**
     * Студент может сам выйти из очереди.
     */
    public function leave(Subject $subject, QueueEntry $entry)
    {
        abort_unless($entry->subject_id === $subject->id, 404);

        $entry->delete();
        $this->renumber($subject);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Вы вышли из очереди.');
    }

    /**
     * Пересчёт позиций по порядку (1, 2, 3 ...) после изменений.
     */
    public static function renumber(Subject $subject): void
    {
        $position = 1;
        foreach ($subject->queueEntries()->orderBy('position')->get() as $entry) {
            $entry->update(['position' => $position++]);
        }
    }
}
