<!-- старт сессии -->
<?php 
	session_start();
?>

<!doctype html>
<html>
<head>

<meta charset="utf-8"> <!-- установка кодировки -->
<title> Совместный графический редактор </title>

<!-- основные стили страницы -->
<!-- набор стилей для всех страниц -->
<link href="styles/bground.css?=4" rel="stylesheet" type="text/css">
<!-- набор стилей для текущей страницы -->
<link href="styles/index.css?=1" rel="stylesheet" type="text/css">
<!-- набор стилей для формы авторизации и регистрации -->
<link href="styles/auth.css?=1" rel="stylesheet" type="text/css">

</head>
<body>

<div id="overlay"></div> <!-- оверлей-блок для затемнения фона страницы -->

<div class="top_theme"> <!-- все содержимое страницы -->
	<header> <!-- меню страницы (верхняя панель) -->
		<div id="bye_top"> <!-- блок с логином пользователя (справа) -->
			<?php

			if( isset($_SESSION['user_id']) ) {
			echo $_SESSION['user_id'].' <button id="logout"> выйти </button>';
			}
			else {
				echo "привет гость";
			}

			?>
		</div>
		<div> добро пожаловать </div> <!-- левый блок меню -->
	</header>

	<h1> Совместный графический редактор </h1> <!-- главнй заголовок страницы -->

	<div id="auth_form" > <!-- блок формы авторизации -->
		<form name="login-form" class="login-form" action="" id="auth_data" onSubmit="auth_click(event);"> <!-- форма авторизации -->

			<div class="header"> <!-- заголовок формы -->
				<h1>Авторизация</h1> 
				<span>Введите ваши регистрационные данные для входа</span>
			</div>

			<div class="content"> <!-- поля для ввода данных -->
				<input name="username" type="text" class="input username" title="Логин" placeholder="логин" required/> <!-- поле логина -->
				<input name="password" type="password" class="input password" title="Пароль" placeholder="пароль" required/> <!-- поле пароля -->
			</div>

			<div class="footer"> <!-- кнопки формы -->
				<input type="submit" name="submit" value="ВОЙТИ" class="button" /> <!-- отправка данных формы -->
				<input type="button" value="Регистрация" class="register" id="auth_btn"/> <!-- кнопка открытия формы регистрации -->
			</div>

		</form>
	</div>
	
	<div id="reg_form"> <!-- блок формы регистрации -->

		<button class="close_img" onClick="close_click();"> <!-- кнопка закрытия формы регистрации -->
			<img  alt="close" src="img/close.png">
		</button> 

		<form name="login-form" class="login-form" method="post" enctype='multipart/form-data' id="reg_data"> <!-- форма регистрации -->

			<div class="header"> <!-- заголовки формы -->
				<h1>Регистрация</h1>
				<span>Введите регистрационные данные (имя будет отображаться в чате; логин и пароль для входа в систему; файл изображения для аватарки в чате)</span>
			</div>

			<div class="content"> <!-- поля для ввода данных формы -->
				<input name="username" type="text" class="input username" title="никнейм" placeholder="никнейм (для чата)" required/> <!-- никнейм -->
				<input name="login" type="text" class="input username" title="Логин" placeholder="логин" required/> <!-- логин -->
				<input name="password" type="password" class="input password" title="Пароль" placeholder="пароль" required/> <!-- пароль -->
				<input name="password_repeat" type="password" class="input password" title="Повтор пароля" placeholder="повтор пароля" required/> <!-- повтор пароля -->
				<input type="hidden" name="MAX_FILE_SIZE" value="102400" /> <!-- скрытый элемент для настройки контейнера с файлом (размера файла) -->
				<span>загрузить изображение аватарки (не обязательно)</span>
				<br>
				<input id="avatar" type="file" name="avatar" title="аватарка" accept="image/*"/> <!-- контейнер файла -->
			</div>

			<div class="footer"> <!-- кнопки формы -->
				<input type="submit" name="submit" value="ПРИНЯТЬ" id="reg_submit_btn" class="button"/> <!-- отправка данных формы -->
				<input type="button" name="submit" value="Войти" class="register" onClick="close_click();"/> <!-- кнопка перехода к авторизации -->
			</div>	

		</form>
	</div>	

</div>

<!--библиотека JQuery -->
<script src="js/jquery-3.2.1.js" type="text/javascript"></script>
<!-- скрипты для всех страниц -->
<script src="js/bground.js?=4" type="text/javascript"></script>
<!-- основные скрипты данной страницы -->
<script src="js/index.js?=6" type="text/javascript"></script>

</body>
</html>
