<?php

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;

class ListQuestionAnswersAnswerTest extends TestCase {

	/**
	 * Lists all the answers of an specific question
	 */
	public function testCaseOk()
	{
		$this->seed();

		$user = User::find(1);
		$question = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers/list", $params);
		$responseData = json_decode($response->getContent(), true);
		$answers = [Answer::find(1), Answer::find(2)];
		$expectedResponse = ['status' => env('STATUS_OK'), 'answers' => array(
			[
		        "author_name" => "adri",
		        "id" => "1",
		        "text" => "Respuesta creador",
		        "created_at" => $answers[0]->created_at,
		        "updated_at" => $answers[0]->updated_at
		    ],
		    [
		        "author_name" => "victor",
		        "id" => "2",
		        "text" => "Respuesta miembro ordinario",
		        "created_at" => $answers[1]->created_at,
		        "updated_at" => $answers[1]->updated_at
		    ]
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['answers'], $responseData['answers']);
	}

	public function testCaseKo()
	{
		$this->seed();

		$user = User::find(4);
		$question = Question::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/questions/{$question->id}/answers/list", $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'NotMemberUsersNotAllowed'];

		$this->assertEquals($responseData, $expectedResponse);
	}

}