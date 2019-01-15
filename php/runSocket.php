<?php

namespace App;
require_once __DIR__.'/init.php';

error_reporting(E_ALL);
set_time_limit(0); 			//Время выполнения скрипта ограничено 180 секундами 
ob_implicit_flush();		//Включаем вывод без буферизации  

$server = new SocketController("tcp://0.0.0.0:80");
$server->run();

?>