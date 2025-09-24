<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->integer('calories')->nullable();
            $table->decimal('protein', 5, 1)->nullable();
            $table->decimal('fat', 5, 1)->nullable();
            $table->decimal('carbs', 5, 1)->nullable();
            $table->decimal('price', 8, 2);
            $table->json('allergens')->nullable();
            $table->string('restaurant')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_global')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dishes');
    }
};