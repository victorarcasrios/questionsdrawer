<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('questions', function($table)
		{
			$table->increments('id');
			$table->integer('author_id')->nullable();
			$table->integer('best_answer_id')->nullable();
			$table->integer('group_id');
			$table->text('text');
			$table->timestamps();
			$table->foreign('author_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('questions', function($table)
		{
			$table->dropForeign('questions_author_id_foreign');
			$table->dropForeign('questions_group_id_foreign');
		});
		Schema::drop('questions');
	}

}
