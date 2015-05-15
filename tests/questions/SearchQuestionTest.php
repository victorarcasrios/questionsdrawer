<?php

use App\Models\User;
use App\Models\Group;

class SearchQuestionTest extends TestCase {

	/**
	 * Returns all the questions of a group
	 */
	public function testOkRetrieveAll()
	{
		$this->seed();

		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_status' => env('ALL_QUESTIONS')
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions/searches", $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = ['status' => env('STATUS_OK'), 'questions' => array(
			['id' => 2, 'text' => 'Pregunta de ejemplo 2'],
			['id' => 1, 'text' => 'Pregunta de ejemplo 1'],
			['id' => 3, 'text' => 'Pregunta de ejemplo 3'],
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['questions'], $responseData['questions']);
	}

	/**
	 * Returns all the questions of a group answered by a user
	 */
	public function testOkRetrieveAllAnswered()
	{
		$this->seed();

		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_status' => env('ANSWERED_QUESTIONS')
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions/searches", $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = ['status' => env('STATUS_OK'), 'questions' => array(
			['id' => 2, 'text' => 'Pregunta de ejemplo 2'],
			['id' => 1, 'text' => 'Pregunta de ejemplo 1'],
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['questions'], $responseData['questions']);
	}

	/*
	 * Returns all the questions of a group still not answered by a user
	 */
	public function testOkRetrieveAllNotAnswered()
	{
		$this->seed();

		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_status' => env('NOT_ANSWERED_QUESTIONS')
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions/searches", $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = ['status' => env('STATUS_OK'), 'questions' => array(
			['id' => 3, 'text' => 'Pregunta de ejemplo 3'],
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['questions'], $responseData['questions']);
	}

	/**
	 * Search in all the questions of a group
	 */
	public function testOkSearchInAll()
	{
		$this->seed();

		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_status' => env('ALL_QUESTIONS'),
			'search_string' => '3'
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions/searches", $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = ['status' => env('STATUS_OK'), 'questions' => array(
			['id' => 3, 'text' => 'Pregunta de ejemplo 3'],
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['questions'], $responseData['questions']);
	}

	/**
	 * Search in all the questions of a group answered by a user
	 */
	public function testOkSearchInAllAnswered()
	{
		$this->seed();

		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_status' => env('ANSWERED_QUESTIONS'),
			'search_string' => '2'
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions/searches", $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = ['status' => env('STATUS_OK'), 'questions' => array(
			['id' => 2, 'text' => 'Pregunta de ejemplo 2'],
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['questions'], $responseData['questions']);
	}

	/*
	 * Search in all the questions of a group still not answered by a user
	 */
	public function testOkSearchInAllNotAnswered()
	{
		$this->seed();

		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'question_status' => env('NOT_ANSWERED_QUESTIONS'),
			'search_string' => '3'
		];

		$response = $this->call('POST', "api/groups/{$group->id}/questions/searches", $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = ['status' => env('STATUS_OK'), 'questions' => array(
			['id' => 3, 'text' => 'Pregunta de ejemplo 3'],
		)];

		$this->assertArrayHasKey('status', $responseData);
		$this->assertEquals($responseData['status'], $expectedResponse['status']);
		$this->assertEquals($expectedResponse['questions'], $responseData['questions']);
	}

}