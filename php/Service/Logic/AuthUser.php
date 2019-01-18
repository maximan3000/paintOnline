<?php

namespace App\Service\Logic;

use App\Service\Logic\MysqlDb;
use App\Service\Logic\TokenAuth;
use App\Service\Logic\SafeException;

class AuthUser {

	public static function auth($userData) : array
	{
		if (empty($userData['username']) || empty($userData['password'])) {
			throw new SafeException('Empty login (password) received');
		}

		$user = MysqlDb::$client->fetchRow(
			'SELECT * FROM users WHERE username="'.$userData['username'].'";'
		);
		
		if (empty($user)) {
			throw new SafeException('User didn`t found');
		}

		if (!password_verify($userData['password'], $user['password']))
		{
			throw new SafeException('Password is wrong');
		}
		
		$authUser = [
			'id' => $user['id'],
			'username' => $user['username'],
			'firstName' => $user['firstName'],
			'lastName' => $user['lastName'],
			'token' => TokenAuth::createToken($user['id'])
		];
		return $authUser;
	}
}
