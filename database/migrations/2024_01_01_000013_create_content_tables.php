<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name', 150);
            $table->string('patient_avatar_path')->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->default(5); // 1-5 stars
            $table->string('source', 50)->default('google'); // google, facebook, direct
            $table->string('source_url')->nullable();
            $table->date('reviewed_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('before_image_path')->nullable();
            $table->string('after_image_path')->nullable();
            $table->string('image_path')->nullable(); // single image (non before/after)
            $table->string('type', 30)->default('before_after'); // before_after, single, video
            $table->string('video_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('testimonials');
    }
};
