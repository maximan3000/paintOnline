<?php

namespace App;

require_once __DIR__.'/init.php';

use App\Service\Logic\TokenAuth;

header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

$headers = getallheaders();

if (empty($headers['Authorization'])) {
	return;
}

$token = $headers['Authorization'];

if (!TokenAuth::verify($token)) {
	$message = "Authorization failed";
	header("HTTP/1.0 401 $message");
	echo json_encode($message);
}

return;
