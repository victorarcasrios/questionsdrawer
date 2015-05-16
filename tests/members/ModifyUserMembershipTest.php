<?php

use App\Models\User;
use App\Models\Group;

class ModifyUserMembershipTest extends TestCase {

	/**
	 * As group creator, gets an OK after setting the status of a member to Active
	 */
	public function testCaseOkActive()
	{
		$user = User::find(1);
		$group = Group::find(1);
		$target = User::find(3);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'member_id' => $target->id
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members/actives", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		
		$this->assertSame($expectedResponse, $resposeData);
		$this->assertEquals(DB::table('members')->where('user_id', '=', $target->id)
								->where('group_id', '=', $group->id)->first()->status, 'Active');

		$this->seed();
	}

	/**
	 * As group creator, gets an OK after setting the status of a member to Banned
	 */
	public function testCaseOkBan()
	{
		$user = User::find(1);
		$group = Group::find(1);
		$target = User::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'member_id' => $target->id
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members/bans", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		
		$this->assertSame($expectedResponse, $resposeData);
		$this->assertEquals(DB::table('members')->where('user_id', '=', $target->id)
								->where('group_id', '=', $group->id)->first()->status, 'Banned');

		$this->seed();
	}

	/**
	 * As group creator, gets an OK after setting the status of a member to Denied
	 */
	public function testCaseOkDeny()
	{
		$user = User::find(1);
		$group = Group::find(1);
		$target = User::find(3);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'member_id' => $target->id
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members/denials", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		
		$this->assertSame($expectedResponse, $resposeData);
		$this->assertEquals(DB::table('members')->where('user_id', '=', $target->id)
								->where('group_id', '=', $group->id)->first()->status, 'Denied');

		$this->seed();
	}

	/**
	 * As group creator, gets an OK after changing the role o a Student member to Teacher
	 * (and his role to Active) 
	 */
	public function testCaseOkMakeTeacher()
	{
		$user = User::find(1);
		$group = Group::find(1);
		$target = User::find(5);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'member_id' => $target->id
		];

		$response = $this->call('POST', "api/groups/{$group->id}/members/teachers", $params);
		$resposeData = json_decode($response->getContent(), true);
		$expectedResponse = ['status' => env('STATUS_OK')];
		
		$target = DB::table('members')->where('user_id', '=', $target->id)
								->where('group_id', '=', $group->id)->first();

		$this->assertSame($expectedResponse, $resposeData);
		$this->assertEquals($target->role, 'Teacher');
		$this->assertEquals($target->status, 'Active');

		$this->seed();
	}

}
