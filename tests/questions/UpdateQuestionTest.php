<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Question;

class UpdateQuestionTest extends TestCase {

	/**
	 * With the correct user credentials, update an existent question setting a new text for it
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$beforeQuestion = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => 'anotherLazyTextForMyQuestionForTest'
		];

		$response = $this->call('PUT', "api/questions/{$beforeQuestion->id}", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_OK')];
		$afterQuestion = Question::find(1);

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNotEquals($beforeQuestion->text, $afterQuestion->text);
		$this->assertEquals($afterQuestion->text, $params['question_text']);

		$this->seed();
	}

	/**
	 * With the correct user credentials, update an existent question setting again his current text
	 */
	public function testCaseOkSameText()
	{
		$user = User::find(1);
		$beforeQuestion = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => $beforeQuestion->text
		];

		$response = $this->call('PUT', "api/questions/{$beforeQuestion->id}", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_OK')];
		$afterQuestion = Question::find(1);

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertEquals($afterQuestion->text, $params['question_text']);

		$this->seed();
	}

	/**
	 * With the correct user credentials, fails when trying to update a question and set an existent
	 * value for his text (in this group)
	 */
	public function testCaseKoExistentQuestionTextInGroup()
	{
		$user = User::find(1);
		$question = Question::find(1);
		$anotherQuestion = Question::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => $anotherQuestion->text
		];

		$response = $this->call('PUT', "api/questions/{$question->id}", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_KO'), 'exception' => 'GroupAlreadyHasQuestion'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

	/**
	 * For the correct data, but not valid user credentials, returns a KO
	 */
	public function testCaseKoNotAllowedAuthor()
	{
		$user = User::find(4);
		$question = Question::find(1);
		
		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_text' => 'aLazyTextForANotMineQuestionForTest'
		];

		$response = $this->call('PUT', "api/questions/{$question->id}", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

}
