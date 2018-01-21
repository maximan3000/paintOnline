<?php
	
	//require 'class/SessionController.php';

	if ( $_GET['action']&&'logoff'==$_GET['action'] ){
		session_start();
		session_destroy();
		echo json_encode( array( 'result'=>true ) );
		exit;
	}
	else if ( $_GET['action']&&'login'==$_GET['action'] ) {
		session_start();
		echo json_encode( $_SESSION['user_id'] );
		exit;
	}
	else if ( $_GET['action']&&'fulldata'==$_GET['action'] ) {
		session_start();
		$data = array(	'login'=>$_SESSION['user_id'],
						'nickname'=>$_SESSION['nickname'],
						'avatar'=>$_SESSION['avatar']
					 );
		echo json_encode( $data );
		exit;
	}
	
?>