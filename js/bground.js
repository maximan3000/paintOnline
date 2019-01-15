$(document).ready(function(){
	$("#logout").click(function(event) {
		$.ajax({
			url: 'php/entry.php',
			method: 'GET',
			dataType: 'json',
			contentType: 'application/json',
			json: true,
			data: {'action':'logoff'},
			success: function(data) {
				location.reload();
			} 
		}); 
	});
});
