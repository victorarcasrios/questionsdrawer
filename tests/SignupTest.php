<?php

class SignupTest extends TestCase {

	public function testCaseOk()
	{
		$params = [
			'name' => 'NewUserForTest',
			'email' => 'NewUserForTest@gmail.com',
			'password' => 'aLazyPasswordForTest',
		];
		$expectedResponse = ['status' => env('STATUS_OK')];

		$response = $this->call('POST', 'api/users', $params);
		$resposeData = json_decode($response->getContent(), true);
		
		$this->assertSame($expectedResponse, $resposeData);
		$this->seed();
	}

}
