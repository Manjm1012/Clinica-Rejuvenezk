<?php

use App\Support\MigrationGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Citas vinculadas a pacientes y médicos (las existentes siguen funcionando sin ellos).
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
        });

        // Solicitudes del portal: el paciente pide renovar un tratamiento que ya se hizo.
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('patient_treatment_id')->nullable()->constrained('patient_treatments')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->default('renewal');
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time', 20)->nullable(); // manana | tarde
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending'); // pending | scheduled | cancelled | completed
            $table->text('staff_notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });

        // Auditoría de acceso y cambios sobre datos clínicos.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable()->index();
            $table->string('actor_type', 20); // user | patient | system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name', 200)->nullable();
            $table->string('action', 40);     // viewed | created | updated | signed | issued | annulled | printed
            $table->string('auditable_type', 120);
            $table->unsignedBigInteger('auditable_id');
            $table->json('changes')->nullable(); // solo nombres de campos, nunca valores clínicos
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        MigrationGuard::refuseIfHasData('2026_11_22_000300_create_service_requests_and_audit_logs', [
            'service_requests' => null,
            'audit_logs' => null,
        ]);

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('service_requests');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('doctor_id');
        });
    }
};
