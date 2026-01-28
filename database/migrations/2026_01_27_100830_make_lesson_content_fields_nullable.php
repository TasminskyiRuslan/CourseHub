<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('online_lessons', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->change();
            $table->dateTime('end_time')->nullable()->change();
            $table->string('meeting_link')->nullable()->change();
        });

        Schema::table('video_lessons', function (Blueprint $table) {
            $table->string('video_url')->nullable()->change();
            $table->string('provider')->nullable()->change();
        });

        Schema::table('offline_lessons', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->change();
            $table->dateTime('end_time')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('room_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_lessons', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable(false)->change();
            $table->dateTime('end_time')->nullable(false)->change();
            $table->string('meeting_link')->nullable(false)->change();
        });

        Schema::table('video_lessons', function (Blueprint $table) {
            $table->string('video_url')->nullable(false)->change();
            $table->string('provider')->nullable(false)->change();
        });

        Schema::table('offline_lessons', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable(false)->change();
            $table->dateTime('end_time')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('room_number')->nullable(false)->change();
        });
    }
};
