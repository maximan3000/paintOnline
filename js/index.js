// JavaScript Document
function close_click() {
	$('#reg_form').css('display', 'none');
	$('#overlay').height( 0 );
}

function auth_click(event) {
	event.preventDefault();
	$.ajax({
    url: 'php/auth.php',         /* Куда пойдет запрос. */
    method: 'GET',             /* Метод передачи (post или get), по умолчанию get. */
    dataType: 'json',          /* Тип данных которые ожидаются в ответе (xml, json, script, html). */
    contentType: 'application/json',
    json: true, 
    data: $(auth_data).serialize(),     /* Параметры передаваемые в запросе. */
    success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
        if (data.result) {
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
		
		$.ajax({
			url: 'php/register.php',
			type: 'POST',
			dataType: 'json',
			processData: false,
			contentType: false,
			json: true, 
			data: send_data,
			success: function(data){ 
				if (data.result) {
					close_click();
				}
				else {
					alert('Ошибка при вводе данных (размер файла <= 100КБ)');
				}
			} 
		}); 
	});
	
	
});