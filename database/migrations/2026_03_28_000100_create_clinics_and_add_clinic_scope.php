<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('domain', 255)->nullable()->unique();
            $table->string('timezone', 80)->default('America/Bogota');
            $table->string('locale', 10)->default('es');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('clinics')->insert([
            'name' => 'Clinica Rejuvenezk',
            'slug' => 'clinica-rejuvenezk',
            'domain' => null,
            'timezone' => 'America/Bogota',
            'locale' => 'es',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_id')->nullable()->index()->after('id');
            $table->boolean('is_super_admin')->default(false)->after('password');
        });

        $tables = [
            'site_settings',
            'service_categories',
            'services',
            'doctors',
            'doctor_credentials',
            'testimonials',
            'gallery_items',
            'technologies',
            'stats',
            'faqs',
            'leads',
            'appointments',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('clinic_id')->nullable()->index()->after('id');
            });
        }

        foreach (array_merge(['users'], $tables) as $tableName) {
            DB::table($tableName)->update(['clinic_id' => 1]);
        }
    }

    public function down(): void
    {
        $tables = [
            'site_settings',
            'service_categories',
            'services',
            'doctors',
            'doctor_credentials',
            'testimonials',
            'gallery_items',
            'technologies',
            'stats',
            'faqs',
            'leads',
            'appointments',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('clinic_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
            $table->dropColumn('clinic_id');
        });

        Schema::dropIfExists('clinics');
    }
};
