<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('credential_number', 30)->unique();
            $table->string('qr_token')->unique();
            $table->date('issued_at');
            $table->date('expires_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};