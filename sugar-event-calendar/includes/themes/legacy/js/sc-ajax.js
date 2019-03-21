jQuery(document).ready(function($) {
	$('body').on('submit', '.sc_events_form', function() {

		document.body.style.cursor = 'wait';
		if( $(this).attr('id') == 'sc_event_select' ) {
			var calendar = $(this).parent().parent().parent().attr('id');
		} else {
			var calendar = $(this).parent().parent().parent().attr('id');
		}
		var data = $(this).serialize();
	    $.post(sc_vars.ajaxurl, data, function (response) {
	   		$('#' + calendar).html(response);
			scResizeCal();
	 	}).done(function() {
	 		document.body.style.cursor = 'default';
	 	});
		return false;
	});

	$('body').on('submit', '.sc_events_form_widget', function() {

		document.body.style.cursor = 'wait';
		if( $(this).attr('id') == 'sc_event_select' ) {
			var calendar = $(this).parent().parent().parent().attr('id');
		} else {
			var calendar = $(this).parent().parent().parent().parent().attr('id');
		}
		var data = $(this).serialize();
		$.post(sc_vars.ajaxurl, data, function (response) {
			$('#' + calendar).html(response);
			scResizeCal();
		}).done(function() {
			document.body.style.cursor = 'default';
		});
		return false;
	});


	function scResizeCal() {
		var winwidth = $(window).width();
		var winheight = $(window).height();

		if(winwidth <= 480) {
			$('#sc_calendar th').each(function() {
				if( $(this).text().length > 3 ) {
					var day = $(this).text().substr(0, 3);
					$(this).text(day);
				}
			});
			if( ! $('.sc_events_calendar').hasClass('sc_small') ) {
				$('#sc_event_select').hide();
				$('#sc_event_nav_wrap').css('width', '50%');
				$('#sc_event_select').css('width', '50%');
			} else {
				$('#sc_event_select').show();
				$('#sc_event_nav_wrap').css('width', '100%');
				$('#sc_event_select').css('width', '100%');
			}
		} else {
			$('#sc_event_select').show();
			if( ! $('.sc_events_calendar').hasClass('sc_small') ) {
				$('#sc_event_nav_wrap').css('width', '33%');
				$('#sc_event_select').css('width', '33%');
			}
		}
	}

	// resize on load if needed
	scResizeCal();

	$(window).resize(function() {
		scResizeCal();
	});

});