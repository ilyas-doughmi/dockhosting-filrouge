<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('technology_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('directory_path');
            $table->string('url');
            $table->string('status')->default('pending'); // pending - online - stopped - error
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
