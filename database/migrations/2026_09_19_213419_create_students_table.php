<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('student_number', 20)->unique();
            $table->string('first_name');
            $table->string('paternal_last_name');
            $table->string('maternal_last_name')->nullable();
            $table->string('institutional_email')->nullable()->unique();
            $table->string('password');
            $table->unsignedTinyInteger('semester');
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};