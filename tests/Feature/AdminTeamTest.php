<?php

namespace Tests\Feature;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\DoctorResource;
use App\Filament\Resources\UserResource;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CallsLivewireOverHttp;
use Tests\TestCase;

/**
 * Administración del equipo: usuarios con rol, vínculo médico-perfil y páginas nuevas del admin.
 */
class AdminTeamTest extends TestCase
{
    use CallsLivewireOverHttp, RefreshDatabase;

    private Clinic $clinic;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::query()->orderBy('id')->firstOrFail();
        $this->admin = User::factory()->create(['clinic_id' => $this->clinic->id, 'role' => User::ROLE_ADMIN]);
    }

    private function doctorProfile(string $name = 'Dra. Ruiz'): Doctor
    {
        $doctor = new Doctor(['name' => $name, 'is_active' => true]);
        $doctor->clinic_id = $this->clinic->id;
        $doctor->save();

        return $doctor;
    }

    public function test_admin_crea_un_medico_y_lo_vincula_a_su_perfil(): void
    {
        $doctor = $this->doctorProfile();
        $this->actingAs($this->admin);

        $this->livewireCall(UserResource::getUrl('create'), 'create-user', [
            'data.name' => 'Dra. Ruiz',
            'data.email' => 'ruiz@clinica.test',
            'data.password' => 'una-clave-segura',
            'data.role' => User::ROLE_DOCTOR,
            'data.doctor_profile_id' => (string) $doctor->id,
        ], 'create')->assertOk();

        $user = User::query()->where('email', 'ruiz@clinica.test')->firstOrFail();
        $this->assertSame(User::ROLE_DOCTOR, $user->role);
        $this->assertSame($this->clinic->id, $user->clinic_id);
        $this->assertFalse($user->is_super_admin);
        $this->assertSame($user->id, $doctor->fresh()->user_id);
    }

    public function test_paginas_nuevas_del_admin_responden(): void
    {
        $doctor = $this->doctorProfile();
        $this->actingAs($this->admin);

        foreach ([
            UserResource::getUrl('index'),
            UserResource::getUrl('create'),
            AppointmentResource::getUrl('create'),
            DoctorResource::getUrl('edit', ['record' => $doctor]),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

}
