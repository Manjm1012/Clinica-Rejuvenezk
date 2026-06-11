<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('specialty', 200)->nullable();
            $table->string('subtitle', 300)->nullable(); // e.g. "Cirujano Plástico Certificado"
            $table->string('photo_path')->nullable();
            $table->longText('bio')->nullable();
            $table->string('university', 200)->nullable();
            $table->string('certifications')->nullable(); // comma separated or JSON
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('doctor_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('type', 50); // education, certification, association, achievement
            $table->string('title', 300);
            $table->string('institution', 200)->nullable();
            $table->year('year')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_credentials');
        Schema::dropIfExists('doctors');
    }
};
