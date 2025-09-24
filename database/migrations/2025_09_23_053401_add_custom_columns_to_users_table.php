<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'manager', 'admin'])->default('employee');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('dietary_preferences')->nullable();
            $table->string('telegram_chat_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['role', 'company_id', 'dietary_preferences', 'telegram_chat_id']);
        });
    }
};