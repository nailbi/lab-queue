<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEntry extends Model
{
    protected $fillable = [
        'subject_id', 'student_name', 'lab_titles',
        'labs_to_pass', 'position', 'status',
    ];

    /**
     * Человекочитаемые подписи статусов (для вывода во вьюхах).
     */
    public const STATUSES = [
        'waiting' => 'Ожидает',
        'present' => 'На сдаче',
        'passed'  => 'Сдал',
        'absent'  => 'Не явился',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Подпись статуса для текущей записи.
     */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Названия лаб в виде массива (хранятся как текст с переносами строк).
     */
    public function labTitlesArray(): array
    {
        return array_values(array_filter(
            preg_split('/\r\n|\r|\n/', (string) $this->lab_titles),
            fn ($line) => trim($line) !== ''
        ));
    }
}
