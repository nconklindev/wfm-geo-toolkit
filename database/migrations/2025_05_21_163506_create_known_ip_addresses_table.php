<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('known_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('start');
            $table->string('end');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('known_ip_addresses');
    }
};
