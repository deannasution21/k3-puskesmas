<?php

namespace Tests\Feature\Auth;

use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_puskesmas_can_login_with_correct_credentials(): void
    {
        $puskesmas = Puskesmas::factory()->create();
        User::factory()->create([
            'username' => 'kampungbali',
            'password' => Hash::make('rahasia123'),
            'role' => 'puskesmas',
            'puskesmas_id' => $puskesmas->id,
        ]);

        $response = $this->withHeader('Referer', 'http://localhost:5173')
            ->postJson('/api/login', [
                'username' => 'kampungbali',
                'password' => 'rahasia123',
            ]);

        $response->assertOk()->assertJson([
            'username' => 'kampungbali',
            'role' => 'puskesmas',
            'puskesmas' => ['id' => $puskesmas->id, 'nama' => $puskesmas->nama],
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'username' => 'kampungbali',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'kampungbali',
            'password' => 'salah',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('username');
    }

    public function test_login_fails_with_unknown_username(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'tidak_ada',
            'password' => 'apapun123',
        ]);

        $response->assertStatus(422);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->actingAsDinas();

        $this->getJson('/api/me')->assertOk()->assertJson([
            'id' => $user->id,
            'username' => $user->username,
            'role' => 'dinas',
        ]);
    }

    public function test_login_then_logout_invalidates_session(): void
    {
        User::factory()->create([
            'username' => 'admindinas',
            'password' => Hash::make('dinas123'),
            'role' => 'dinas',
            'puskesmas_id' => null,
        ]);

        $this->withHeader('Referer', 'http://localhost:5173')
            ->postJson('/api/login', ['username' => 'admindinas', 'password' => 'dinas123'])
            ->assertOk();

        $this->withHeader('Referer', 'http://localhost:5173')->getJson('/api/me')->assertOk();

        $this->withHeader('Referer', 'http://localhost:5173')
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->assertGuest('web');
    }
}
