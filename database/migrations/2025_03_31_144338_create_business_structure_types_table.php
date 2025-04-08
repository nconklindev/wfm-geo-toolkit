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
            $table->integer('hierarchy_order'); // Maps to hierarchyOrder
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_types');
    }
};
