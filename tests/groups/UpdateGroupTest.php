<?php

use App\Models\User;
use App\Models\Group;

class UpdateGroupTest extends TestCase {

	/**
	 * For the correct data, edit an existent group name
	 
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$beforeGroup = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'group_name' => 'aLazyGroupNameForTest'
		];

		$response = $this->call('PUT', 'api/groups/1', $params);
		$responseData = json_decode($response->getContent(), true);
		
		$expectedResponse = ['status' => env('STATUS_OK')];
		$this->assertEquals($responseData, $expectedResponse);
		$afterGroup = Group::find(1);
		$this->assertNotEquals($beforeGroup->name, $afterGroup->name);
		$this->assertEquals($afterGroup->name, $params['group_name']);

		$this->seed();
	}

}
