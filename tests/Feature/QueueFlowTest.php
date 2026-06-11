<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\QueueEntry;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueFlowTest extends TestCase
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

    public function test_student_can_join_queue_by_choosing_labs(): void
    {
        $subject = $this->makeSubjectWithLabs();
        $labIds = $subject->labs->take(2)->pluck('id')->all();

        $response = $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван Иванов',
            'labs'         => $labIds,
        ]);

        $response->assertRedirect(route('subjects.show', $subject));

        $entry = $subject->queueEntries()->first();
        $this->assertNotNull($entry);
        $this->assertSame('Иван Иванов', $entry->student_name);
        $this->assertSame(2, $entry->labs_to_pass);
        $this->assertSame(["Лабораторная №1", "Лабораторная №2"], $entry->labTitlesArray());
        $this->assertNotEmpty($entry->student_token);
    }

    public function test_student_cannot_join_same_queue_twice(): void
    {
        $subject = $this->makeSubjectWithLabs();
        $labIds = [$subject->labs->first()->id];

        $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван',
            'labs'         => $labIds,
        ]);

        $response = $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван',
            'labs'         => $labIds,
        ]);

        $response->assertSessionHasErrors('student_name');
        $this->assertSame(1, $subject->queueEntries()->count());
    }

    public function test_student_cannot_join_with_lab_from_another_subject(): void
    {
        $subject = $this->makeSubjectWithLabs();
        $other   = Subject::create(['name' => 'Физика']);
        $foreign = $other->labs()->create(['number' => 1, 'title' => 'Лабораторная №1']);

        $response = $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван',
            'labs'         => [$foreign->id],
        ]);

        $response->assertSessionHasErrors('labs.0');
        $this->assertSame(0, $subject->queueEntries()->count());
    }

    public function test_student_can_leave_own_entry_and_queue_is_renumbered(): void
    {
        $subject = $this->makeSubjectWithLabs();

        // Чужая запись впереди.
        $subject->queueEntries()->create([
            'student_name' => 'Пётр', 'lab_titles' => 'Лабораторная №1',
            'labs_to_pass' => 1, 'position' => 1, 'status' => 'waiting',
            'student_token' => 'someone-else',
        ]);

        $this->post(route('subjects.join', $subject), [
            'student_name' => 'Иван',
            'labs'         => [$subject->labs->first()->id],
        ]);

        $mine = $subject->queueEntries()->where('student_name', 'Иван')->first();
        $this->assertSame(2, $mine->position);

        $response = $this->delete(route('subjects.leave', [$subject, $mine]));
        $response->assertRedirect(route('subjects.show', $subject));

        $this->assertNull(QueueEntry::find($mine->id));
        $this->assertSame(1, $subject->queueEntries()->where('student_name', 'Пётр')->value('position'));
    }

    public function test_student_cannot_delete_someone_elses_entry(): void
    {
        $subject = $this->makeSubjectWithLabs();

        $foreign = $subject->queueEntries()->create([
            'student_name' => 'Пётр', 'lab_titles' => 'Лабораторная №1',
            'labs_to_pass' => 1, 'position' => 1, 'status' => 'waiting',
            'student_token' => 'someone-else',
        ]);

        // Инициализируем свою сессию (другой токен).
        $this->get(route('subjects.show', $subject));

        $response = $this->delete(route('subjects.leave', [$subject, $foreign]));

        $response->assertForbidden();
        $this->assertNotNull(QueueEntry::find($foreign->id));
    }

    public function test_admin_can_add_and_delete_lab_buttons(): void
    {
        $admin   = User::create([
            'name' => 'Староста', 'email' => 'admin@test.ru', 'password' => bcrypt('password'),
        ]);
        $subject = Subject::create(['name' => 'Программирование']);

        $this->actingAs($admin)
            ->post(route('admin.labs.store', $subject))
            ->assertRedirect();

        $lab = $subject->labs()->first();
        $this->assertSame('Лабораторная №1', $lab->title);

        $this->actingAs($admin)
            ->post(route('admin.labs.store', $subject));
        $this->assertSame('Лабораторная №2', $subject->labs()->where('number', 2)->value('title'));

        $this->actingAs($admin)
            ->delete(route('admin.labs.destroy', [$subject, $lab]))
            ->assertRedirect();

        $this->assertNull(Lab::find($lab->id));
    }

    public function test_guest_cannot_manage_lab_buttons(): void
    {
        $subject = Subject::create(['name' => 'Программирование']);

        $this->post(route('admin.labs.store', $subject))->assertRedirect(route('login'));
        $this->assertSame(0, $subject->labs()->count());
    }
}
