<?php

use App\Models\User;
use App\Models\Answer;

class UpdateAnswerTest extends TestCase {

	/**
	 * For the correct data, updates an existent answer setting a new value for its text
	 * and returns an OK
	 */
	public function testCaseOk()
	{
		$user = User::find(2);
		$beforeAnswer = Answer::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_text' => 'aLazyNewTextForOneOfMyAnswersForTest'
		];

		$response = $this->call('PUT', "api/answers/{$beforeAnswer->id}", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		$afterAnswer = Answer::find(2);

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNotEquals($beforeAnswer->text, $afterAnswer->text);
		$this->assertEquals($afterAnswer->text, $params['answer_text']);

		$this->seed();
	}

	/**
	 * For correct data, but not allowed (not answer author) user returns a KO
	 */
	public function testCaseKoUserIsNotAuthor()
	{
		$user = User::find(4);
		$question = Answer::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_text' => 'aLazyTextAsResponseForAQuestionForTest'
		];

		$response = $this->call('PUT', "api/answers/{$question->id}", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'OnlyAnswerAuthorAllowed'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

}
