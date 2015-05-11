<?php

use App\Models\User;

class GetInfoTest extends TestCase {

	public function testListRoles()
	{
		$user = User::find(2);
		
		$response = $this->call('GET', "api/users/{$user->id}/roles");
		$resposeData = json_decode($response->getContent(), true);

		$expectedResponse = ["status" => env('STATUS_OK'),"roles" => array("Student","Teacher")];
		$this->assertEquals($resposeData, $expectedResponse);
	}

	public function testListStatuses()
	{
		$user = User::find(5);
		
		$response = $this->call('GET', "api/users/{$user->id}/statuses");
		$resposeData = json_decode($response->getContent(), true);

		$expectedResponse = ["status" => env('STATUS_OK'),"statuses" => array("Banned")];
		$this->assertEquals($resposeData, $expectedResponse);
	}	
}
