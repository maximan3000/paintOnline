<?php
require_once 'classes/AjaxController.php';

$ajax = new AjaxController();

$res = null;

if ( $_GET ) {
	$res = $ajax->takeGet($_GET);
}
else if ( $_POST ) {
	$res = $ajax->takePost($_POST, $_FILES);
}

echo json_encode( $res );

?>