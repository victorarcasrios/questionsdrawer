<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('answers', function($table)
		{
			$table->increments('id');
			$table->integer('question_id');
			$table->integer('author_id')->nullable();
			$table->text('text');
			$table->timestamps();
			$table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('author_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('answers', function($table)
		{
			$table->dropForeign('answers_question_id_foreign');
			$table->dropForeign('answers_author_id_foreign');
		});
		Schema::drop('answers');
	}

}
