<?php

	require_once 'classes/SocketController.php';

	error_reporting(E_ALL);	//Выводим все ошибки и предупреждения 
	set_time_limit(0); 			//Время выполнения скрипта ограничено 180 секундами 
	ob_implicit_flush();		//Включаем вывод без буферизации  

	$server = new SocketController("tcp://127.0.0.1:3002");
	$server->run();

?>