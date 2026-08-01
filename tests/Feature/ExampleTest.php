<?php

namespace Tests\Feature;

use App\Models\DataPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_root_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_screen_returns_a_successful_response(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Iniciar sesion');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_must_change_temporary_password(): void
    {
        DataPolicy::query()->updateOrCreate(
            ['version' => 'test'],
            ['published_at' => now(), 'is_active' => true, 'content' => 'Test policy'],
        );

        User::query()->updateOrCreate(
            ['email' => 'feature@example.test'],
            [
                'name' => 'Feature',
                'password' => Hash::make('TempPass!2026'),
                'email_verified_at' => now(),
                'is_active' => true,
                'must_change_password' => true,
            ],
        );

        $response = $this->post(route('login.store'), [
            'email' => 'feature@example.test',
            'password' => 'TempPass!2026',
            'accept_policy' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('password.change.edit'));
    }

    public function test_authenticated_dashboard_returns_a_successful_response(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'dashboard@example.test'],
            [
                'name' => 'Dashboard',
                'password' => Hash::make('TempPass!2026'),
                'email_verified_at' => now(),
                'is_active' => true,
                'must_change_password' => false,
            ],
        );

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Mapa funcional inicial');
    }
}
