<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('members', function($table)
		{
			$table->integer('group_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->char('role', 8);
			$table->char('status', 8);
			$table->timestamps();
			$table->primary('group_id', 'user_id');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('role')->references('name')->on('roles')->onDelete('restrict')->onUpdate('cascade');
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
			$table->dropForeign('members_group_id_foreign');
			$table->dropForeign('members_user_id_foreign');
			$table->dropForeign('members_name_foreign');
		});
		Schema::drop('members');
	}

}
