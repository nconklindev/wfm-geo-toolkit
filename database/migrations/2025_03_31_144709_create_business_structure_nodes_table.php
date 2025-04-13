<?php

use App\Models\BusinessStructureType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_structure_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(BusinessStructureType::class)->constrained('business_structure_types')->restrictOnDelete();
            $table->string('path')->index()->nullable();
            $table->json('path_hierarchy');
            $table->nestedSet();
            $table->timestamps();

            $table->index(['parent_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_nodes');
    }
};
