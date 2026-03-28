<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 30)->nullable();
            $table->string('email', 200)->nullable();
            $table->text('message')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('source', 100)->default('website'); // website, whatsapp, instagram, facebook
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('status', 50)->default('new'); // new, contacted, qualified, appointment_set, converted, lost
            $table->string('tayrai_lead_id')->nullable()->index(); // ID del lead en TayrAI CRM
            $table->string('tayrai_contact_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('patient_name', 200);
            $table->string('patient_phone', 30);
            $table->string('patient_email', 200)->nullable();
            $table->dateTime('appointment_at');
            $table->string('status', 50)->default('pending'); // pending, confirmed, cancelled, completed, no_show
            $table->text('notes')->nullable();
            $table->string('tayrai_appointment_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('leads');
    }
};
