<?php

use App\Models\BusinessStructureType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_structure_type_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BusinessStructureType::class)->constrained('business_structure_types')->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->string('hex_color')->nullable()->default('#193cb8');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['business_structure_type_id', 'user_id']);
            $table->index(['user_id', 'business_structure_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_type_user');
    }
};
