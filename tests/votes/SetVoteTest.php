<?php

use App\Models\User;
use App\Models\Answer;

class SetVoteTest extends TestCase {

	/**
	 * For the correct data, creates a new vote from the user to an specific answer
	 */
	public function testCaseOkCreateNewVote()
	{
		$user = User::find(1);
		$answer = Answer::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'vote' => 'Positive'
		];

		$response = $this->call('POST', "api/answers/{$answer->id}/votes/set", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		$vote = $user->votes()->where('answer_id', '=', $answer->id)->first();

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertEquals($vote->type, $params['vote']);

		$this->seed();
	}

	/**
	 * For the correct data, updates an existent vote of the user to an specific answer
	 */
	public function testCaseOkUpdateExistentVote()
	{
		$user = User::find(1);
		$answer = Answer::find(2);
		$beforeVote = $user->votes()->where('answer_id', '=', $answer->id)->first();

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'vote' => 'Negative'
		];

		$response = $this->call('POST', "api/answers/{$answer->id}/votes/set", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		$afterVote = $user->votes()->where('answer_id', '=', $answer->id)->first();

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertNotEquals($beforeVote->type, $afterVote->type);
		$this->assertEquals($afterVote->type, $params['vote']);

		$this->seed();
	}

}
