<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_staroste_can_authenticate_with_correct_code(): void
    {
        // Вход в этом приложении — по 4-значному коду старосты,
        // а не по email/паролю. Должна существовать учётная запись.
        config(['auth.admin_code' => '4321']);
        User::factory()->create();

        $response = $this->post('/login', ['code' => '4321']);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.subjects.index', absolute: false));
    }

    public function test_staroste_can_not_authenticate_with_wrong_code(): void
    {
        config(['auth.admin_code' => '4321']);
        User::factory()->create();

        $this->post('/login', ['code' => '0000']);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
