<?php

use App\Models\User;
use App\Models\Answer;

class DeleteAnswerTest extends TestCase {

	/**
	 * For the correct data, updates an existent answer setting a new value for its text
	 * and returns an OK
	 */
	public function testCaseOk()
	{
		$user = User::find(2);
		$answer = Answer::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/answers/{$answer->id}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNull(Answer::find(2));

		$this->seed();
	}

	/**
	 * For correct data, but not allowed (not answer author) user returns a KO
	 */
	public function testCaseKoUserIsNotAuthor()
	{
		$user = User::find(4);
		$answer = Answer::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/answers/{$answer->id}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'OnlyAnswerAuthorAllowed'];

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNotNull(Answer::find(1));

		$this->seed();
	}

}
