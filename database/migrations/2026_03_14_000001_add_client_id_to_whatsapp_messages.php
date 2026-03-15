<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id');
        });

        // Backfill client_id from conversations
        DB::statement('UPDATE whatsapp_messages m JOIN whatsapp_conversations c ON m.conversation_id = c.id SET m.client_id = c.client_id WHERE m.client_id IS NULL');

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->index(['client_id']);
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
