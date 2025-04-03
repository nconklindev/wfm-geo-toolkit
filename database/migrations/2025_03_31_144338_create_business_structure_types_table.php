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
            $table->string('hex_color')->nullable()->default('#193cb8');
            $table->integer('hierarchy_order'); // Maps to hierarchyOrder
            $table->date('start_date')->default('1970-01-01');
            $table->date('end_date')->default('9999-12-31');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_structure_types');
    }
};
