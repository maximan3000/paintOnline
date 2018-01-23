<?php

require_once 'DBController.php';

/*
класс для обработки ajax запросов
*/
class AjaxController extends DBController
{
	private $answer; // содержит ответ на ajax запрос
	
	function __construct() { //конструктор класса
		parent::__construct();
		$this->answer = array();
	}

	function __destruct() { //деструктор класса
		parent::__destruct();
	}

	public function takeGet ($get) { //обработка GET запроса. аргумент - переменная $_GET
		if ($get&&$get['action']) {
			switch ($get['action']) {
				case 'logoff':
					$this->destroy();
					$this->answer = array( 'result' => true );
					break;
				case 'login':
					$this->answer = $this->getInfo( array('user_id') );
					break;
				case 'fulldata':
					$this->answer = $this->getInfo( array('user_id', 'nickname', 'avatar') );
					break;
				default:
					# code...
					break;
			}
		}
	}

	public function tagePost($post) { // обработка POST запроса. аргумент - переменная $_POST
		
	}
}

?>