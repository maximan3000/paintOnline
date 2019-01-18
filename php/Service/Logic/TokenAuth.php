<?php

namespace App\Service\Logic;

use ReallySimpleJWT\Token;
use ReallySimpleJWT\Exception\TokenValidatorException;

class TokenAuth {

	public static function getUserId($token)
	{
		$payload = Token::getPayload($token);
		return json_decode($payload)->userID;
	}

	public static function createToken($userID) 
	{
		$userID = $userID;
		$secret = SECRET;
		$expiration = time()+TOKEN_EXPARITION;
		$issuer = 'PaintOnline service';

		$token = Token::create($userID, $secret, $expiration, $issuer);
		
		return $token;
	}

	public static function verify($token)
	{
		return Token::validate($token, SECRET);
	}
}

?>