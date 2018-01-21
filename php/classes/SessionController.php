<?php
	
/*
	класс, реализующий взаимодействие с текущей запущенной сессией
*/
class SessionController
{
	//protected $session = $_SESSION; //ссылка на переменную данных сессии

	function __construct(argument)
	{
		session_start(); //старт сессии
	}

	public function getInfo(&$type) { // получение информации сессии; на вход подается массив строк, где каждая строка - свойство сессии, информацию о которой нужно получить
		if ($type) {
			$result = null;
			foreach ($type as $value) {
				if ( $_SESSION[$value] ) {
					$result[] = array( $value => $_SESSION[$value] );
				}
			}
			$type = &$result;
			return true;
		}
		else { return false; }
	}

	public function setInfo(&$value) { // установка свойств сессии; на вход подается массив с элементами типа ключ => значение, где ключ - название устанавливаемого свойства и значение - значение этого свойства
		if ($value) {
			foreach ($value as $key => $value) {
				$_SESSION][$key] = $value;
			}
			return true;
		}
		else { return false; }
	}

	public function destroy() { // уничтожение работающей сессии
		session_destroy();
	}

}

?>