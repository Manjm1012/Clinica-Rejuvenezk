<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropUnique('site_settings_group_key_unique');
            $table->unique(['clinic_id', 'group', 'key'], 'site_settings_clinic_group_key_unique');
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropUnique('service_categories_slug_unique');
            $table->unique(['clinic_id', 'slug'], 'service_categories_clinic_slug_unique');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique('services_slug_unique');
            $table->unique(['clinic_id', 'slug'], 'services_clinic_slug_unique');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique('services_clinic_slug_unique');
            $table->unique('slug', 'services_slug_unique');
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropUnique('service_categories_clinic_slug_unique');
            $table->unique('slug', 'service_categories_slug_unique');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropUnique('site_settings_clinic_group_key_unique');
            $table->unique(['group', 'key'], 'site_settings_group_key_unique');
        });
    }
};
