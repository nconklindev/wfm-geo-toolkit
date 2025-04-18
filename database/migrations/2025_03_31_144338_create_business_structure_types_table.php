<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_structure_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->integer('order');
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['name', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_types');
    }
};
