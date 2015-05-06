<?php

use App\Models\User;

class SigninTest extends TestCase {

	public function testSigninByName()
	{
		$params = [
			'name' => 'adri',
			'password' => 'adri',
		];
		
		$response = $this->call('POST', 'api/users/signin', $params);
		$resposeData = json_decode($response->getContent(), true);
		$user = User::find(1);

		$this->assertEquals($resposeData['status'], env('STATUS_OK'));
		$this->assertArrayHasKey('id_and_token', $resposeData);
		$idAndToken = $resposeData['id_and_token'];
		$this->assertEquals($idAndToken['user_id'], $user->id);
		$this->assertEquals($idAndToken['csrf_token'], $user->remember_token);
	}

	public function testSigninByEmail()
	{
		$params = [
			'email' => 'adri@gmail.com',
			'password' => 'adri'
		];
		
		$response = $this->call('POST', 'api/users/signin', $params);
		$resposeData = json_decode($response->getContent(), true);
		$user = User::find(1);

		$this->assertEquals($resposeData['status'], env('STATUS_OK'));
		$this->assertArrayHasKey('id_and_token', $resposeData);
		$idAndToken = $resposeData['id_and_token'];
		$this->assertEquals($idAndToken['user_id'], $user->id);
		$this->assertEquals($idAndToken['csrf_token'], $user->remember_token);
	}

	public function testSigninByBothNameAndEmail()
	{
		$params = [
			'name' => 'adri',
			'email' => 'adri@gmail.com',
			'password' => 'adri'
		];
		
		$response = $this->call('POST', 'api/users/signin', $params);
		$resposeData = json_decode($response->getContent(), true);
		$user = User::find(1);

		$this->assertEquals($resposeData['status'], env('STATUS_OK'));
		$this->assertArrayHasKey('id_and_token', $resposeData);
		$idAndToken = $resposeData['id_and_token'];
		$this->assertEquals($idAndToken['user_id'], $user->id);
		$this->assertEquals($idAndToken['csrf_token'], $user->remember_token);
	}

}
