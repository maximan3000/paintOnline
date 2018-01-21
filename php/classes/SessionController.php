<?php
	
/*
	класс, реализующий работу сессии
*/
class SessionController
{
	//protected $session = $_SESSION; //ссылка на переменную данных сессии

	function __construct(argument)
	{
		session_start(); //старт сессии
	}

	public function getInfo($type) {
		if ($type) {
			$result = null;
			foreach ($type as $key => $value) {
				if ( $_SESSION[$key] ) {
					$result[] = array( $value => $value, );
				}
			}
		}
		else { return false; }
	}

}

?>