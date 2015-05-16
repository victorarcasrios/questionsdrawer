<?php

use App\Models\User;
use App\Models\Group;

class ListGroupMembersTest extends TestCase {

	/**
	 * Retrieve all the members of a group with the given role and status combination
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members/Student/Active/index", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK'), 'members' => [
			["id" => "2", "name" => "victor", "role" => "Student", "status" => "Active"]
		]];
		
		$this->assertSame($expectedResponse, $resposeData);

		$this->seed();
	}

}