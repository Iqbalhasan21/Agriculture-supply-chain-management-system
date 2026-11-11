<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('not_submitted');
            $table->string('id_front_path')->nullable();
            $table->string('id_back_path')->nullable();
            $table->string('face_path')->nullable();
            $table->longText('nid_encrypted')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('nid_verified_at')->nullable();
            $table->timestamp('face_verified_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });

        DB::table('users')->whereIn('role', ['client', 'farmer'])->orderBy('id')->chunkById(200, function ($users) {
            $now = now();
            foreach ($users as $user) {
                DB::table('user_verifications')->insertOrIgnore([
                    'user_id' => $user->id,
                    'status' => 'not_submitted',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_verifications');
    }
};
