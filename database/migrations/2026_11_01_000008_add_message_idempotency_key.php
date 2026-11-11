<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_messages', function (Blueprint $table) {
            $table->uuid('client_token')->nullable()->after('sender_id');
            $table->unique(['thread_id', 'client_token'], 'message_thread_client_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('message_messages', function (Blueprint $table) {
            $table->dropUnique('message_thread_client_token_unique');
            $table->dropColumn('client_token');
        });
    }
};
