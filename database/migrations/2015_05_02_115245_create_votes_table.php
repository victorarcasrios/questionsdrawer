<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('votes', function($table)
		{
			$table->integer('author_id')->unsigned();
			$table->integer('answer_id')->unsigned();
			$table->char('type', 8);
			$table->timestamps();
			$table->primary(['author_id', 'answer_id']);
			$table->foreign('author_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('answer_id')->references('id')->on('answers')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('type')->references('type')->on('vote_types')->onDelete('no action')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('votes', function($table)
		{
			$table->dropForeign('votes_author_id_foreign');
			$table->dropForeign('votes_answer_id_foreign');
			$table->dropForeign('votes_type_foreign');
		});
		Schema::drop('votes');
	}

}
