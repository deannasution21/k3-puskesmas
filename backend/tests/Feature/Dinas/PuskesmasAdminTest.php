<?php

namespace Tests\Feature\Dinas;

use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PuskesmasAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_dinas_can_create_puskesmas_with_account(): void
    {
        $this->actingAsDinas();

        $response = $this->postJson('/api/admin/puskesmas', [
            'nama' => 'UPT Puskesmas Uji Coba',
            'alamat' => 'Jl. Testing No. 1',
            'username' => 'ujicoba',
            'password' => 'password123',
        ])->assertCreated();

        $this->assertDatabaseHas('puskesmas', ['nama' => 'UPT Puskesmas Uji Coba']);
        $this->assertDatabaseHas('users', [
            'username' => 'ujicoba',
            'role' => 'puskesmas',
            'puskesmas_id' => $response->json('id'),
        ]);
    }

    public function test_username_must_be_unique(): void
    {
        $this->actingAsDinas();
        User::factory()->create(['username' => 'sudahada']);

        $this->postJson('/api/admin/puskesmas', [
            'nama' => 'UPT Puskesmas Baru',
            'username' => 'sudahada',
            'password' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('username');
    }

    public function test_dinas_can_update_puskesmas_and_username(): void
    {
        $this->actingAsDinas();
        $puskesmas = Puskesmas::factory()->create();
        User::factory()->create(['username' => 'lama', 'puskesmas_id' => $puskesmas->id]);

        $this->putJson("/api/admin/puskesmas/{$puskesmas->id}", [
            'nama' => 'Nama Baru',
            'alamat' => 'Alamat Baru',
            'username' => 'baru',
        ])->assertOk();

        $this->assertDatabaseHas('puskesmas', ['id' => $puskesmas->id, 'nama' => 'Nama Baru']);
        $this->assertDatabaseHas('users', ['puskesmas_id' => $puskesmas->id, 'username' => 'baru']);
    }

    public function test_reset_password_updates_hash_correctly(): void
    {
        $this->actingAsDinas();
        $puskesmas = Puskesmas::factory()->create();
        $user = User::factory()->create([
            'username' => 'target',
            'password' => Hash::make('lama12345'),
            'puskesmas_id' => $puskesmas->id,
        ]);

        $this->postJson("/api/admin/puskesmas/{$puskesmas->id}/reset-password", [
            'password' => 'barusekali123',
        ])->assertNoContent();

        $this->assertTrue(Hash::check('barusekali123', $user->fresh()->password));
        $this->assertFalse(Hash::check('lama12345', $user->fresh()->password));
    }

    public function test_deleting_puskesmas_cascades_to_its_user_account(): void
    {
        $this->actingAsDinas();
        $puskesmas = Puskesmas::factory()->create();
        User::factory()->create(['username' => 'akandihapus', 'puskesmas_id' => $puskesmas->id]);

        $this->deleteJson("/api/admin/puskesmas/{$puskesmas->id}")->assertNoContent();

        $this->assertDatabaseMissing('puskesmas', ['id' => $puskesmas->id]);
        $this->assertDatabaseMissing('users', ['username' => 'akandihapus']);
    }

    public function test_puskesmas_role_cannot_access_admin_endpoints(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/admin/puskesmas')->assertStatus(403);
    }
}
