<?php
	
/*
	класс, реализующий взаимодействие с текущей запущенной сессией
*/
class SessionController {

	//protected $session = $_SESSION; //ссылка на переменную данных сессии

	function __construct() {
		session_start(); //старт сессии
		$_SESSION['sessionID'] = session_id();
	}

	function __destruct() {// деструктор класса
	}

	protected function getInfo($type) { // получение информации сессии; на вход подается массив строк, где каждая строка - свойство сессии, информацию о которой нужно получить
		$result = null;
		if ($type) {
			foreach ($type as $value) {
				if ( $_SESSION[$value] ) {
					$result[ $value ] = $_SESSION[$value] ;
				}
			}
		}
		return $result;
	}

	protected function setInfo($value) { // установка свойств сессии; на вход подается массив с элементами типа ключ => значение, где ключ - название устанавливаемого свойства и значение - значение этого свойства
		if ($value) {
			foreach ($value as $key => $value) {
				$_SESSION[$key] = $value;
			}
			return true;
		}
		else { return false; }
	}

	protected function destroy() { // уничтожение работающей сессии
		session_destroy();
	}

}

?>