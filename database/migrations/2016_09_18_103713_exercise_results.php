<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExerciseResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'exercise_results',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->integer('exercise_id')->unsigned();
                $table->foreign('exercise_id')->references('id')->on('exercises')->onDelete('cascade');
                $table->integer('number_of_good_answers')->default('0');
                $table->integer('number_of_good_answers_today')->default('0');
                $table->timestamp('latest_good_answer')->nullable();
                $table->integer('number_of_bad_answers')->default('0');
                $table->integer('number_of_bad_answers_today')->default('0');
                $table->timestamp('latest_bad_answer')->nullable();
                $table->integer('percent_of_good_answers')->default('0');
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
        Schema::drop('exercise_results');
    }
}
