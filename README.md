# Deprecated

This project is no longer being maintained. It has been rewritten in javascript and now lives at [CSSCompressor](https://github.com/codenothing/CSSCompressor)

----


[CSS Compressor](http://www.codenothing.com/css-compressor/)
============================================================

CSSCompression is a PHP based CSS minifier that analyzes stylesheets for various compressions.
It finds possible CSS shorthand techniques for combination of properties.


Usage
-----

	require( 'src/CSSCompression.php' );
	$compressed = CSSCompression::express( $css, 'sane' );


Or, if you need to run it multiple times

	$CSSC = new CSSCompression( 'sane' );
	$compressed = $CSSC->compress( $css );


Modes
-----

Modes are pre-defined sets of options that can be set by passing in the mode name.

 - **safe**: (99% safe) Safe mode does zero combinations or organizing. It's the best mode if you use a lot of hacks.

 - **sane**: (90% safe) Sane mode does most combinations(multiple long hand notations to single shorthand), but still keeps most declarations in their place.

 - **small**: (65% safe) Small mode reorganizes the whole sheet, combines as much as it can, and will break most comment hacks. 

 - **full**: (64% safe) Full mode does everything small does, but also converts hex codes to their short color name alternatives.


Here's a few different ways to initiate a mode.

	// Express with safe mode
	$compressed = CSSCompression::express( $css, 'safe' );

	// Creating new instance with sane mode
	$CSSC = new CSSCompression( 'sane' );

	// Setting an instance with small mode
	$CSSC->mode( 'small' );

	// Or compressing with the current instance, and setting full mode
	$compressed = $CSSC->compress( $css, 'full' );
	


Singleton Instances
-------------------

Yes the compressor provides singleton access(separate from express), but use it wisely.

	$CSSC = CSSCompression::getInstance();

	// Or, if you want to keep named instances
	$rose_instance = CSSCompression::getInstance('rose');
	$blue_instance = CSSCompression::getInstance('blue');


Option Handling
---------------

The compressor has an option function with multiple functionalities.

 - If no arguments are passed in, the entire options array is returned.

 - If a single name argument is passed, then the value of that key name in the options array is returned.

 - If both a name and value are passed, then that value is set to it's corresponding key in the array.

Usage

	// Returns entire options array
	$options = $CSSC->option();

	// Returns the readability option value
	$readability = $CSSC->option( 'readability' );

	// Sets the readability to non-readable
	$CSSC->option( 'readability', CSSCompression::READ_NONE );

Alternatively, you can use direct access methods

	// Direct access to options array
	$options = $CSSC->options;
	
	// Direct access to readability option value
	$readability = $CSSC->readability;
	
	// Direct setting of readability value
	$CSSC->readability = CSSCompression::READ_NONE;

Additionally, a reset function is provided to revert back to base options (decided at runtime).

	// Resets options to original values
	$CSSC->reset();



Readability
-----------

The compressor class provides static integers that map to the internal readability values

	CSSCompression::READ_MAX // Maximum Readability
	CSSCompression::READ_MED // Medium readability of output
	CSSCompression::READ_MIN // Minimal readability of output
	CSSCompression::READ_NONE // No readability of output (full compression into single line)

	// To set maximum readability (Assuming you have your own instance)
	$CSSC->option( 'readability', CSSCompression::READ_MAX );

	// Or, just pass it in as another option
	$options = array(
		'readability' => CSSCompression::READ_MAX,
		// Other options ...
	);
	// Get full readability through express
	$compressed = CSSCompression::express( $css, $options );


Contributors
------------
[Corey Hart](http://www.codenothing.com) - Creator

[Martin Zvarík](http://www.teplaky.net/) - Pointed out the url and empty definition bug.

[Phil DeJarnett](http://www.overzealous.com/) - Pointed out splitting(and numerous other) problems

[Stoyan Stefanov](http://www.phpied.com/) - [At rules writeup](http://www.phpied.com/css-railroad-diagrams/) and test suite help.

[Julien Deniau](http://www.jeuxvideo.fr/) - Pointed out escaped characters issue

[Olivier Gorzalka](http://clearideaz.com/) - Strict error warnings cleanup
