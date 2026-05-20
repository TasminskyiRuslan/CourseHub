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
        Schema::table('courses', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->softDeletes();
            $table->dropForeign(['author_id']);
            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->dropSoftDeletes();
            $table->dropForeign(['author_id']);
            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
