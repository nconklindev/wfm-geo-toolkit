<?php

use App\Models\BusinessStructureNode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_structure_node_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BusinessStructureNode::class)->constrained('business_structure_nodes')->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_structure_node_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_node_user');
    }
};
