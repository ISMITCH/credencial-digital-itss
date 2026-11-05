<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('all_careers')
                ->default(true)
                ->after('status');

            $table->boolean('all_semesters')
                ->default(true)
                ->after('all_careers');
        });

        Schema::create('career_event', function (Blueprint $table) {
            $table->id();

            $table->foreignId('career_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['career_id', 'event_id']);
        });

        Schema::create('event_semesters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('semester');

            $table->timestamps();

            $table->unique(['event_id', 'semester']);
        });

        /*
         * Conserva la carrera que ya tenían registrados
         * los eventos anteriores.
         */
        $events = DB::table('events')
            ->whereNotNull('career_id')
            ->get();

        foreach ($events as $event) {
            DB::table('career_event')->insertOrIgnore([
                'career_id' => $event->career_id,
                'event_id' => $event->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('events')
                ->where('id', $event->id)
                ->update([
                    'all_careers' => false,
                ]);
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('career_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('career_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });

        $relationships = DB::table('career_event')
            ->orderBy('event_id')
            ->get();

        foreach ($relationships as $relationship) {
            DB::table('events')
                ->where('id', $relationship->event_id)
                ->whereNull('career_id')
                ->update([
                    'career_id' => $relationship->career_id,
                ]);
        }

        Schema::dropIfExists('event_semesters');
        Schema::dropIfExists('career_event');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'all_careers',
                'all_semesters',
            ]);
        });
    }
};