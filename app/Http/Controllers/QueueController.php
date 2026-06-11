<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QueueController extends Controller
{
    /**
     * Главная страница: список предметов со счётчиками очереди.
     */
    public function index()
    {
        $subjects = Subject::withCount(['queueEntries', 'labs'])->orderBy('name')->get();

        return view('home', compact('subjects'));
    }

    /**
     * Детальная страница предмета: описание + текущая очередь + форма записи.
     */
    public function show(Request $request, Subject $subject)
    {
        $subject->load(['queueEntries', 'labs']);

        // Запись текущего студента (если он уже в очереди) — по токену браузера.
        $myEntry = $subject->queueEntries
            ->firstWhere('student_token', self::studentToken($request));

        return view('subject', compact('subject', 'myEntry'));
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
     * Вводит имя и выбирает лабораторные кнопками (список лаб ведёт староста).
     * Записаться по одному предмету можно только один раз.
     */
    public function store(Request $request, Subject $subject)
    {
        $token = self::studentToken($request);

        // Повторная запись с этого браузера запрещена, пока есть своя запись в очереди.
        if ($subject->queueEntries()->where('student_token', $token)->exists()) {
            return redirect()
                ->route('subjects.show', $subject)
                ->withErrors(['student_name' => 'Вы уже записаны в очередь по этому предмету. Сначала выйдите из неё.']);
        }

        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'labs'         => ['required', 'array', 'min:1'],
            'labs.*'       => [
                'integer',
                Rule::exists('labs', 'id')->where('subject_id', $subject->id),
            ],
        ], [
            'labs.required' => 'Выберите хотя бы одну лабораторную работу.',
        ]);

        // Названия выбранных лаб — из списка, который ведёт староста.
        $labs = $subject->labs()->whereIn('id', $data['labs'])->get();

        // Позиция = текущая длина очереди + 1 (встаёт в конец).
        $nextPosition = (int) $subject->queueEntries()->max('position') + 1;

        $subject->queueEntries()->create([
            'student_name'  => $data['student_name'],
            'lab_titles'    => $labs->pluck('title')->implode("\n"),
            'labs_to_pass'  => $labs->count(),
            'position'      => $nextPosition,
            'status'        => 'waiting',
            'student_token' => $token,
        ]);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Вы записаны в очередь!');
    }

    /**
     * Студент может сам выйти из очереди — но только удалить свою запись.
     */
    public function leave(Request $request, Subject $subject, QueueEntry $entry)
    {
        abort_unless($entry->subject_id === $subject->id, 404);

        // Удалить можно только запись, созданную из этого же браузера.
        abort_unless($entry->student_token === self::studentToken($request), 403);

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

    /**
     * Анонимный токен студента: хранится в сессии браузера,
     * привязывает записи очереди к конкретному студенту без регистрации.
     */
    public static function studentToken(Request $request): string
    {
        $token = $request->session()->get('student_token');

        if (! $token) {
            $token = Str::random(40);
            $request->session()->put('student_token', $token);
        }

        return $token;
    }
}
