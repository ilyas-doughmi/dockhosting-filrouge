<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_database_id')->constrained()->onDelete('cascade');
            $table->string('host');
            $table->integer('port');
            $table->string('database_name');
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_credentials');
    }
};
