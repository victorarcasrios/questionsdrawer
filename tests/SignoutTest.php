<?php

use App\Models\User;

class SignoutTest extends TestCase {

	/**
	 * For the correct credentials, the user is logged out cuz his tokenn is deleted from the db
	 */
	public function testCaseOk()
	{
		$user = User::find(1);
		$params = [
			'user_id' => 1,
			'csrf_token' => $user->remember_token,
		];
		
		$response = $this->call('POST', 'api/users/signout', $params);
		$resposeData = json_decode($response->getContent(), true);

		$this->assertEquals($resposeData['status'], env('STATUS_OK'));
		$this->assertNull(User::find(1)->remember_token);
	}

	/**
	 * For a incorrect csrf token, the application returns a STATUS_AUTH_ERROR code
	 * and the db token remains with the same value
	 */
	public function testKoIncorrectCsrfToken()
	{
		$prev_user_token = User::find(1)->remember_token;
		$params = [
			'user_id' => 1,
			'csrf_token' => 'fakelargetoken123456',
		];

		$response = $this->call('POST', 'api/users/signout', $params);
		$resposeData = json_decode($response->getContent(), true);

		$this->assertEquals($resposeData['status'], env('STATUS_AUTH_ERROR'));

		$post_user_token = User::find(1)->remember_token;
		$this->assertSame($prev_user_token, $post_user_token);

		$this->seed();
	}

}
