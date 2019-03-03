<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonAggregate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lesson_aggregate', function (Blueprint $table) {
            $table->unsignedInteger('parent_lesson_id');
            $table->foreign('parent_lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            $table->unsignedInteger('child_lesson_id');
            $table->foreign('child_lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            $table->unique(['parent_lesson_id', 'child_lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_aggregate');
    }
}
