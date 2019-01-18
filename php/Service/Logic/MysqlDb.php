<?php

namespace App\Service\Logic;

use Simplon\Mysql\PDOConnector;
use Simplon\Mysql\Mysql;

class MysqlDb
{
    public static $client;

    public static function init($url, $user, $pass, $database)
    {
    	$pdo = new PDOConnector(
			$url,
			$user,
			$pass,
			$database
		);
		$pdoConn = $pdo->connect('utf8', []);
        self::$client = new Mysql($pdoConn);
    }
}