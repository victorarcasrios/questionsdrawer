<?php

use App\Models\User;
use App\Models\Question;

class CreateAnswerTest extends TestCase {

	/**
	 * For the correct data, creates a new answer for the given user to the indicated question
	 * and returns an OK
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$question = Question::find(3);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_text' => 'aLazyTextAsResponseForAQuestionForTest'
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers", $params);
		$responseData = json_decode($response->getContent(), true);
		$lastQuestionAnswer =  $question->answers()->orderBy('created_at', 'DESC')->first();
		$expectedResponse = ['status' => env('STATUS_OK')];

		$this->assertEquals($responseData, $expectedResponse);
		$this->assertEquals($lastQuestionAnswer->text, $params['answer_text']);

		$this->seed();
	}

	/**
	 * For proper data, but an already answered question by the given user returns a KO
	 */
	public function testCaseKoUserHasAlreadyAnswered()
	{
		$user = User::find(1);
		$question = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_text' => 'aLazyTextAsResponseForAQuestionForTest'
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_KO'), 'exception' => 'UserHasAlreadyAnswered'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

	/**
	 * For correct data, but not allowed (not member) user returns a KO
	 */
	public function testCaseKoNotMemberAuthor()
	{
		$user = User::find(4);
		$question = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'answer_text' => 'aLazyTextAsResponseForAQuestionForTest'
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'NotMemberUsersNotAllowed'];

		$this->assertEquals($responseData, $expectedResponse);

		$this->seed();
	}

}
