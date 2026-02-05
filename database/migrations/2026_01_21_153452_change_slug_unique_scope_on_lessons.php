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
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique('lessons_slug_unique');
            $table->unique(['course_id', 'slug'], 'lessons_course_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {

            $table->dropUnique('lessons_course_slug_unique');
            $table->unique('slug', 'lessons_slug_unique');
        });
    }
};
