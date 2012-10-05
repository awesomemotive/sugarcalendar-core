jQuery(document).ready(function($) {
	if($('.sc_meta_box_row .sc_datepicker').length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		$('.sc_datepicker').datepicker({dateFormat: dateFormat});
	}	
	$('.sc_event_recurring').change(function() {
		var selected = $(':selected', this).val();
		if( selected != 'none' ) {
			$('.sc_recurring_help').show();
		} else {
			$('.sc_recurring_help').hide();
		}
	});
});