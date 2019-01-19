<?php

namespace App\PastServices;

/*
класс для обработки ajax запросов
*/
class AjaxController extends DBController {
	private $answer; // содержит ответ на ajax запрос
	
	function __construct() { //конструктор класса
		parent::__construct();
		$this->answer = null;
	}

	function __destruct() { //деструктор класса
		parent::__destruct();
	}

	public function takeGet ($get) { //обработка GET запроса. аргумент - переменная $_GET
		if ( $get&&isset($get['action']) ) { 
			switch ($get['action']) {
				case 'logoff':
					$this->destroy();
					$this->answer = array( 'result' => true );
					break;
				case 'login':
					$this->answer = $this->getInfo( array('login', 'sessionID') );
					break;
				case 'fulldata':
					$this->answer = $this->getInfo( array('login', 'name', 'avatar') );
					break;
				case 'auth':
					if ( ''!=$get['login'] ) {
						$query = $this->sql( "
							SELECT login, name, avatar
							FROM pastusers 
							WHERE login='".$get['login']."' AND password='".$get['password']."';" 
						);  
						if ( $query ) {
							$this->setInfo( $query );
							$this->answer = $this->getInfo( array('login', 'name', 'avatar') );
						}
					}
					break;
				default:
					# code...
					break;
			}
		}
		return $this->answer;
	}

	public function takePost($post, $files) { // обработка POST запроса. аргумент - переменная $_POST
		if ( $post&&isset($post['action']) ) { 
			switch ($post['action']) {
				case 'register':
					
					if ( $post['password']==$post['password_repeat'] ) {

						if ( $files['avatar']['name']&&preg_match('/.(png|jpg|jpeg|gif)$/', $files['avatar']['name'], $arr_name) ) {
							$us_filename = $post['login'];
						}
						else {
							$us_filename = 'default.png';
						}

						if ( $this->dml( "
							INSERT INTO pastusers(id, name, login, password, avatar)
							VALUES (null, '".$post['username']."', '".$post['login']."', '".$post['password']."', '../img/$us_filename.png');" ) ) {
							$this->answer = array( 'result' => true );
							move_uploaded_file($files['avatar']['tmp_name'], "../img/".$post['login'].".png");
						}
					}

					break;
				default:
					# code...
					break;
			}
		}
		return $this->answer;
	}
}

?>