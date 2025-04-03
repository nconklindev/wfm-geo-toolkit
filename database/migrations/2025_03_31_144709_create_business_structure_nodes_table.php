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
            $table->text('description')->nullable();
            $table->string('structure_hash')->nullable()->index()->after('path_hierarchy');
            $table->foreignIdFor(BusinessStructureType::class)->constrained('business_structure_types')->restrictOnDelete();
//            $table->unsignedBigInteger('parent_id')->nullable(); // Only need these if not using NestedSet
//            $table->unsignedBigInteger('_lft');
//            $table->unsignedBigInteger('_rgt');
            $table->string('path')->index()->nullable();
            $table->json('path_hierarchy')->nullable(); // Maps to orgPathHierarchy from WFM API
            $table->date('start_date')->default('1970-01-01'); // Maps to Effective Date
            $table->date('end_date')->default('9999-12-31'); // Forever
            $table->nestedSet();
            $table->timestamps();

            $table->index(['parent_id', 'structure_hash']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_nodes');
    }
};
