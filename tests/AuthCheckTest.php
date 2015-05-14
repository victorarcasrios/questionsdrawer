<?php

use App\Models\User;

class AuthCheckTest extends TestCase {

	/**
	 * Correct credentials case expect OK
	 */
	public function testOk()
	{
		$user = User::find(1);
		
		$params = [
			'user_id' => 1,
			'csrf_token' => $user->remember_token,
		];
		
		$response = $this->call('POST', 'api/users/check', $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];

		$this->assertEquals($resposeData, $expectedResponse);
	}

	/**
	 * Incorrect csrf_token expects KO response
	 */
	public function testKo()
	{
		$user = User::find(1);
		
		$params = [
			'user_id' => 1,
			'csrf_token' => 0,
		];
		
		$response = $this->call('POST', 'api/users/check', $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_KO'), 'exception' => 'IncorrectData'];

		$this->assertEquals($resposeData, $expectedResponse);
	}

}
