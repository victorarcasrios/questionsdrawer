<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Question;

class CreateQuestionTest extends TestCase {

	/**
	 * For the correct data, creates a new question in the given group for the given user and returns its id
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => 'aLazyTextForMyNewQuestionForTest'
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions", $params);
		$responseData = json_decode($response->getContent(), true);
		$lastGroupQuestion =  $group->questions()->orderBy('created_at', 'DESC')->first();
		$expectedResponse = [ 'status' => env('STATUS_OK'), 'id' => $lastGroupQuestion->id];

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertEquals($lastGroupQuestion->text, $params['question_text']);

		$this->seed();
	}

	/**
	 * For the correct data, but an already existent question (text) returns a KO
	 */
	public function testCaseKoExistentTextQuestion()
	{
		$user = User::find(1);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => 'aLazyTextForMyNewQuestionForTest'
		];

		$existentQuestion = Question::create([
			'group_id' => $group->id,
			'author_id' => $user->id,
			'text' => $params['question_text']
		]);

		$response = $this->call('POST', "api/groups/{$group->id}/questions", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_KO'), 'exception' => 'GroupAlreadyHasQuestion'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

	/**
	 * For the correct data, but not valid user credentials, returns a KO
	 */
	public function testCaseKoNotAllowedCreator()
	{
		$user = User::find(4);
		$group = Group::find(1);
		
		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => 'aLazyTextForMyNewQuestionForTest'
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

}
