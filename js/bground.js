// JavaScript Document


$(document).ready(function(){
	$("#logout").click(function(event) {
		$.ajax({
		url: 'php/logoff.php',         /* Куда пойдет запрос. */
		method: 'GET',             /* Метод передачи (post или get), по умолчанию get. */
		dataType: 'json',          /* Тип данных которые ожидаются в ответе (xml, json, script, html). */
		contentType: 'application/json',
		json: true, 
		data: {'action':'logoff'},     /* Параметры передаваемые в запросе. */
		success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
			location.reload();
		} 
		}); 
	});
});