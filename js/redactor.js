var webSocket; //сокет-клиент
//объекты для работы с canvas 
var action = false; //отслеживание состояния мыши
var ctx, skatch, brush; //ctx - само полотно 2d, skatch - объект кисти skatch, brush - объект кисти brush
var website_color = "white";

function submitFrom() {
	sendMessage(form_message.message.value, "txtMessage"); 
	form_message.message.value = '';
	return false; //сбрасываем другие обработчики события
}

//функция отображения сообщений в чате (текст сообщения, тип сообщения: mine - свой, alien - чужой)
function createMessage(data, flag='mine') {
	
	var msg = $("<div class='message'></div>");
	var img = $("<img src='cursovic/"+data.avatar+"' alt='аватар' class='avatar'>");
	var elem = $("<div class='text_content'>"+data.name+": <br> "+data.text + "</div>");
	
	img.appendTo(msg);
	elem.appendTo(msg);

	if ('mine'===flag) {
		msg.addClass("your_message");
	}
	else if ('alien'===flag) {
		msg.addClass("alien_message");
	}

	$('#chat_scroll').append( msg ); // присоединение сообщения к чату
	$('#hidden_message').css( 'height', $('#hidden_message').height() - msg.height() - 20 ); //уменьшение высоты невидимого блока (20 - margin-top + margin-right)

	scrollChat(msg.position().top); //прокрутка чата до конца
}

//функция прокрутки чата до конца (смещение последнего элемента относительно страницы)
function scrollChat(offsetTop){
	var elem = $("#chat_scroll");
	elem.slimScroll({scrollTo: offsetTop+elem.scrollTop() }); //сама прокрутка
}

//функция отправки сообщения сокету-серверу
function sendMessage(message, type) {
	if ("txtMessage"==type) {
		if ($.trim( message )) { //проверка, не пустое ли сообщение
			
			$.ajax({
				url: 'php/entry.php',
				method: 'GET', 
				dataType: 'json',
				data: 'action=fulldata',
				success: function(data){ 
					var msg = {
						'type': 'txtMessage',
						'name': data.name,
						'avatar': data.avatar,
						'text': message
					};
					console.dir(data);
					webSocket.send(JSON.stringify(msg));
					createMessage( {'text': message, 'name':data.name, 'avatar': data.avatar } , 'mine');
				} 
			}); 
		}
	}
	else if ("pngMessage"==type) {
		message.globalAlpha = ctx.globalAlpha;
		message.strokeStyle = ctx.strokeStyle;
		message.lineWidth = ctx.lineWidth;
		message.type = 'pngMessage';
		webSocket.send(JSON.stringify(message));
	}
}

//обработчик события - получения сообщения от сервера
function getMessage(message) { 
	msg = JSON.parse(message.data);
	if ( 'txtMessage' == msg.type ) {
		createMessage( { 'text': msg.text, 'name': msg.name, 'avatar': msg.avatar } , 'alien'); //отображение полученного в чате сообщения
	}
	else if ( 'pngMessage' == msg.type ) {
		var temp = {"ga": ctx.globalAlpha, "sS": ctx.strokeStyle, "lW": ctx.lineWidth};
		ctx.globalAlpha = msg.globalAlpha;
		ctx.strokeStyle = msg.strokeStyle;
		ctx.lineWidth = msg.lineWidth;
		if ( "skatch" == msg.tool ){
			updateSkatchMove(msg);
		}
		else if ( "brush" == msg.tool ) {
			makeLine(msg.x, msg.y, msg.toX, msg.toY);
		}
		else if ( "clear" == msg.tool ) {
			ctx.clearRect(0,0,$("#draw_area").height(), $("#draw_area").width());
		}
		ctx.globalAlpha = temp.ga;
		ctx.strokeStyle = temp.sS;
		ctx.lineWidth = temp.lW;
	} 
	else if ( 'exitSession' == msg.type ) {
		if (msg.result) { $(location).attr('href','main.php'); }
	}
}

