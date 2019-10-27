<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RegisterNumberOfGoodAndBadAnswersToday extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercise_results', function (Blueprint $table) {
            $table->integer('number_of_good_answers_today')->after('number_of_good_answers')->default('0');
            $table->integer('number_of_bad_answers_today')->after('number_of_bad_answers')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exercise_results', function (Blueprint $table) {
            $table->dropColumn('number_of_good_answers_today');
        });
        Schema::table('exercise_results', function (Blueprint $table) {
            $table->dropColumn('number_of_bad_answers_today');
        });
    }
}
