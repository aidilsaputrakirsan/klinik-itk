<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasienTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_can_register_patient_with_same_nik_as_soft_deleted_patient()
    {
        $this->actingAs($this->user);

        // 1. Register first patient as draft (NIK: 2222222222222222)
        $response = $this->post(route('pasien.store'), [
            'nik' => '2222222222222222',
            'nama' => 'John Doe',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Kebagusan No. 1',
            'status_pasien' => 'umum',
        ]);

        $response->assertRedirect(route('pasien.create'));
        $this->assertDatabaseHas('pasiens', [
            'nik' => '2222222222222222',
            'nama' => 'John Doe',
            'is_draft' => true,
        ]);

        $pasien = Pasien::where('nik', '2222222222222222')->firstOrFail();

        // 2. Activate the patient (move from draft to main)
        $response = $this->post(route('pasien.activate', $pasien->id));
        $response->assertSessionHas('success');
        $this->assertFalse((bool)$pasien->fresh()->is_draft);

        // 3. Delete (soft-delete) the patient
        $response = $this->delete(route('pasien.destroy', $pasien->id));
        $response->assertRedirect(route('pasien.index'));
        $this->assertSoftDeleted('pasiens', [
            'id' => $pasien->id,
        ]);

        // 4. Try to register another patient with the same NIK
        $response = $this->post(route('pasien.store'), [
            'nik' => '2222222222222222',
            'nama' => 'Jane Doe',
            'tanggal_lahir' => '1995-05-05',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Margonda No. 10',
            'status_pasien' => 'umum',
        ]);

        $response->assertRedirect(route('pasien.create'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pasiens', [
            'nik' => '2222222222222222',
            'nama' => 'Jane Doe',
            'is_draft' => true,
        ]);
    }

    public function test_cannot_activate_draft_patient_if_nik_already_active()
    {
        $this->actingAs($this->user);

        // Create an active patient
        $activePasien = Pasien::create([
            'nomor_rm' => 'RM2026070001',
            'nik' => '2222222222222222',
            'nama' => 'Active Patient',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat Active',
            'tipe_pasien' => 'umum',
            'is_draft' => false,
        ]);

        // Create a draft patient with the same NIK
        $draftPasien = Pasien::create([
            'nomor_rm' => 'RM2026070002',
            'nik' => '2222222222222222',
            'nama' => 'Draft Patient',
            'tanggal_lahir' => '1995-01-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Alamat Draft',
            'tipe_pasien' => 'umum',
            'is_draft' => true,
        ]);

        // Try to activate draft patient with the same NIK
        $response = $this->post(route('pasien.activate', $draftPasien->id));
        $response->assertSessionHas('error', 'Pasien dengan NIK tersebut sudah terdaftar di Daftar Pasien Utama.');
        $this->assertTrue((bool)$draftPasien->fresh()->is_draft);
    }
}
