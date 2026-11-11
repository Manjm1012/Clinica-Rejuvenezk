<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portal del paciente: pacientes, historial de tratamientos e intervalo de renovación por servicio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            // NOT NULL para que los únicos por clínica funcionen (en MySQL varios NULL no chocan).
            $table->unsignedBigInteger('clinic_id')->index();
            $table->string('name', 200);
            $table->string('email', 200)->nullable();
            $table->string('phone', 30); // solo dígitos, normalizado en el modelo
            $table->date('birth_date')->nullable();
            $table->string('password')->nullable(); // pacientes cargados por la clínica no tienen contraseña
            $table->boolean('marketing_consent')->default(false);
            $table->dateTime('marketing_consent_at')->nullable();
            $table->dateTime('data_policy_accepted_at')->nullable();
            $table->string('data_policy_version', 20)->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['clinic_id', 'email'], 'patients_clinic_email_unique');
            $table->unique(['clinic_id', 'phone'], 'patients_clinic_phone_unique');
        });

        Schema::create('patient_treatments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->date('performed_at');
            $table->text('notes')->nullable();          // visibles para el paciente
            $table->text('internal_notes')->nullable(); // SOLO administración
            $table->date('next_due_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'performed_at'], 'patient_treatments_patient_performed_index');
            $table->index(['clinic_id', 'next_due_at'], 'patient_treatments_clinic_due_index');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('renewal_interval_days')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_treatments');
        Schema::dropIfExists('patients');

        if (Schema::hasColumn('services', 'renewal_interval_days')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('renewal_interval_days');
            });
        }
    }
};
