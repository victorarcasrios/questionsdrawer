<?php

use App\Models\User;

class SearchGroupsTest extends TestCase {

	/**
	 * Searches by a string within his own ones 
	 */
	public function testOkSearchingTextInOwnOnes()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'role_name' => env('CREATOR'),
			'search_string' => '2'
		];

		$response = $this->call('POST', 'api/groups/searches', $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = [ 
			'status' => env('STATUS_OK'), 
			'groups' => array(['id' => 2, 'name' => 'Grupo 2'])
		];

		$this->assertEquals($responseData, $expectedResponse);
	}

	/**
	 * Just retrieving all their own groups
	 */
	public function testOkRetrieveCreatedGroups()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'role_name' => env('CREATOR'),
		];

		$response = $this->call('POST', 'api/groups/searches', $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = [ 
			'status' => env('STATUS_OK'), 
			'groups' => array(
				['id' => 1, 'name' => 'Grupo 1'],
				['id' => 2, 'name' => 'Grupo 2'],
				['id' => 3, 'name' => 'Grupo 3']
			)
		];

		$this->assertEquals($responseData, $expectedResponse);
	}

	/**
	 * Search a text within those groups where this user is not a member
	 */
	public function testOkSearchingTextInNotMemberOnes()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'role_name' => env('NOT_MEMBER'),
			'search_string' => 2
		];

		$response = $this->call('POST', 'api/groups/searches', $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = [ 
			'status' => env('STATUS_OK'), 
			'groups' => array(
				['id' => 5, 'name' => 'Grupo 2'],
				['id' => 8, 'name' => 'Grupo 2'],
			)
		];

		$this->assertEquals($responseData, $expectedResponse);
	}

	/**
	 * Just retrieving all the groups where this user is not a member
	 */
	public function testOkRetrieveNotMemberGroups()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'role_name' => env('NOT_MEMBER'),
		];

		$response = $this->call('POST', 'api/groups/searches', $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = [ 
			'status' => env('STATUS_OK'), 
			'groups' => array(
				['id' => 4, 'name' => 'Grupo 1'],
				['id' => 5, 'name' => 'Grupo 2'],
				['id' => 6, 'name' => 'Grupo 3'],
				['id' => 7, 'name' => 'Grupo 1'],
				['id' => 8, 'name' => 'Grupo 2'],
				['id' => 9, 'name' => 'Grupo 3']
			)
		];

		$this->assertEquals($responseData, $expectedResponse);
	}

	/**
	 * Just retrieving all the groups where this user is a member with an specific role
	 */
	public function testOkRetrieveGroupsByMemberRole()
	{
		$user = User::find(2);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'role_name' => 'Student',
		];

		$response = $this->call('POST', 'api/groups/searches', $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = [ 
			'status' => env('STATUS_OK'), 
			'groups' => array(
				['id' => 1, 'name' => 'Grupo 1'],
			)
		];

		$this->assertEquals($responseData, $expectedResponse);
	}

	/**
	 * Searches a text within all the groups where this user is a member with an specific role
	 */
	public function testOkSearchingTextInGroupsWhereRole()
	{
		$user = User::find(1);

		$params = [
			'user_id' => $user->id,
			'csrf_token' => $user->remember_token,
			'role_name' => 'Teacher',
			'search_string' => 2
		];

		$response = $this->call('POST', 'api/groups/searches', $params);
		$responseData = json_decode($response->getContent(), true);

		$expectedResponse = [ 
			'status' => env('STATUS_OK'), 
			'groups' => array(
				['id' => 2, 'name' => 'Grupo 2'],
			)
		];

		$this->assertEquals($responseData, $expectedResponse);
	}

}
