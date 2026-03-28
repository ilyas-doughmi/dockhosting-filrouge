<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('docker_image');
            $table->string('default_file');
            $table->text('default_content');
            $table->integer('default_port');
            $table->string('status')->default('active'); // active - inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
