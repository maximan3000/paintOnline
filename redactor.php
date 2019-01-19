<?php
	session_start();
	if ( $_SESSION['login'] ) {
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
<title> Редактор </title>

<link href="styles/bground.css?=4" rel="stylesheet" type="text/css">
<link href="styles/redactor.css?=16" rel="stylesheet" type="text/css">

</head>
<body>
<div class="top_theme">
  <header class="top_button">
    <div id="bye_top"> <span><?= $_SESSION['login'] ?></span> <button id="logout"> выйти </button> </div>
    <div> <button id="exit_session"> отключиться от сессии </button> </div>
  </header>
  <h1> Совместный графический редактор </h1>
  <div class="socket">
    <div id="draw_place">
      <div id="tools">
        <div id="skatch" name="skatch" class="tool"><img src="img/skatch.png" alt="skatch"></div>
        <div id="brush" name="brush" class="tool"><img src="img/brush.png" alt="brush"></div>
        <div id="eraser" name="eraser" class="tool"><img src="img/eraser.png" alt="eraser"></div>
        <div id="colorControl" name="colorControl">
          <input type="color" value="#000" class="">
        </div>
        <div id="sizeControl" name="sizeControl">
          <input type="range" min="1" max="30" value="1" step="1" class="">
          <span></span>
        </div>
        <div id="clear" name="clear" class="tool"><img src="img/clear.png" alt="clear"></div>
      </div>
      <br>
      <canvas id="draw_area" width="500" height="500"></canvas>
    </div>
    <div id="chat_area">
      <div id="chat">
		  <div id="chat_scroll">
		  	<div id="hidden_message" class='message hidden'></div>
		  </div>
      </div>
      <form id="form_message" name="form_message" onsubmit="return false;">
			<div class="form_inputs">
					<input id="text_input" name="message" autofocus placeholder="Введите ваше сообщение..." autocomplete="off" >
					<button id="submit_btn" type="submit" name="" value="" onClick="submitFrom();"><img src="img/enter-key.png" alt="enter" height="25" width="15"> </button>
			</div>
		</form>
    </div>
  </div>
</div>

<script src="js/jquery-3.2.1.js" type="text/javascript"></script>
<!--расширение JQuery для работы с сокетами ресурс: https://github.com/dchelimsky/jquery-websocket -->
<script src="js/jquery.simple.websocket.js" type="text/javascript"></script>
<!--расширение JQuery для полосы прокрутки ресурс: http://rocha.la/jQuery-slimScroll -->
<script src="js/jquery.slimscroll.min.js" type="text/javascript"></script>

<script src="js/redactor.js?=5" type="text/javascript"></script>
<script src="js/skatch.js?=3" type="text/javascript"></script>
<script src="js/brush.js?=3" type="text/javascript"></script>
<script src="js/bground.js?=3" type="text/javascript"></script>

</body>
</html>



