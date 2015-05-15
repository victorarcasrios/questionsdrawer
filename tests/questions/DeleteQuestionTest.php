<?php

use App\Models\User;
use App\Models\Question;

class DeleteQuestionTest extends TestCase {

	/**
	 * With the correct user credentials, update an existent question setting a new text for it
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$question = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/questions/{$question->id}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		$question = Question::find(1);

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNull($question);

		$this->seed();
	}

	/**
	 * With the incorrect user credentials, do nothing, just returns a KO
	 */
	public function testCaseKoUserWithoutDeletionPermissions()
	{
		$user = User::find(4);
		$beforeQuestion = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/questions/{$beforeQuestion->id}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission'];
		$afterQuestion = Question::find(1);

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNotNull($afterQuestion);
		$this->assertEquals($beforeQuestion, $afterQuestion);

		$this->seed();
	}

	/**
	 * With the correct user credentials, but an inexistent question returns a KO
	 */
	public function testCaseKoInexistentQuestion()
	{
		$user = User::find(1);
		$questionId = 100;

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/questions/{$questionId}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'QuestionNotFound'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}
}