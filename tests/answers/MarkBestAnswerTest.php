<?php

use App\Models\User;
use App\Models\Question;

class MarkBestAnswerTest extends TestCase {

	/**
	 * For the correct data, marks an answers as the best one of a question
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$question = Question::find(1);
		$beforeBestAnswer = $question->bestAnswer;

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_id' => 1
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers/best/set", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		$afterBestAnswer = Question::find(1)->bestAnswer;

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNotEquals($beforeBestAnswer, $afterBestAnswer);
		$this->assertEquals($afterBestAnswer->id, $params['answer_id']);

		$this->seed();
	}

	/**
	 * For correct data, but not allowed (not question author nor group creator author) 
	 * user returns a KO
	 */
	public function testCaseKoUserIsNotAuthorNorCreator()
	{
		$user = User::find(2);
		$question = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_id' => 2
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers/best/set", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_KO'), 'exception' => 'OnlyAuthorOrCreatorCanSetIt'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

}