//функция выбора инструмента рисования
function chooseTool(tool) {

	var cnvs = $("#draw_area");
	cnvs.unbind("mousedown");
	cnvs.unbind("mousemove");
	cnvs.unbind("mouseup");
	cnvs.unbind("mouseleave");

	if ("skatch" == tool) {
		ctx.globalAlpha = 0.1;
		ctx.strokeStyle = $("#colorControl input").val();
		cnvs.mousedown( mDown );
		cnvs.mousemove( mMove );
		cnvs.mouseup( mUp );
		cnvs.mouseleave( mUp );
	}
	else if ("brush" == tool) {
		ctx.globalAlpha = 1;
		ctx.strokeStyle = $("#colorControl input").val();
		cnvs.mousedown( bDown );
		cnvs.mousemove( bMove );
		cnvs.mouseup( bUp );
		cnvs.mouseleave( bUp );
	}
	else if ("eraser" == tool) {
		ctx.globalAlpha = 1;
		ctx.strokeStyle = website_color;
		cnvs.mousedown( bDown );
		cnvs.mousemove( bMove );
		cnvs.mouseup( bUp );
		cnvs.mouseleave( bUp );
	}
}

//инициализация объектов canvas 
function initcnvs(){
	ctx = draw_area.getContext('2d');
	ctx.lineJoin = "round";
	
	ctx.globalAlpha = 1;
	ctx.strokeStyle = "black";
	ctx.lineWidth = $("#sizeControl input").val();
	
	skatch = {};
	brush = {};
	skatch.points = new Array(10);
	brush.mPrevPos = { "x" : 0, "y" : 0}; //предыдущая позиция курсора
};

//обработка нажатия на clear
function clearCanvas() {
	ctx.clearRect(0,0,$("#draw_area").height(), $("#draw_area").width());
	var msg = {};
	msg.tool = "clear";
	sendMessage(msg, "pngMessage");
}

//скрипты инициализации
$(document).ready(function(){
	
	$('#chat_scroll').slimScroll({ //инициализация полосы прокрутки
        height: '523px',
		start: 'bottom',
		size: '4px'
    });
	
	webSocket = new WebSocket('ws://127.0.0.1:3002');
	webSocket.onmessage = getMessage;
	
	$.ajax({
		url: 'php/entry.php',
		method: 'GET',
		dataType: 'json',
		data: 'action=login',
		success: function(data) {
			var message = { 
				'type': 'sessionMessage', 
				'action' : 'open',
				'broad' : true,
				'sessionID' : data.sessionID 
			};
			webSocket.send(JSON.stringify(message));
			load_actions();
		} 
	}); 
});

function load_actions() {

	initcnvs(); //инициализация области canvas
	
	//привязка функции выбора инструмента рисования
	$("#skatch, #brush, #eraser").bind("click", function() {
		$("#tools div").css("background-color", "");
		$(this).css("background-color", "#C8C8C8");
		var tool = $(this).attr("name");
		chooseTool(tool);
	});
	
	//привязка события для изменения кружка отображения размера кисти
	var inpSize = $("#sizeControl input");
	inpSize.bind("input", function() {
		var span = $("#sizeControl span");
		span.css("width", inpSize.val());
		span.css("height", inpSize.val());
		span.css("top", -inpSize.val()/2);
		
		ctx.lineWidth = inpSize.val();
	});
	
	//привязка события для изменения цвета кисти
	var inpColor = $("#colorControl input");
	inpColor.change(function() {
		ctx.strokeStyle = inpColor.val();
	});
	
	//привязка события нажатия на кнопку clear
	$("#clear").click( clearCanvas );
	
	//событие выхода из сессии
	$("#exit_session, #logout").click(function(event) {
		var message = { 
			'type': 'sessionMessage', 
			'action' : 'exit'
		};
		webSocket.send(JSON.stringify(message));
	});

}
