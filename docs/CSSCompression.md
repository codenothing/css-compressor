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


public static array modes
-------------------------

Group of predefined sets of options


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


public function mode( string $mode = NULL )
-------------------------------------------

Sets the mode of the instance.


public function option( [ mixed $name = NULL, mixed $value = NULL )
-------------------------------------------------------------------

Custom option handling, any one of the following may happen

 - Passing no arguments returns the entire array of options currently set.

 - Passing only a string name argument returns the value for that option.

 - Passing a single array argument merges those into the current options of the instance.

 - Passing a string name argument, and a value argument sets the value to it's corresponding option name.


public function compress( string $css = NULL, [ mixed $options = NULL ] )
-------------------------------------------------------------------------

Merges the given options, and compresses the given string. $options can be the name of a mode, or an array of options to merge.


public static function express( string $css = NULL, [ mixed $options = NULL ] )
-------------------------------------------------------------------------------

Use's it's own singleton instance to return compressed css sheets.  $options can be the name of a mode, or an array of options to merge.


public function reset()
-----------------------

Cleans out compression instance, all of it's subclasses, and resets options back to their defaults.


public function flush()
-----------------------

Cleans out class vars.


public static function getInstance()
------------------------------------

Returns a singleton instance of the compressor


public static function getJSON( string $file )
----------------------------------------------

Pulls the contents of the $file, does some quick comment stripping, then returns a json decoded hash.
