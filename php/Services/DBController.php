<?php

namespace App\Services;

use Simplon\Mysql\PDOConnector;
use Simplon\Mysql\Mysql;

class DBController extends SessionController {

	public $mysqli_db;
	
	function __construct() {
		parent::__construct();
		$pdo = new PDOConnector(
			'localhost', 
			'root', 
			'', 
			'paint'
		);
		$pdoConn = $pdo->connect('utf8', []);
		$this->mysqli_db = new Mysql($pdoConn);
	}

	function __destruct() {
		$this->mysqli_db->close();
		parent::__destruct();
	}

	public function dml($dml_str): bool { //Возвращает true при успешной операции
		$query_result = false;
		if ($dml_str) {
			$query_result = $this->mysqli_db->executeSql($dml_str);
		}
		return $query_result;
	}

	public function sql($sql_str): array { //Возвращает данные выборки
		$query_result = array();
		if ($sql_str) {
			$query_result = $this->mysqli_db->fetchRow($sql_str);
		}
		return isset($query_result)?$query_result:array();
	}

}

?>