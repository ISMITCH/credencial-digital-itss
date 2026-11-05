<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('credential_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('registered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('registered_at');
            $table->string('registration_method', 20)->default('qr');
            $table->string('status', 20)->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['event_id', 'student_id'],
                'event_student_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};