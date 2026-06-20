<?php

namespace Tests\Feature;

use App\Models\QueueEntry;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты двух нововведений:
 *  №1 — корректная (атомарная) выдача позиций в очереди;
 *  №8 — заморозка / открытие записи в очередь старостой.
 */
class RegistrationFreezeTest extends TestCase
{
    use RefreshDatabase;

    private function makeSubjectWithLabs(int $labs = 3): Subject
    {
        $subject = Subject::create(['name' => 'Программирование']);

        for ($n = 1; $n <= $labs; $n++) {
            $subject->labs()->create(['number' => $n, 'title' => 'Лабораторная №' . $n]);
        }

        return $subject;
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Староста', 'email' => 'admin@test.ru', 'password' => bcrypt('password'),
        ]);
    }

    // ---------------------------------------------------------------------
    //  №1 — выдача позиций
    // ---------------------------------------------------------------------

    public function test_new_subject_has_registration_open_by_default(): void
    {
        $subject = Subject::create(['name' => 'Физика']);

        $this->assertTrue($subject->registration_open);
    }

    public function test_sequential_students_get_unique_consecutive_positions(): void
    {
        $subject = $this->makeSubjectWithLabs();
        $labId   = $subject->labs->first()->id;

        // Три студента из разных «браузеров» (каждый запрос — своя сессия).
        foreach (['Иван', 'Пётр', 'Сидор'] as $name) {
            $this->post(route('subjects.join', $subject), [
                'student_name' => $name,
                'labs'         => [$labId],
            ]);
            $this->flushSession();
        }

        $positions = $subject->queueEntries()->orderBy('position')->pluck('position')->all();

        // Позиции уникальны и идут по порядку 1,2,3 — инвариант, который
        // сохраняет транзакция с блокировкой при выдаче позиции.
        $this->assertSame([1, 2, 3], $positions);
        $this->assertSame(3, $subject->queueEntries()->count());
    }

    // ---------------------------------------------------------------------
    //  №8 — заморозка записи
    // ---------------------------------------------------------------------

    public function test_student_cannot_join_when_registration_closed(): void
    {
        $subject = $this->makeSubjectWithLabs();
        $subject->update(['registration_open' => false]);

        $response = $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван',
            'labs'         => [$subject->labs->first()->id],
        ]);

        $response->assertSessionHasErrors('student_name');
        $this->assertSame(0, $subject->queueEntries()->count());
    }

    public function test_admin_can_freeze_and_reopen_registration(): void
    {
        $admin   = $this->makeAdmin();
        $subject = $this->makeSubjectWithLabs();

        // Заморозить.
        $this->actingAs($admin)
            ->put(route('admin.subjects.registration', $subject))
            ->assertRedirect();
        $this->assertFalse($subject->fresh()->registration_open);

        // Открыть обратно.
        $this->actingAs($admin)
            ->put(route('admin.subjects.registration', $subject))
            ->assertRedirect();
        $this->assertTrue($subject->fresh()->registration_open);
    }

    public function test_guest_cannot_toggle_registration(): void
    {
        $subject = $this->makeSubjectWithLabs();

        $this->put(route('admin.subjects.registration', $subject))
            ->assertRedirect(route('login'));

        $this->assertTrue($subject->fresh()->registration_open);
    }

    public function test_existing_student_can_still_leave_when_frozen(): void
    {
        $subject = $this->makeSubjectWithLabs();

        // Студент уже в очереди.
        $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван',
            'labs'         => [$subject->labs->first()->id],
        ]);
        $entry = $subject->queueEntries()->first();

        // Староста замораживает запись.
        $subject->update(['registration_open' => false]);

        // Уже записавшийся может выйти из очереди (та же сессия/токен).
        $this->delete(route('subjects.leave', [$subject, $entry]))
            ->assertRedirect(route('subjects.show', $subject));

        $this->assertNull(QueueEntry::find($entry->id));
    }
}
