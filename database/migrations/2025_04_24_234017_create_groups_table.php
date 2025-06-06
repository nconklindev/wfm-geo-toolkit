<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('known_places', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('user_id')->constrained('groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('known_places', function (Blueprint $table) {
            $table->dropForeign('group_id');
            $table->dropColumn('group_id');
        });

        Schema::dropIfExists('groups');
    }
};
