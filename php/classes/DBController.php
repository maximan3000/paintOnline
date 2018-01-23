<?php

require_once 'SessionController.php';

/*
класс для работы с базой данных
*/
class DBController extends SessionController {

	protected $mysqli_db; //переменная с информацией о соединении с базой
	
	function __construct() { //конструктор класса
		parent::__construct();
		$this->mysqli_db = mysqli_connect('localhost', 'root', '', 'paint'); //подключение к базе
		mysqli_set_charset($this->mysqli_db, "utf8"); //изменение кодировки подключения к базе данных
	}

	function __destruct() { // деструктор класса
		mysqli_close( $this->mysqli_db); //закрытие соединения с базой
		parent::__destruct();
	}

	private function dml($dml_str) { //выполнение dml операции с БД (data manipulation). Возвращает true при успешной операции
		if ($dml_str&&0==mysqli_connect_errno()) { //проверка параметра и соединения с базой

			$query_result= mysqli_query($this->mysqli_db, $dml_str); //выполнение запроса

			if (0==mysqli_errno($this->mysqli_db)&&mysqli_affected_rows($this->mysqli_db)>0) { //проверка на наличие ошибок запроса к БД и проверка того, что были изменены данны в БД
				return true;
			}
		}
		return false;
	}

	private function sql($sql_str) { //выполнение выборки данных из БД. Возвращает данные выборки либо false
		if ($sql_str&&0==mysqli_connect_errno()) { //проверка параметра и соединения с базой

			$query_result= mysqli_query($this->mysqli_db, $sql_str); //выполнение запроса

			if (0==mysqli_errno($this->mysqli_db)) { //проверка на наличие ошибок запроса к БД

				for ($i=0; $i<mysqli_num_rows($query_result) ; $i++){
					$res[$i] = mysqli_fetch_array($query_result, MYSQLI_ASSOC);
				}//получение результата запроса	

				mysqli_free_result($query_result); //очистка буфера результата

				return $res;
			}
		}
		return false;
	}

}

?>