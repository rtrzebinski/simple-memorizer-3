<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LessonsThreshold extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lesson_user', function (Blueprint $table) {
            $table->boolean('bidirectional')->after('lesson_id')->default(false);
            $table->integer('threshold')->after('bidirectional')->default(100);
        });
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('bidirectional');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->boolean('bidirectional')->after('visibility')->default(false);
        });
        Schema::table('lesson_user', function (Blueprint $table) {
            $table->dropColumn('bidirectional');
        });
        Schema::table('lesson_user', function (Blueprint $table) {
            $table->dropColumn('threshold');
        });
    }
}
