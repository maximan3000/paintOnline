// JavaScript Document
function close_click() {
	$('#reg_form').css('display', 'none');
	$('#overlay').height( 0 );
}

function auth_click(event) {
	event.preventDefault();
	var get_data = $(auth_data).serialize();
	get_data += '&action=auth';

	$.ajax({
    url: 'php/index.php',         /* Куда пойдет запрос. */
    method: 'GET',             /* Метод передачи (post или get), по умолчанию get. */
    dataType: 'json',          /* Тип данных которые ожидаются в ответе (xml, json, script, html). */
    contentType: 'application/json',
    json: true, 
    data: get_data,     /* Параметры передаваемые в запросе. */
    success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
        if (data) {
			$(location).attr('href','main.php');
		}
		else {
			alert('Некорректные данные');
		}
    } 
    }); 
}


$(document).ready(function(){
	$('#auth_btn').click( function(event) {
		$('#reg_form').css('display', 'block');
		$('#overlay').height( $(document).height() );
	});
	
	$('#reg_submit_btn').click(function(event) {
		event.preventDefault();
		var send_data = new FormData(reg_data);
		send_data.append( 'action', 'register' );
		
		$.ajax({
			url: 'php/index.php',
			type: 'POST',
			dataType: 'json',
			processData: false,
			contentType: false,
			json: true, 
			data: send_data,
			success: function(data){ 
				if (data) {
					close_click();
				}
				else {
					alert('Ошибка при вводе данных (размер файла <= 100КБ)');
				}
			} 
		}); 
	});
	
	
});