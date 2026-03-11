<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('container_id')->nullable();
            $table->string('container_name')->unique();
            $table->string('status')->default('pending'); // pending - running - stopped - error - removed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_databases');
    }
};
