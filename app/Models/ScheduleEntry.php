<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleEntry extends Model
{
    protected $fillable = [
        'day_of_week', 'time_start', 'time_end',
        'subject', 'room', 'teacher', 'type', 'week_type',
    ];

    /** Дни недели для подписей (1 = понедельник). */
    public const DAYS = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
    ];

    /** Типы занятий: ключ → подпись на «штампе». */
    public const TYPES = [
        'lecture'  => 'Лекция',
        'practice' => 'Практика',
        'lab'      => 'Лаб',
    ];

    /** Чётность недели: ключ → подпись. null означает «каждую неделю». */
    public const WEEK_TYPES = [
        'numerator'   => 'Числитель',
        'denominator' => 'Знаменатель',
    ];

    /** Человекочитаемая подпись типа занятия. */
    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /** Человекочитаемая подпись чётности недели (или пусто). */
    public function weekTypeLabel(): string
    {
        return self::WEEK_TYPES[$this->week_type] ?? '';
    }
}
