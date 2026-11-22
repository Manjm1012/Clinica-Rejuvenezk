<?php

use App\Support\MigrationGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historia clínica: consultas (con notas aclaratorias) y fórmulas médicas.
 * Los textos clínicos van cifrados (cast "encrypted"), por eso son TEXT.
 * Ninguna tabla borra en cascada desde el paciente: la historia clínica no se elimina.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->dateTime('consulted_at');
            $table->text('reason')->nullable();      // motivo de consulta
            $table->text('findings')->nullable();    // examen / hallazgos
            $table->text('diagnosis')->nullable();   // impresión diagnóstica
            $table->text('plan')->nullable();        // plan / procedimiento realizado
            $table->string('status', 20)->default('draft'); // draft | signed
            $table->dateTime('signed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'consulted_at']);
        });

        Schema::create('consultation_addenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->nullOnDelete();
            $table->string('code', 40)->nullable()->unique(); // número de la fórmula, se asigna al emitir
            $table->string('status', 20)->default('draft');   // draft | issued | annulled
            $table->text('diagnosis')->nullable();
            $table->text('care_instructions')->nullable();    // indicaciones y cuidados posteriores
            $table->dateTime('issued_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->dateTime('annulled_at')->nullable();
            $table->string('annulled_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'issued_at']);
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->string('medication', 200);           // nombre genérico (DCI) o producto
            $table->string('concentration', 100)->nullable();
            $table->string('dosage_form', 100)->nullable(); // forma farmacéutica: crema, gel, tableta...
            $table->string('route', 100)->nullable();       // vía: tópica, oral...
            $table->string('dose', 100)->nullable();
            $table->string('frequency', 150)->nullable();
            $table->string('duration', 100)->nullable();
            $table->string('quantity', 100)->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        MigrationGuard::refuseIfHasData('2026_11_22_000200_create_clinical_records_tables', [
            'consultations' => null,
            'consultation_addenda' => null,
            'prescriptions' => null,
        ]);

        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('consultation_addenda');
        Schema::dropIfExists('consultations');
    }
};
