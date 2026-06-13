<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * schedule_entries — пары в расписании семестра, которые ведёт староста.
     *
     * Каждая строка — одна пара в конкретный день недели:
     *  - day_of_week  — день недели (1 = понедельник … 7 = воскресенье);
     *  - time_start / time_end — время пары («09:00», «10:30»);
     *  - subject      — название предмета (просто текст, чтобы расписание
     *                   не зависело от таблицы subjects — преподаватель пары
     *                   по физкультуре может и не быть отдельным «предметом»);
     *  - room         — аудитория (необязательно);
     *  - teacher      — преподаватель (необязательно);
     *  - type         — тип занятия: lecture | practice | lab;
     *  - week_type    — чётность недели: null = каждую неделю,
     *                   numerator = числитель, denominator = знаменатель.
     */
    public function up(): void
    {
        Schema::create('schedule_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week');     // 1..7
            $table->string('time_start', 5);                // «09:00»
            $table->string('time_end', 5);                  // «10:30»
            $table->string('subject');                      // Название предмета
            $table->string('room')->nullable();             // Аудитория
            $table->string('teacher')->nullable();          // Преподаватель
            $table->string('type')->default('lecture');     // lecture | practice | lab
            $table->string('week_type')->nullable();        // null | numerator | denominator
            $table->timestamps();

            // Сортировка внутри дня — по времени начала.
            $table->index(['day_of_week', 'time_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_entries');
    }
};
