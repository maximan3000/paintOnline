// JavaScript Document
var login;
var webSocket; //сокет-клиент

function create_session_elem (name) {
	var form = $('<form></form>');
	var name = $('<div>'+name+'</div>');
	var pass = $('<input type="password" value="пароль" class="input password" onfocus="this.value='+"''"+'" required>');
	var subm = $('<input type="submit" name="submit" value="войти" class="button"/>')
	form.append(name).append(pass).append(subm);
	
	subm.click(function(event){
		event.preventDefault();
		var message = { 
			'type': 'sessionMessage', 
			'action' : 'enter',
			'name' : name.text(),
			'password' : pass.val()
		};
		webSocket.send(	JSON.stringify( message ) );
	});
	
	$('#session_container').append(form);
}

function get_list(data) {
	if ( 'list' == data.type){
		$('#session_container form').remove();
		$.map( data.items, function (elem){
			create_session_elem(elem);
		});
	}
	else if ( 'submit' == data.type ){
		if (data.allow) {
			$(location).attr('href','redactor.php');
		}
		else {
			alert('неправильный пароль');
		}
	}
}

$(document).ready(function() {
	webSocket = $.simpleWebSocket( //инициализация сокета-клиента
		{
			url: 'ws://127.0.0.1:3002/', // address 'ws|wss://ip:port/'
			//protocols: 'tcp', optional - не создано описание в WebSocket.php
			timeout: 20000, // optional, default timeout between connection attempts
			attempts: 1, // optional, default attempts until closing connection
			dataType: 'json' // optional (xml, json, text), default json
		}
	);
	webSocket.connect(); //соединение с сокет-сервером
	webSocket.listen(get_list);
	
	$.ajax({
		url: 'php/logoff.php',         /* Куда пойдет запрос. */
		method: 'GET',             /* Метод передачи (post или get), по умолчанию get. */
		dataType: 'json',          /* Тип данных которые ожидаются в ответе (xml, json, script, html). */
		contentType: 'application/json',
		json: true, 
		data: {'action':'login'},     /* Параметры передаваемые в запросе. */
		success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
			login = data;
			var message = { 
				'type': 'sessionMessage', 
				'action' : 'open',
				'broad' : true,
				'login' : login 
			};
			webSocket.send(	JSON.stringify( message ) );
		} 
		}); 
	
	$("#submit_btn").click(function(event) {
		event.preventDefault();
		var message = { 
			'type': 'sessionMessage', 
			'action' : 'create',
			'name' : $(content_txt).val(),
			'password' : $(content_pswd).val()
		};
		webSocket.send(	JSON.stringify( message ) );
	});
});
