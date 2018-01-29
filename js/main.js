// JavaScript Document
var login; //логин
var webSocket; //сокет-клиент
var sessionID; //идентификатор сессии

function create_session_elem (data) {
	var form = $('<form></form>');
	var name = $('<div>'+data.name+'</div>');
	var hostID = $('<span value="'+data.hostID+'"></span>');
	var pass = $('<input type="password" value="пароль" class="input password" onfocus="this.value='+"''"+'" required>');
	var subm = $('<input type="submit" name="submit" value="войти" class="button"/>')
	form.append(name).append(hostID).append(pass).append(subm);
	
	subm.click(function(event){
		event.preventDefault();
		var message = { 
			'type': 'sessionMessage', 
			'action' : 'enter',
			'hostID' : hostID.attr('value'),
			'password' : pass.val()
		};
		webSocket.send(	message );
	});
	
	$('#session_container').append(form);
}

function get_list(data) {
	console.dir(data);
	if ( 'infoSession' == data.type){
		$('#session_container form').remove();
		$.map( data.items, function (elem){
			create_session_elem(elem);
		});
	}
	else if ( 'createSession' == data.type ){
		if (data.result) {
			var msg = { 
				'type': 'sessionMessage', 
				'action' : 'enter',
				'hostID' : sessionID,
				'password' : $(content_pswd).val()
			};
			webSocket.send(	msg );
		}
		else {
			alert('неккоректные данные');
		}
	}
	else if ( 'enterSession' == data.type ) {
		if (data.result) {
			$(location).attr('href','redactor.php');
		}
	}
	else {
			alert('неправильный пароль');
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
		url: 'php/index.php',
		method: 'GET',
		dataType: 'json',
		data: 'action=login',
		success: function(data) {
			sessionID = data.sessionID;
			var message = { 
				'type': 'sessionMessage', 
				'action' : 'open',
				'broad' : true,
				'sessionID' : sessionID 
			};
			webSocket.send(	message );
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
		webSocket.send(	message );
	});
});
