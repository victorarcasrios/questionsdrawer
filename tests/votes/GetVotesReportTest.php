<?php

use App\Models\User;
use App\Models\Answer;

class GetVotesReportTest extends TestCase {

	/**
	 * For the correct data, gets a OK and a report of votes of an specific answer
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$answer = Answer::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];

		$response = $this->call('POST', "api/answers/{$answer->id}/votes/report", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK'), 'votes' => [
			'Negative' => '1',
			'Positive' => '2'
		]];

		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($responseData['votes'], $expectedResponse['votes']);
		$this->assertEquals($responseData['votes']['Positive'], $expectedResponse['votes']['Positive']);
		$this->assertEquals($responseData['votes']['Negative'], $expectedResponse['votes']['Negative']);

		$this->seed();
	}

}
