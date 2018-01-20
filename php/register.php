<?php
	$answer = array(
		'result'=>false
	);
	$query_result = null;
	$arr_name = null;
	
	if ( isset($_POST["username"]) ){
		$mysqli_db = mysqli_connect('localhost', 'root', '', 'paint'); //соединение с БД
		mysqli_set_charset($mysqli_db, "utf8");
				
		if (0==mysqli_connect_errno()) { //если соединение успешно
			
			$us_name = $_POST["username"];
			$us_login = $_POST["login"];
			$us_password = $_POST["password"];
			$us_pswd_repeat = $_POST["password_repeat"];
			$us_filename = 'default.png';

			if ( isset($_FILES['avatar'])&&$_FILES['avatar']['name'] ) {
				
				$us_filename = $_FILES['avatar']['name'];
				preg_match('/.(png|jpg|jpeg|gif)$/', $us_filename, $arr_name);
				
				
			}
			
			if ( !(''==$us_name||''==$us_login||''==$us_password||''==$us_pswd_repeat)&&($us_password==$us_pswd_repeat) ) {
									
				if ( $arr_name ) {
					move_uploaded_file($_FILES['avatar']['tmp_name'], "../img/$us_login.png");
				}
			
				$str_query = "
				INSERT INTO users(id, name, login, password, avatar)
				VALUES (null, '$us_name', '$us_login', '$us_password', '../img/$us_login.png');";
								
				$query_result = mysqli_query($mysqli_db, $str_query); //выполнение запроса
			}
											
			if ($query_result){
				$answer['result'] = true;
			}

			//mysqli_free_result($query_result); //очистка буфера результата
			mysqli_close($mysqli_db); //закрытие соединения
		}
	}

	echo json_encode($answer);
?>