<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 20);
            $table->string('city');
            $table->string('country');
            $table->string('flag', 10)->nullable();
            $table->unsignedInteger('world_rank');
            $table->unsignedTinyInteger('acceptance_rate');
            $table->unsignedTinyInteger('international_rate');
            $table->string('tuition');
            $table->unsignedInteger('tuition_value')->default(0);
            $table->json('requirements');
            $table->string('deadline');
            $table->string('type');
            $table->string('accent', 7)->default('#0d6b52');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
