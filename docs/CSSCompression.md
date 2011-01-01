Class CSSCompression
====================

Below is a description of all public access points to the compressor.


const *bool* VERSION
--------------------

Release version


const *bool* DATE
-----------------

Release Date


const *bool* DEV
----------------

Signifies development mode. When true, back door access to all subclasses is enabled. Should always be false in production.


const *bool* TOKEN
------------------

Special marker that gets injected and removed into the stylesheet during compression. Change this if it exists in your sheet.


public static *array* defaults
------------------------------

Default settings for every instance.


const *int* READ_MAX, const *int* READ_MED, const *int* READ_MIN, const *int* READ_NONE
---------------------------------------------------------------------------------------

Readability constants. Tells what format to return the css back after compression.


Getters
=======

This is the list of readable vars on any given instance

 - *string* **css**: Contains the compressed result of the last compression ran.
 - *string* **mode**: Contains the current mode of the instance
 - *array* **options**: Contains the complete array of currently defined options
 - *array* **stats**: Contains the result stats of the last compression ran.
 - *string* **(option-name)**: Contains the value of that option **name**.

Usage:

	// Print out compressed css
	echo $CSSC->css;

	// Print out the current mode
	echo $CSSC->mode;

	// Print out list of options
	print_r( $CSSC->options );

	// Print out result stats
	print_r( $CSSC->stats );

	// Print out a single options value
	echo $CSSC->readability;


Setters
=======

Currently, you can only directly set options

 - *string* **options**, *array* **value**: Merge an array of options with the current defaults
 - *string* **name**, *mixed* **value**: Set the option **name** with the **value**.

Usage:

	// Merge a custom set of options into the defined set
	// Remember that it doesn't set, just merges
	$CSSC->options = array( 'readability' => CSSCompression::READ_MAX, 'organize' => true );

	// Set a single options value
	$CSSC->readability = CSSCompression::READ_MAX;
	$CSSC->organize = true;



public function __construct( [ *mixed* $css = NULL, *mixed* $options = NULL ] )
-------------------------------------------------------------------------------

Builds the subclasses first, then does one of the following

 - Passing a single string argument that is the name of a mode, sets the mode for this instance.

 - Passing a single array of options argument will merge those with the defaults for this instance.

 - Passing a single long string argument, that is not the name of a mode, will run compression with the defaults set.

 - Passing a long string argument, and a mode name argument, sets the mode's options, and then runs compression on the css string.

 - Passing a long string argument, and an array of options argument, merges those with default options, and runs compression on the css string.

Usage:

	// Create an instance in 'sane' mode
	$CSSC = new CSSCompression( 'sane' );
	
	// Create an instance with a custom set of options
	$CSSC = new CSSCompression( array( 'readability' => CSSCompression::READ_MAX ) );
	
	// Creates a new instance, then runs compression on the css passed
	$CSSC = new CSSCompression( $css );
	echo $CSSC->css;
	
	// Creates a new instance in 'sane' mode, then runs compression on the css passed
	$CSSC = new CSSCompression( $css, 'sane' );
	echo $CSSC->css;
	
	// Creates a new instance with a custom set of options, then runs compression on the css passed
	$CSSC = new CSSCompression( $css, array( 'readability' => CSSCompression::READ_MAX ) );
	echo $CSSC->css;


*array* public function mode( *string* $mode = NULL )
-----------------------------------------------------

Sets the mode of the instance.

	// Set this instance to 'sane' mode
	$CSSC->mode( 'sane' );


*array* public static function modes( [ *mixed* $mode = NULL, *array* $config = NULL ] )
----------------------------------------------------------------------------------------

Mode configuration, any one of the following combination of arguments is allowed

 - Passing no arguments returns the entire array of modes.

 - Passing only a string mode argument returns that modes configuration.

 - Passing a string mode argument, and an array config argument sets that config to the mode.

 - Passing a single array argument merges a set of modes into the configured set

Usage:

	// Returns the entire list of modes
	$modes = CSSCompression::modes();
	
	// Returns 'sane' mode configuration
	$sane = CSSCompression::modes( 'sane' );
	
	// Add 'rose' mode to the list of modes
	CSSCompression::modes( 'rose', array( 'organize' => false, 'readability' => CSSCompression::READ_MAX ) );
	
	// Add 'rose' and 'blue' mode configurations to set of modes
	CSSCompression::modes(array(
		'rose' => array( 'organize' => false, 'readability' => CSSCompression::READ_MAX ),
		'blue' => array( 'rm-multi-define' => false, 'readability' => CSSCompression::READ_NONE )
	));

**NOTE:** When an instance configures itself to a mode, it sets every option to true, and expects the mode configuration to tell it what is false.


*mixed* public function option( [ *mixed* $name = NULL, *mixed* $value = NULL ] )
---------------------------------------------------------------------------------

Custom option handling, any one of the following may happen

 - Passing no arguments returns the entire array of options currently set.

 - Passing only a string name argument returns the value for that option.

 - Passing a single array argument merges those into the current options of the instance.

 - Passing a string name argument, and a value argument sets the value to it's corresponding option name.

Usage:

	// Get the entire options array for this instance
	$options = $CSSC->option();
	
	// Get the current readability value for this instance
	$readability = $CSSC->option( 'readability' );
	
	// Merge a set of options into the current instance
	$CSSC->option( array( 'organize' => false, 'readability' => CSSCompression::READ_MAX ) );
	
	// Set the readability of the current object to full
	$CSSC->option( 'readability', CSSCompression::READ_MAX );


*string* public function compress( *string* $css = NULL, [ *mixed* $options = NULL ] )
--------------------------------------------------------------------------------------

Compresses the given string with the given options/mode. $options can be the name of a mode, or an array of options.

	// Compress the css passed
	$compressed = $CSSC->comrpess( $css );

	// Compress the css in 'sane' mode
	$compressed = $CSSC->comrpess( $css, 'sane' );

	// Compress the css with a custom set of options
	$compressed = $CSSC->comrpess( $css, array( 'readability' => CSSCompression::READ_MAX ) );


*string* public static function express( *string* $css = NULL, [ *mixed* $options = NULL ] )
--------------------------------------------------------------------------------------------

Use's it's own singleton instance to return compressed css sheets.  $options can be the name of a mode, or an array of options.

	// Compress the css passed
	$compressed = CSSCompression::express( $css );

	// Compress the css in 'sane' mode
	$compressed = CSSCompression::express( $css, 'sane' );

	// Compress the css with a custom set of options
	$compressed = CSSCompression::express( $css, array( 'readability' => CSSCompression::READ_MAX ) );


*bool* public function reset()
------------------------------

Cleans out compression instance, all of it's subclasses, and resets options back to their defaults.

	// Reset this instance to it's defaults
	$CSSC->reset();


*bool* public function flush()
------------------------------

Cleans out class vars.

	// Flush out compression variables
	$CSSC->flush();


*object* public static function getInstance( [ *string* name = NULL ] )
-----------------------------------------------------------------------

Returns a singleton instance of the compressor

	// Get a singleton instance
	$CSSC = CSSCompression::getInstance();

	// Get the stored 'rose' singleton instance
	$CSSC = CSSCompression::getInstance( 'rose' );


*array* public static function getJSON( *string* $file )
--------------------------------------------------------

Pulls the contents of the $file, does some quick comment stripping, then returns a json decoded hash. Mainly for internal use.

	$json = CSSCompression::getJSON( "/path/to/my/file.json" );
