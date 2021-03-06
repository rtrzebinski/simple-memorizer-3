<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Lessons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'lessons',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('owner_id')->unsigned();
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('name');
                $table->enum('visibility', ['public', 'private']);
                $table->integer('exercises_count')->default(0);
                $table->integer('subscribers_count')->default(0);
                $table->integer('child_lessons_count')->default(0);
                $table->timestamps();
            }
        );

        Schema::create(
            'lesson_user',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->integer('lesson_id')->unsigned();
                $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
                $table->boolean('bidirectional')->default(false);
                $table->boolean('favourite')->default(false);
                $table->integer('percent_of_good_answers')->default('0');
                $table->unique(['user_id', 'lesson_id']);
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lesson_user');
        Schema::drop('lessons');
    }
}
