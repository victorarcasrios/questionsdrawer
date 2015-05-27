<?php

use App\Models\User;

class GetUserMembershipsTest extends TestCase {

	/**
	 * Get all the active memberships of a specific user
	 */
	public function testCaseOkGetActiveOnes()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];
		$expectedResponse = ['status' => env('STATUS_OK')];

		$response = $this->call('POST', 'api/memberships/active', $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK'), 'memberships' => array(
			["id" => "1","name" => "Grupo 1","role" => "Teacher","status" => "Active"],
			["id" => "2","name" => "Grupo 2","role" => "Teacher","status" => "Active"],
			["id" => "3","name" => "Grupo 3","role" => "Teacher","status" => "Active"]
		)];
		
		$this->assertSame($expectedResponse['status'], $resposeData['status']);
		$this->assertSame($expectedResponse['memberships'], $resposeData['memberships'], "\$canonicalize = true");

		$this->seed();
	}

	/**
	 * Get all the memberships of an specific user with the given role and status combination
	 */
	public function testCaseOkGetGroupOnes()
	{
		$user = User::find(4);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];
		$expectedResponse = ['status' => env('STATUS_OK')];

		$response = $this->call('POST', 'api/memberships/Student/Denied', $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK'), 'memberships' => array(
			["id" => "1", "name" => "Grupo 1"]
		)];
		
		$this->assertSame($expectedResponse['status'], $resposeData['status']);
		$this->assertSame($expectedResponse['memberships'], $resposeData['memberships'], "\$canonicalize = true");

		$this->seed();
	}

}
