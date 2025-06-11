<?php

use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_structure_node_known_place', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BusinessStructureNode::class)->constrained('business_structure_nodes')->cascadeOnDelete();
            $table->foreignIdFor(KnownPlace::class)->constrained('known_places')->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->string('path')->index()->nullable();
            $table->json('path_hierarchy')->nullable();
            $table->timestamps();

            $table->unique(['business_structure_node_id', 'known_place_id'], 'node_place_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_node_known_place');
    }
};
