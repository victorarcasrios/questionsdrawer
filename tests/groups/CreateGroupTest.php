<?php

use App\Models\User;

class CreateGroupTest extends TestCase {

	/**
	 * For the correct data, creates a new group for the given user and returns its id
	 */
	public function testCaseOk()
	{
		$user = User::find(4);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'group_name' => 'aLazyGroupNameForTest'
		];

		$response = $this->call('POST', 'api/groups', $params);
		$responseData = json_decode($response->getContent(), true);
		$lastUserGroup =  $user->createdGroups()->orderBy('created_at', 'DESC')->first();
		$expectedResponse = [ 'status' => env('STATUS_OK'), 'group_id' => $lastUserGroup->id];

		$this->assertEquals($responseData, $expectedResponse);
		$this->seed();
	}

	/**
	 * For the correct data of a user that has reached the limit of groups for user, returns a 
	 * GroupsLimitReached exception
	 */
	public function testKoGroupsLimitReached()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'group_name' => 'aLazyGroupNameForTest'
		];

		$response = $this->call('POST', 'api/groups', $params);
		$responseData = json_decode($response->getContent(), true);
		$expectedResponse = [ 'status' => env('STATUS_KO'), 'exception' => 'GroupsLimitReached'];

		$this->assertSame($responseData, $expectedResponse);
	}

}
