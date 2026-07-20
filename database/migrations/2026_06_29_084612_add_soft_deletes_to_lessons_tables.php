<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons_tables', function (Blueprint $table) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->softDeletes();
            });

            Schema::table('online_lessons', function (Blueprint $table) {
                $table->softDeletes();
            });

            Schema::table('video_lessons', function (Blueprint $table) {
                $table->softDeletes();
            });

            Schema::table('offline_lessons', function (Blueprint $table) {
                $table->softDeletes();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons_tables', function (Blueprint $table) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });

            Schema::table('online_lessons', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });

            Schema::table('video_lessons', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });

            Schema::table('offline_lessons', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        });
    }
};
