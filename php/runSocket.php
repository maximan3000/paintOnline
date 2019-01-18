<?php

namespace App;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Socket\SocketController;

require_once __DIR__.'/init.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SocketController()
        )
    ),
    '80',
    '0.0.0.0'
);

$server->run();

?>