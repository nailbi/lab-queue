<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'description', 'lab_count', 'registration_open'];

    protected $casts = [
        'registration_open' => 'boolean',
    ];

    /**
     * Значения по умолчанию на уровне модели: новый предмет сразу открыт
     * для записи, даже до сохранения в БД (без обращения к базе).
     */
    protected $attributes = [
        'registration_open' => true,
    ];

    /**
     * Записи в очереди по этому предмету, упорядоченные по позиции.
     */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class)->orderBy('position');
    }

    /**
     * Лабораторные работы предмета (кнопки на странице записи).
     * Список ведёт староста.
     */
    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class)->orderBy('number');
    }
}
