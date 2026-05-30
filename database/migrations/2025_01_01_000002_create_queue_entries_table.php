<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запись студента в очередь на предмет.
     * Студент сам вводит имя, названия лаб и их количество.
     * position — позиция в очереди (староста может регулировать).
     * status — состояние явки: waiting / present / passed / absent.
     */
    public function up(): void
    {
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('student_name');             // Имя студента
            $table->text('lab_titles');                 // Названия лаб (через перенос строки)
            $table->unsignedInteger('labs_to_pass');    // Сколько лаб хочет защитить
            $table->unsignedInteger('position');        // Позиция в очереди
            $table->enum('status', ['waiting', 'present', 'passed', 'absent'])
                  ->default('waiting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};
