
<?php
	session_start();
	if ( $_SESSION['user_id'] ) {
		echo "true";
	}
	else {
		header("Location: index.php");
	}
?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Документ без названия</title>

<!-- основные стили страницы -->
<link href="styles/bground.css?=4" rel="stylesheet" type="text/css">
<link href="styles/main.css?=4" rel="stylesheet" type="text/css">
<link href="styles/auth.css?=4" rel="stylesheet" type="text/css">

<!--библиотека JQuery -->
<script src="js/jquery-3.2.1.js" type="text/javascript"></script>
<!--расширение JQuery для работы с сокетами ресурс: https://github.com/dchelimsky/jquery-websocket -->
<script src="js/jquery.simple.websocket.js" type="text/javascript"></script>


<!-- основные скрипты -->
<script src="js/main.js?=6" type="text/javascript"></script>
<script src="js/bground.js?=6" type="text/javascript"></script>

</head>

<body>

<div class="top_theme">
  <header>
    <div id="bye_top"> <span><?= $_SESSION['user_id'] ?></span> <button id="logout"> выйти </button> </div>
    <div> <a href="index.php"> на главную </a> </div>
  </header>
  <h1> Совместный графический редактор </h1>
  <div>
  	<form class="login-form">
  		<div class="header">
  		  <span>СОЗДАНИЕ СЕССИИ:</span>
  		</div>
  		<div class="content">
    		<input id="content_txt" type="text" title="название" class="input username" placeholder="название сессии" required>
    		<input id="content_pswd" type="password" title="пароль" placeholder="пароль для входа" class="input password" required>
  		</div>
  		
  		<div class="footer">
  		  <input id="submit_btn" type="submit" name="submit" value="СОЗДАТЬ" class="button" />
  		</div>
  	</form>
  </div>
  
  <div id="session_container">
  	<!-- элемент списка сессий
  	<form>
  		<span>session 1</span>
  		<input type="password" value="пароль" class="input password" onfocus="this.value=''" required>
  		<input type="submit" name="submit" value="войти" class="button" />
  	</form>
  	-->
  </div>  
  
  
</div>

</body>
</html>
