<?php

use App\Models\User;
use App\Models\Group;

class DeleteGroupTest extends TestCase {

	/**
	 * For the correct data, delete the group with the given (at the URI) groupId
	 
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/groups/{$group->id}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		
		$expectedResponse = ['status' => env('STATUS_OK')];
		$this->assertEquals($responseData, $expectedResponse);
		
		$this->assertEquals($responseData, $expectedResponse);
		$group = Group::find(1);
		$this->assertNull($group);

		$this->seed();
	}

	/**
	 * For the incorrect credentials (user with no deletion permissions), return a KO status
	 
	 */
	public function testCaseKo()
	{
		$user = User::find(2);
		$beforeGroup = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
		];

		$response = $this->call('POST', "api/groups/{$beforeGroup->id}/delete", $params);
		$responseData = json_decode($response->getContent(), true);
		
		$expectedResponse = ['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission'];
		$this->assertEquals($responseData, $expectedResponse);
		
		$this->assertEquals($responseData, $expectedResponse);
		$afterGroup = Group::find(1);
		$this->assertNotNull($afterGroup);
		$this->assertEquals($beforeGroup, $afterGroup);

		$this->seed();
	}

}
