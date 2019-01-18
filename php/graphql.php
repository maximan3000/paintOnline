<?php
namespace App;

header('Content-type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");


require_once __DIR__.'/init.php';

use App\Service\Types;
use App\Service\Logic\MysqlDb;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;

MysqlDb::init('paintdb', 'root', 'pass', 'paintdb');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variables = isset($input['variables']) ? $input['variables'] : null;

$schema = new Schema([
    'query' => Types::query(),
    'mutation' => Types::mutation()
]);

try {
    $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
} catch (\Exception $e) {
    $result = [
        'error' => [
            'message' => $e->getMessage()
        ]
    ];
}

if (!empty($result->errors[0]->message)) {
	$message = $result->errors[0]->message;
	header("HTTP/1.0 400 $message");
	echo json_encode($message);
	return;
}

echo json_encode($result);

?>