<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\QueueManageController;

/*
|--------------------------------------------------------------------------
| Публичная часть (для студентов, без авторизации)
|--------------------------------------------------------------------------
*/

Route::get('/', [QueueController::class, 'index'])->name('home');

Route::get('/subjects/{subject}', [QueueController::class, 'show'])->name('subjects.show');

// Динамический элемент: AJAX-обновление списка очереди.
Route::get('/subjects/{subject}/queue', [QueueController::class, 'queue'])->name('subjects.queue');

// Студент записывается / выходит из очереди.
Route::post('/subjects/{subject}/join', [QueueController::class, 'store'])->name('subjects.join');
Route::delete('/subjects/{subject}/leave/{entry}', [QueueController::class, 'leave'])->name('subjects.leave');

/*
|--------------------------------------------------------------------------
| Админ-часть старосты (требует входа по паролю)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Управление предметами.
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    // Управление очередью предмета.
    Route::get('/subjects/{subject}/queue', [QueueManageController::class, 'show'])->name('queue.show');
    Route::put('/subjects/{subject}/queue/{entry}/status', [QueueManageController::class, 'setStatus'])->name('queue.status');
    Route::put('/subjects/{subject}/queue/{entry}/move', [QueueManageController::class, 'move'])->name('queue.move');
    Route::delete('/subjects/{subject}/queue/{entry}', [QueueManageController::class, 'destroy'])->name('queue.destroy');
});

// Аутентификация старосты (Laravel Breeze/стандартные маршруты подключаются авторизацией).
require __DIR__.'/auth.php';
