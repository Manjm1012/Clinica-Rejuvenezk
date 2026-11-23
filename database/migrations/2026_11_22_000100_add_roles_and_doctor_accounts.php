<?php

use App\Support\MigrationGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roles del panel y cuentas de médicos.
 * Los usuarios existentes quedan como "admin" para no perder acceso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('is_super_admin')->index();
        });

        Schema::table('doctors', function (Blueprint $table) {
            // Cuenta con la que el médico entra al panel /medico (un usuario = un médico).
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            // Registro profesional (RETHUS / tarjeta profesional): obligatorio en la fórmula médica.
            $table->string('professional_registration', 60)->nullable();
            $table->string('signature_path')->nullable();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->string('document_type', 10)->nullable()->after('name');
            $table->string('document_number', 30)->nullable()->after('document_type');
            $table->string('gender', 20)->nullable()->after('birth_date');
            // Datos clínicos: se guardan cifrados (cast "encrypted"), por eso son TEXT.
            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('current_medications')->nullable();
        });
    }

    public function down(): void
    {
        // Las columnas clínicas del paciente no se eliminan si tienen datos.
        MigrationGuard::refuseIfHasData('2026_11_22_000100_add_roles_and_doctor_accounts', [
            'patients' => fn ($query) => $query->where(fn ($q) => $q->whereNotNull('allergies')
                ->orWhereNotNull('medical_history')
                ->orWhereNotNull('current_medications')),
        ]);

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'document_number', 'gender', 'allergies', 'medical_history', 'current_medications']);
        });

        // Orden explícito (llave foránea -> índice único -> columnas): así funciona en MariaDB/MySQL y en SQLite.
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'professional_registration', 'signature_path']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
