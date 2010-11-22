jQuery(function(){
	var options = jQuery('div.options');

	// Style the options
	options.find("label:odd").addClass('odd');

	// Only open them in custom mode
	jQuery("select[name=mode]").change(function(){
		if ( jQuery( this ).val() == 'custom' ) {
			options.slideDown();
		}
		else {
			options.slideUp();
		}
	});
});
