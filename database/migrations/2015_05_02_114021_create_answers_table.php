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
			$table->integer('question_id')->unsigned();
			$table->integer('author_id')->unsigned()->nullable();
			$table->text('text');
			$table->timestamps();
			$table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('author_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
		});

		Schema::table('questions', function($table)
		{
			$table->foreign('best_answer_id')->references('id')->on('answers')->onDelete('set null')->onUpdate('cascade');
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
		Schema::table('questions', function($table)
		{
			$table->dropForeign('questions_best_answer_id_foreign');
		});
		Schema::drop('answers');
	}

}
