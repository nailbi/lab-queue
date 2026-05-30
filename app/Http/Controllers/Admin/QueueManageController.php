<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\QueueController;
use App\Models\Subject;
use App\Models\QueueEntry;
use Illuminate\Http\Request;

class QueueManageController extends Controller
{
    /**
     * Страница управления очередью конкретного предмета.
     */
    public function show(Subject $subject)
    {
        $subject->load('queueEntries');

        return view('admin.queue', compact('subject'));
    }

    /**
     * Отметить явку/статус студента: пришёл, сдал, не явился, ожидает.
     */
    public function setStatus(Request $request, Subject $subject, QueueEntry $entry)
    {
        abort_unless($entry->subject_id === $subject->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:waiting,present,passed,absent'],
        ]);

        $entry->update(['status' => $data['status']]);

        return back()->with('success', 'Статус обновлён.');
    }

    /**
     * Подвинуть запись вверх или вниз в очереди.
     * direction: up | down
     */
    public function move(Request $request, Subject $subject, QueueEntry $entry)
    {
        abort_unless($entry->subject_id === $subject->id, 404);

        $direction = $request->input('direction');

        // Сосед, с которым меняемся местами.
        $neighbor = $direction === 'up'
            ? $subject->queueEntries()->where('position', '<', $entry->position)
                      ->orderByDesc('position')->first()
            : $subject->queueEntries()->where('position', '>', $entry->position)
                      ->orderBy('position')->first();

        if ($neighbor) {
            // Меняем позиции местами.
            $tmp = $entry->position;
            $entry->update(['position' => $neighbor->position]);
            $neighbor->update(['position' => $tmp]);
        }

        return back();
    }

    /**
     * Удалить запись из очереди и пересчитать позиции.
     */
    public function destroy(Subject $subject, QueueEntry $entry)
    {
        abort_unless($entry->subject_id === $subject->id, 404);

        $entry->delete();
        QueueController::renumber($subject);

        return back()->with('success', 'Запись удалена из очереди.');
    }
}
