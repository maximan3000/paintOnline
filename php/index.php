<?php
/* echo json_encode( "res" ); exit(); */
require_once 'classes/AjaxController.php';

$ajax = new AjaxController();

if ( $_GET ) {
	$res = $ajax->takeGet($_GET);
}
else if ( $_POST ) {
	$res = $ajax->takePost($_POST, $_FILES);
}

echo json_encode( $res );

?>