<?php

namespace App\Service\Logic;

use App\Service\Logic\MysqlDb;
use App\Service\Logic\TokenAuth;
use App\Service\Logic\SafeException;

class RegisterUser {

	public static function register($userData) : array
	{
		if (!trim($userData['username']))
		{
			throw new SafeException('Empty username');
		}
		if (!trim($userData['password']))
		{
			throw new SafeException('Empty password');
		}
		if (!trim($userData['firstName']))
		{
			throw new SafeException('Empty firstName');
		}
		if (!trim($userData['lastName']))
		{
			throw new SafeException('Empty lastName');
		}
		if (MysqlDb::$client->fetchColumn(
			'SELECT count(*) FROM users WHERE username="'.$userData['username'].'";') > 0)
		{
			throw new SafeException('Such username exists');
		}

		$user = array(
			'username' => $userData['username'],
			'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
			'firstName' => $userData['firstName'],
			'lastName' => $userData['lastName']
		);
		MysqlDb::$client->insert("users", $user);

		return MysqlDb::$client->fetchRow(
			'SELECT username,firstName,lastName FROM users WHERE username="'.$user['username'].'";'
		);
	}

}

?>