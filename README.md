[CSS Compressor](http://www.codenothing.com/css-compressor/)
========================

PHP Based CSS Compressor.


Usage
-----

	$CSSC = new CSSCompressor( $css, $options );
	echo $CSSC->css;


Option Handling
---------------

The compressor has an option function attached to it, that has multiple functionalities. If no arguments are passed in,
the entire options array is returned. If a single name argument is passed, then the value of that key name in the options
array is returned. If both a name and value are passed, then that value is set to it's corresponding key in the array.

	// Returns entire options array
	$options = $CSSC->option();

	// Returns the readability value
	$readability = $CSSC->option( 'readability' );

	// Sets the readability to non-readable
	$CSSC->option( 'readability', CSSCompression::READ_NONE );


Additionally, a reset function is provided to revert back to base options (decided at runtime).

	// Resets options to original values
	$CSSC->reset();


Singleton Instances
-------------------

Yes the compressor provides a singleton access method, but use it wisely.

	$CSSC = CSSCompression::getInstance();


Readability
-----------

The compressor class provides static integers that map to the internal readability values

	CSSCompression::READ_MAX // Maximum Readability
	CSSCompression::READ_MED // Medium readability of output
	CSSCompression::READ_MIN // Minimal readability of output
	CSSCompression::READ_NONE // No readability of output (full compression into single line)


Credits
--------
[Corey Hart](http://www.codenothing.com) - Creator

[Martin Zvarík](http://www.teplaky.net/) - Pointed out the url and empty definition bug.

[Phil DeJarnett](http://www.overzealous.com/) - Pointed out splitting(and numerous other) problems

Julien Deniau - Pointed out escaped characters issue
