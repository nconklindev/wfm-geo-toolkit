<?php

use App\Models\Group;
use App\Models\KnownPlace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_known_place', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class)->constrained('groups')->cascadeOnDelete();
            $table->foreignIdFor(KnownPlace::class)->constrained('known_places')->cascadeOnDelete();
            // Ensure a known place can only be added to a group once
            $table->unique(['group_id', 'known_place_id']);


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_known_place');
    }
};
