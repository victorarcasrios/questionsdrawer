<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Group;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Vote;
use App\Models\Member;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		$this->call('VoteTypeTableSeeder');
		$this->call('RoleTableSeeder');
		$this->call('MemberStatusTableSeeder');

		$this->call('UserTableSeeder');
		$this->call('GroupTableSeeder');
		$this->call('QuestionTableSeeder');
		$this->call('AnswerTableSeeder');
		$this->call('VoteTableSeeder');
		$this->call('MemberTableSeeder');

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}

class VoteTypeTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('vote_types')->truncate();
		DB::table('vote_types')->insert(array(
			['type' => 'Positive'],
			['type' => 'Negative'],
		));
	}
}

class RoleTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('roles')->truncate();
		DB::table('roles')->insert(array(
			['name' => 'Teacher'],
			['name' => 'Student'],
		));
	}
}

class MemberStatusTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('member_statuses')->truncate();
		DB::table('member_statuses')->insert(array(
			['name' => 'Active'],
			['name' => 'Demanded'],
			['name' => 'Denied'],
			['name' => 'Banned']
		));
	}
}

class UserTableSeeder extends Seeder
{

	private function getUsers()
	{
		return array(
			[
				'name' => 'adri',
				'email' => 'adri@gmail.com',
				'password' => Hash::make('adri')
			],
			[
				'name' => 'victor',
				'email' => 'victor@gmail.com',
				'password' => Hash::make('victor')
			],
			[
				'name' => 'waldo',
				'email' => 'waldo@gmail.com',
				'password' => Hash::make('waldo')
			],
			[
				'name' => 'miguel',
				'email' => 'miguel@gmail.com',
				'password' => Hash::make('miguel')
			],
			[
				'name' => 'borja',
				'email' => 'borja@gmail.com',
				'password' => Hash::make('borja')
			]
		);
	}	

	public function run()
	{
		DB::table('users')->truncate();

		$users = $this->getUsers();
		foreach($users as $user)
			User::create($user);
	}
}

class GroupTableSeeder extends Seeder
{

	public function run()
	{
		DB::table('groups')->truncate();

		for($userId = 1; $userId <= 3; $userId++){
			for($i = 1; $i <= 3; $i++)
				Group::create(['name' => "Grupo $i", 'creator_id' => $userId]);
		}
	}
}

class QuestionTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('questions')->truncate();

		for($id = 1; $id <= 3; $id++){
			for($i = 1; $i <= 3; $i++)
				Question::create(['text' => "Pregunta de ejemplo $i", 'author_id' => $id, 'group_id' => $id]);
		}
	}
}

class AnswerTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('answers')->truncate();

		## Only the two first users had answered questions
		Answer::create(['question_id' => 1, 'author_id' => 1, 'text' => 'Respuesta creador' ]);
		Answer::create(['question_id' => 1, 'author_id' => 2, 'text' => 'Respuesta miembro ordinario']);
		
		$answer = Answer::create(['question_id' => 2, 'author_id' => 2, 'text' => 'Respuesta correcta miembro ordinario']);
		$secondQuestion = Question::find(2);
		$secondQuestion->best_answer_id = $answer->id;
		$secondQuestion->save();
	}
}

class VoteTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('votes')->truncate();

		Vote::create(['author_id' => 1, 'answer_id' => 2, 'type' => 'Positive']);
		Vote::create(['author_id' => 2, 'answer_id' => 2, 'type' => 'Positive']);
		Vote::create(['author_id' => 3, 'answer_id' => 2, 'type' => 'Negative']);
	}
}

class MemberTableSeeder extends Seeder
{
	public function run()
	{
		DB::table('members')->truncate();

		## Teachers
		Member::create(['group_id' => 1, 'user_id' => 1, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 2, 'user_id' => 1, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 3, 'user_id' => 1, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 4, 'user_id' => 2, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 5, 'user_id' => 2, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 6, 'user_id' => 2, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 7, 'user_id' => 3, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 8, 'user_id' => 3, 'role' => 'Teacher', 'status' => 'Active']);
		Member::create(['group_id' => 9, 'user_id' => 3, 'role' => 'Teacher', 'status' => 'Active']);

		## Some students for the first group
		Member::create(['group_id' => 1, 'user_id' => 2, 'role' => 'Student', 'status' => 'Active']);
		Member::create(['group_id' => 1, 'user_id' => 3, 'role' => 'Student', 'status' => 'Demanded']);
		Member::create(['group_id' => 1, 'user_id' => 4, 'role' => 'Student', 'status' => 'Denied']);
		Member::create(['group_id' => 1, 'user_id' => 5, 'role' => 'Student', 'status' => 'Banned']);
	}
}
