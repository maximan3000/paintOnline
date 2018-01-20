<?php
	$answer = array(
		'result'=>false
	);
	if ( isset($_GET["username"])&&''!=$_GET["username"]&&''!=$_GET["password"] ) {
		$mysqli_db = mysqli_connect('localhost', 'root', '', 'paint'); //соединение с БД
		mysqli_set_charset($mysqli_db, "utf8");
		$res = null; //результат
		$user = $_GET["username"];
		$pass = $_GET["password"];
		
		if (0==mysqli_connect_errno()) { //если соединение успешно
			$query_result= mysqli_query($mysqli_db, //выполнение запроса
				"SELECT id, login, password, name, avatar
				FROM users 
				WHERE login='$user';");
			if (mysqli_errno($mysqli_db)){//если ошибка запроса
			}
			else {//если нет ошибок
				for ($i=0; $i<mysqli_num_rows($query_result) ; $i++){
					$res[$i] = mysqli_fetch_array($query_result, MYSQLI_ASSOC);
				}//получение результата запроса			
			}
			mysqli_free_result($query_result); //очистка буфера результата
			mysqli_close($mysqli_db); //закрытие соединения
			if ($user==$res[0]["login"] && $pass==$res[0]["password"]) {
				$answer = array(
					'result' => true,
				);
				
				session_start();
				$_SESSION['user_id'] = $res[0]["login"];
				$_SESSION['nickname'] = $res[0]["name"];
				$_SESSION['avatar'] = $res[0]["avatar"];
			}
		}
	}

	echo json_encode($answer);
?>