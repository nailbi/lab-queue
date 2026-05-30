<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица предметов, которые создаёт староста.
     * lab_count — количество лабораторных по предмету (староста может менять).
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Название предмета
            $table->text('description')->nullable(); // Описание (для детальной страницы / "статьи")
            $table->unsignedInteger('lab_count')->default(0); // Кол-во лаб по предмету
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
