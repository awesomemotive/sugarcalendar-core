jQuery(document).ready(function($) {
	$('body').on('submit', '.sc_events_form', function() {

		document.body.style.cursor = 'wait';
		var calendar = $(this).parent().parent().parent().parent().attr('id');
		var data = $(this).serialize();
	    $.post(sc_vars.ajaxurl, data, function (response) {
	   		$('#' + calendar).html(response);
	 	}).done(function() {
	 		document.body.style.cursor = 'default';
	 	});
		return false;
	});
});