<?php 
	session_start();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title> Совместный графический редактор </title>

<!-- основные стили страницы -->
<link href="styles/bground.css?=4" rel="stylesheet" type="text/css">
<link href="styles/index.css?=1" rel="stylesheet" type="text/css">
<link href="styles/auth.css?=1" rel="stylesheet" type="text/css">

<!--библиотека JQuery -->
<script src="js/jquery-3.2.1.js" type="text/javascript"></script>
<script src="js/bground.js?=4" type="text/javascript"></script>

<!-- основные скрипты -->
<script src="js/index.js?=6" type="text/javascript"></script>
<script src="js/bground.js?=6" type="text/javascript"></script>

</head>
<body>
<div id="overlay">
</div>
<div class="top_theme">
	<header>
		<div id="bye_top"> <?php if( isset($_SESSION['user_id']) ) {
			echo $_SESSION['user_id'].' <button id="logout"> выйти </button>';
		}
		else {
			echo "привет гость";
		}?>
		
		</div>
		<div> добро пожаловать </div>
	</header>
	<h1> Совместный графический редактор </h1>

	<div id="auth_form" >
		<form name="login-form" class="login-form" action="" id="auth_data" onSubmit="auth_click(event);">

		<div class="header">
		<h1>Авторизация</h1>
		<span>Введите ваши регистрационные данные для входа</span>
		</div>

		<div class="content">
		<input name="username" type="text" class="input username" title="Логин" placeholder="логин" required/>
		<input name="password" type="password" class="input password" title="Пароль" placeholder="пароль" required/>
		</div>
		<div class="footer">
		<input type="submit" name="submit" value="ВОЙТИ" class="button" />
		<input type="button" value="Регистрация" class="register" id="auth_btn"/>
		</div>
		</form>
	</div>
	
	<div id="reg_form">
		<button class="close_img" onClick="close_click();"><img  alt="close" src="img/close.png"></button>
		<form name="login-form" class="login-form" method="post" enctype='multipart/form-data' id="reg_data">

		<div class="header">
		<h1>Регистрация</h1>
		<span>Введите регистрационные данные (имя будет отображаться в чате; логин и пароль для входа в систему; файл изображения для аватарки в чате)</span>
		</div>

		<div class="content">
			<input name="username" type="text" class="input username" title="никнейм" placeholder="никнейм (для чата)" required/>
			<input name="login" type="text" class="input username" title="Логин" placeholder="логин" required/>
			<input name="password" type="password" class="input password" title="Пароль" placeholder="пароль" required/>
			<input name="password_repeat" type="password" class="input password" title="Повтор пароля" placeholder="повтор пароля" required/>
			<input type="hidden" name="MAX_FILE_SIZE" value="102400" />
			<span>загрузить изображение аватарки (не обязательно)</span>
			<br>
			<input id="avatar" type="file" name="avatar" title="аватарка" accept="image/*"/>
		</div>

		<div class="footer">
			<input type="submit" name="submit" value="ПРИНЯТЬ" id="reg_submit_btn" class="button"/>
			<input type="button" name="submit" value="Войти" class="register" onClick="close_click();"/>
		</div>	

		</form>
	</div>	
</div>
</body>
</html>
