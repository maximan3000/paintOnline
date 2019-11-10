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
    '3002',
    '127.0.0.1'
);

echo "Socker runs at 127.0.0.1:3002";
$server->run();

?>