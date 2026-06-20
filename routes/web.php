<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\QueueManageController;
use App\Http\Controllers\Admin\LabController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;

/*
|--------------------------------------------------------------------------
| Публичная часть (для студентов, без авторизации)
|--------------------------------------------------------------------------
*/

Route::get('/', [QueueController::class, 'index'])->name('home');

// «Личный кабинет» после входа = панель старосты.
// Маршрут именован dashboard, потому что на него ссылаются стандартные
// контроллеры авторизации (после входа / подтверждения почты и т.п.).
Route::get('/dashboard', fn () => redirect()->route('admin.subjects.index'))
    ->middleware('auth')
    ->name('dashboard');

// Расписание семестра (для всех, без авторизации).
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');

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

    // Заморозить / открыть запись в очередь по предмету.
    Route::put('/subjects/{subject}/registration', [SubjectController::class, 'toggleRegistration'])->name('subjects.registration');

    // Управление кнопками лабораторных работ предмета.
    Route::post('/subjects/{subject}/labs', [LabController::class, 'store'])->name('labs.store');
    Route::delete('/subjects/{subject}/labs/{lab}', [LabController::class, 'destroy'])->name('labs.destroy');

    // Управление расписанием семестра.
    Route::get('/schedule', [AdminScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [AdminScheduleController::class, 'store'])->name('schedule.store');
    Route::put('/schedule/{entry}', [AdminScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{entry}', [AdminScheduleController::class, 'destroy'])->name('schedule.destroy');

    // Управление очередью предмета.
    Route::get('/subjects/{subject}/queue', [QueueManageController::class, 'show'])->name('queue.show');
    Route::put('/subjects/{subject}/queue/{entry}/status', [QueueManageController::class, 'setStatus'])->name('queue.status');
    Route::put('/subjects/{subject}/queue/{entry}/move', [QueueManageController::class, 'move'])->name('queue.move');
    Route::delete('/subjects/{subject}/queue/{entry}', [QueueManageController::class, 'destroy'])->name('queue.destroy');
});

// Аутентификация старосты (Laravel Breeze/стандартные маршруты подключаются авторизацией).
require __DIR__.'/auth.php';
