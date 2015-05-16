<?php

use App\Models\User;
use App\Models\Question;

class GetBestAnswerTest extends TestCase {

	/**
	 * For the correct data, returns the id of the answer marked as the best one for the 
	 * indicated question
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$question = Question::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers/best/get", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK'), 'answer_id' => $question->bestAnswer->id];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

}
