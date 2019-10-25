<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecordAnswersTimestamps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercise_results', function (Blueprint $table) {
            $table->timestamp('latest_good_answer')->after('number_of_good_answers')->nullable();
            $table->timestamp('latest_bad_answer')->after('number_of_bad_answers')->nullable();
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
            $table->dropColumn('latest_good_answer');
        });
        Schema::table('exercise_results', function (Blueprint $table) {
            $table->dropColumn('latest_bad_answer');
        });
    }
}
