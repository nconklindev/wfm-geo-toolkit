<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('known_places', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 11, 7);
            $table->integer('radius');
            $table->boolean('is_active')->default(true);
            $table->json('locations')->nullable();
            $table->json('wifi_networks')->nullable();
            $table->integer('accuracy');
            $table->json('validation_order');
            $table->string('color')->nullable();
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Allow for different users to have the same Known Place names
            // Keep them unique by User
            $table->unique(['name', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('known_places');
    }
};
