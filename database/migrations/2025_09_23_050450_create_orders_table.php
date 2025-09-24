<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('planned_for');
            $table->time('planned_time')->default('13:00');
            $table->enum('status', ['planned', 'ordered', 'delivered', 'received', 'cancelled'])->default('planned');
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['planned_for', 'company_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};