<?php

use App\Models\User;
use App\Models\Group;

class ApplyOrLeaveGroupTest extends TestCase {

	/**
	 * Return a KO when trying to apply to an already applied group
	 */
	public function testCaseKoAlreadyAppliedGroup()
	{
		$user = User::find(4);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_KO'), 'exception' => 'AlreadyDeniedStudent'];
		
		$this->assertSame($expectedResponse, $resposeData);

		$this->seed();
	}

	/**
	 * Returns an OK when applying to a group
	 */
	public function testCaseOkApplyGroup()
	{
		$user = User::find(4);
		$group = Group::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		
		$membership = \DB::table('members')->where('user_id', '=', $user->id)
							->where('group_id', '=', $group->id)->first();
		$this->assertSame($expectedResponse, $resposeData);
		$this->assertEquals($membership->role, 'Student');
		$this->assertEquals($membership->status, 'Demanded');

		$this->seed();
	}

	/**
	 * Returns an OK when leaving a related group
	 */
	public function testCaseOkLeaveGroup()
	{
		$user = User::find(2);
		$group = Group::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members/leave", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		
		$this->assertSame($expectedResponse, $resposeData);
		$this->assertFalse(\DB::table('members')->where('user_id', '=', $user->id)
							->where('group_id', '=', $group->id)->exists());

		$this->seed();
	}

}
