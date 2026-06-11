<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\Subject;

class LabController extends Controller
{
    /**
     * Добавить лабораторную: кнопка «Лабораторная №N»
     * получает следующий свободный номер автоматически.
     */
    public function store(Subject $subject)
    {
        $nextNumber = (int) $subject->labs()->max('number') + 1;

        $subject->labs()->create([
            'number' => $nextNumber,
            'title'  => 'Лабораторная №' . $nextNumber,
        ]);

        return back()->with('success', 'Лабораторная №' . $nextNumber . ' добавлена.');
    }

    /**
     * Удалить лабораторную (кнопка исчезает со страницы записи).
     * Уже сделанные записи в очереди не трогаем.
     */
    public function destroy(Subject $subject, Lab $lab)
    {
        abort_unless($lab->subject_id === $subject->id, 404);

        $lab->delete();

        return back()->with('success', $lab->title . ' удалена.');
    }
}
