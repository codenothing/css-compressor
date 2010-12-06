/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
jQuery(function(){
	var options = jQuery('div.options input'), select = jQuery("select[name=mode]"), modes = window.CSSCompressionModes;

	// Style the options
	jQuery("div.options label:odd").addClass('odd');

	// Only open them in custom mode
	select.change(function(){
		var mode = jQuery( this ).val();
		if ( mode != 'custom' ) {
			options.each(function(){
				this.checked = ! modes[ mode ].hasOwnProperty( this.name );
			});
			select.val( mode );
		}
	});

	// Switch to custom on any option change
	options.change(function(){
		select.val('custom');
	});

	// Show framed results after submit
	jQuery("form").one( 'submit', function(){
		jQuery('iframe').slideDown();
	});

	// Trigger option change to first one
	select.trigger('change');
});
