<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * labs — список лабораторных работ предмета (кнопки на странице записи).
     * Кнопки создаёт и удаляет староста: «Лабораторная №1», «Лабораторная №2» и т.д.
     *
     * student_token — анонимный токен браузера студента, чтобы:
     *  - студент мог записаться в очередь по предмету только один раз;
     *  - студент мог удалить из очереди только свою запись.
     */
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');  // Номер лабораторной (для подписи кнопки)
            $table->string('title');            // Подпись кнопки: «Лабораторная №1»
            $table->timestamps();
        });

        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('student_token', 64)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropColumn('student_token');
        });

        Schema::dropIfExists('labs');
    }
};
