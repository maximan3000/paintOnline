var login; //логин
var webSocket; //сокет-клиент
var sessionID; //идентификатор сессии
var openMessage; //первое сообщение в сессии сокета для его инициализации 

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
		webSocket.send(JSON.stringify(message));
	});
	
	$('#session_container').append(form);
}

function get_list(data) {
	data = JSON.parse(data.data);
	console.dir(data);
	if ( 'infoSession' == data.type){
		$('#session_container form').remove();
		$.map( data.items, function (elem){
			create_session_elem(elem);
		});
	}
	else if ( 'createSession' == data.type ){
		if (data.result) {
			var message = { 
				'type': 'sessionMessage', 
				'action' : 'enter',
				'hostID' : sessionID,
				'password' : $(content_pswd).val()
			};
			webSocket.send(JSON.stringify(message));
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
	/*webSocket = $.simpleWebSocket(
		{
			url: 'ws://127.0.0.1:3002/', // address 'ws|wss://ip:port/'   
			protocols: 'tcp', //optional - не создано описание в WebSocket.php
			timeout: 1000,
			attempts: 5,
			dataType: 'json'
		}
	);*/
	webSocket = new WebSocket('ws://127.0.0.1:3002');
	webSocket.onmessage = get_list;
	
	$.ajax({
		url: 'php/entry.php',
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
			console.dir(webSocket);
			if (webSocket.readyState==1) {
				webSocket.send(JSON.stringify(message));
			}
			else {
				openMessage = message;
				webSocket.onopen = function(e) {
				    webSocket.send(JSON.stringify(openMessage));
				};
			}
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
		webSocket.send(JSON.stringify(message));
	});
});
