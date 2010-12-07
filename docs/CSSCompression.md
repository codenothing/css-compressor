CSSCompression
==============

Below is a description of all public access points to the compressor.


const bool DEV
--------------

Signifies development mode. When true, back door access to all subclasses is enabled. Should always be false in production.


public static array defaults
----------------------------

Default settings for every instance.


const int READ_MAX, int READ_MED, int READ_MIN, int READ_NONE
-------------------------------------------------------------

Readability constants. Tells what format to return the css back after compression.


Getters
=======

This is the list of readable vars on any given instance

 - string **css**: Contains the compressed result of the last compression ran.
 - string **mode**: Contains the current mode of the instance
 - array **options**: Contains the complete array of currently defined options
 - array **stats**: Contains the result stats of the last compression ran.
 - string **(option-name)**: Contains the value of that option **name**.


Setters
=======

Currently, you can only directly set options

 - string options, array value: Merge an array of options with the current defaults
 - string name, mixed value: Set the option **name** with the **value**.



public function __construct( [ mixed $css = NULL, mixed $options = NULL ] )
---------------------------------------------------------------

Builds the subclasses first, then does one of the following

 - Passing a single string argument that is the name of a mode, sets the mode for this instance.

 - Passing a single array of options argument will merge those with the defaults for this instance.

 - Passing a single long string argument, that is not the name of a mode, will run compression with the defaults set.

 - Passing a long string argument, and a mode name argument, sets the mode's options, and then runs compression on the css string.

 - Passing a long string argument, and an array of options argument, merges those with default options, and runs compression on the css string.

Here's a few examples

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


public function mode( string $mode = NULL )
-------------------------------------------

Sets the mode of the instance.

	// Set this instance to 'sane' mode
	$CSSC->modes( 'sane' );


public static function modes( [ mixed $mode = NULL, array $config = NULL ] )
----------------------------------------------------------------------------

Mode configuration, any one of the following combination of arguments is allowed

 - Passing no arguments returns the entire array of modes.

 - Passing only a string mode argument returns that modes configuration.

 - Passing a string mode argument, and an array config argument sets that config to the mode.

 - Passing a single array argument merges a set of modes into the configured set

Here's a few demo examples

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

**NOTE:** When an instance congures itself to a mode, it sets every option to true, and expects the mode configuration to tell it what is false.


public function option( [ mixed $name = NULL, mixed $value = NULL )
-------------------------------------------------------------------

Custom option handling, any one of the following may happen

 - Passing no arguments returns the entire array of options currently set.

 - Passing only a string name argument returns the value for that option.

 - Passing a single array argument merges those into the current options of the instance.

 - Passing a string name argument, and a value argument sets the value to it's corresponding option name.

Here's a few examples.

	// Get the entire options array for this instance
	$options = $CSSC->option();
	
	// Get the current readability value for this instance
	$readability = $CSSC->option( 'readability' );
	
	// Merge a set of options into the current instance
	$CSSC->option( array( 'organize' => false, 'readability' => CSSCompression::READ_MAX ) );
	
	// Set the readability of the current object to full
	$CSSC->option( 'readability', CSSCompression::READ_MAX );


public function compress( string $css = NULL, [ mixed $options = NULL ] )
-------------------------------------------------------------------------

Compresses the given string with the given options/mode. $options can be the name of a mode, or an array of options.

	// Compress the css passed
	$compressed = $CSSC->comrpess( $css );

	// Compress the css in 'sane' mode
	$compressed = $CSSC->comrpess( $css, 'sane' );

	// Compress the css with a custom set of options
	$compressed = $CSSC->comrpess( $css, array( 'readability' => CSSCompression::READ_MAX ) );


public static function express( string $css = NULL, [ mixed $options = NULL ] )
-------------------------------------------------------------------------------

Use's it's own singleton instance to return compressed css sheets.  $options can be the name of a mode, or an array of options.

	// Compress the css passed
	$compressed = CSSCompression::express( $css );

	// Compress the css in 'sane' mode
	$compressed = CSSCompression::express( $css, 'sane' );

	// Compress the css with a custom set of options
	$compressed = CSSCompression::express( $css, array( 'readability' => CSSCompression::READ_MAX ) );


public function reset()
-----------------------

Cleans out compression instance, all of it's subclasses, and resets options back to their defaults.

	// Reset this instance to it's defaults
	$CSSC->reset();


public function flush()
-----------------------

Cleans out class vars.

	// Flush out compression variables
	$CSSC->flush();


public static function getInstance( [ string name = NULL ] )
------------------------------------------------------------

Returns a singleton instance of the compressor

	// Get a singleton instance
	$CSSC = CSSCompression::getInstance();

	// Get the store 'rose' singleton instance
	$CSSC = CSSCompression::getInstance( 'rose' );


public static function getJSON( string $file )
----------------------------------------------

Pulls the contents of the $file, does some quick comment stripping, then returns a json decoded hash. Mainly for internal use.

	$json = CSSCompression::getJSON( $filepath );
