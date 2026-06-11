<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Панель старосты: список всех предметов.
     */
    public function index()
    {
        $subjects = Subject::withCount('queueEntries')->with('labs')->orderBy('name')->get();

        return view('admin.subjects', compact('subjects'));
    }

    /**
     * Создать предмет: название и описание.
     * Список лабораторных добавляется отдельными кнопками.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        Subject::create($data);

        return back()->with('success', 'Предмет создан.');
    }

    /**
     * Изменить предмет.
     */
    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $subject->update($data);

        return back()->with('success', 'Предмет обновлён.');
    }

    /**
     * Удалить предмет (вместе с его очередью — каскадно).
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Предмет удалён.');
    }
}
