<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberStatusesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_statuses', function($table)
		{
			$table->char('name', 8)->primary();
		});
		Schema::table('members', function($table)
		{
			$table->foreign('status')->references('name')->on('member_statuses')->onDelete('restrict')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('members', function($table)
		{
			$table->dropForeign('members_status_foreign');
		});
		Schema::drop('member_statuses');
	}

}
