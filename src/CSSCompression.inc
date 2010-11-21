<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
error_reporting( E_ALL );

// Controller handles all subclass loading
require( dirname(__FILE__) . '/lib/Control.php' );


Class CSSCompression
{
	/**
	 * WARNING: This should ALWAYS BE FALSE in production
	 * When DEV is true, backdoor access to private methods is opened.
	 * Only used for unit testing and development.
	 */
	const DEV = true;

	/**
	 * The default set of options for every instance.
	 */
	public static $defaults = array(
		// Converts long color names to short hex names
		// (aliceblue -> #f0f8ff)
		'color-long2hex' => true,

		// Converts rgb colors to hex
		// (rgb(159,80,98) -> #9F5062, rgb(100%) -> #FFFFFF)
		'color-rgb2hex' => true,

		// Converts long hex codes to short color names (#f5f5dc -> beige)
		// Only works on latest browsers, careful when using
		'color-hex2shortcolor' => false,

		// Converts long hex codes to short hex codes
		// (#44ff11 -> #4f1)
		'color-hex2shorthex' => true,

		// Converts font-weight names to numbers
		// (bold -> 700)
		'fontweight2num' => true,

		// Removes zero decimals and 0 units
		// (15.0px -> 15px || 0px -> 0)
		'format-units' => true,

		// Lowercases html tags from list
		// (BODY -> body)
		'lowercase-selectors' => true,

		// Add space after pseduo selectors, for ie6
		// (a:first-child{ -> a:first-child {)
		'pseduo-space' => false,

		// Compresses single defined multi-directional properties
		// (margin: 15px 25px 15px 25px -> margin:15px 25px)
		'directional-compress' => true,

		// Combines multiply defined selectors and details
		// (p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;})
		// (p{color:blue;} a{color:blue;} -> p,a{color:blue;})
		'organize' => true,

		// Combines color/style/width properties
		// (border-style:dashed;border-color:black;border-width:4px; -> border:4px dashed black)
		'csw-combine' => true,

		// Combines cue/pause properties
		// (cue-before: url(before.au); cue-after: url(after.au) -> cue:url(before.au) url(after.au))
		'auralcp-combine' => true,

		// Combines margin/padding directionals
		// (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -> margin:10px 5px 4px 1px;)
		'mp-combine' => true,

		// Combines border directionals
		// (border-top|right|bottom|left:1px solid black -> border:1px solid black)
		'border-combine' => true,

		// Combines font properties
		// (font-size:12pt; font-family: arial; -> font:12pt arial)
		'font-combine' => true,

		// Combines background properties
		// (background-color: black; background-image: url(bgimg.jpeg); -> background:black url(bgimg.jpeg))
		'background-combine' => true,

		// Combines list-style properties
		// (list-style-type: round; list-style-position: outside -> list-style:round outside)
		'list-combine' => true,

		// Removes the last semicolon of a property set
		// ({margin: 2px; color: blue;} -> {margin: 2px; color: blue})
		'unnecessary-semicolons' => true,

		// Readibility of Compressed Output, Defaults to none
		'readability' => 0,
	);

	/**
	 * Readability Constants
	 *
	 * @param (int) READ_MAX: Maximum readability of output
	 * @param (int) READ_MED: Medium readability of output
	 * @param (int) READ_MIN: Minimal readability of output
	 * @param (int) READ_NONE: No readability of output (full compression into single line)
	 */ 
	const READ_MAX = 3;
	const READ_MED = 2;
	const READ_MIN = 1;
	const READ_NONE = 0;

	/**
	 * Modes are predefined sets of configuration for referencing
	 * When creating a mode, all options are set to true, and the mode array
	 * defines which options are to be false
	 *
	 * @mode safe: Keeps selector and detail order, and prevents hex to shortname conversion
	 * @mode medium: Prevents hex to shortname conversion
	 * @mode small: Full compression
	 */
	public static $modes = array(
		'safe' => array(
			'color-hex2shortcolor' => false,
			'orgainze' => false,
		),
		'medium' => array(
			'color-hex2shortcolor' => false,
			'pseduo-space' => false
		),
		'small' => array(
			'pseduo-space' => false
		),
	);

	/**
	 * Controller of all subclasses
	 */
	private $Control;

	/**
	 * Builds the subclasses, runs the compression if css passed, and merges options
	 *
	 * @param (string) css: CSS to compress on initialization if needed
	 * @param (array) options: Array of preferences to override the defaults
	 */ 
	public function __construct( $css = NULL, $options = NULL ) {
		$this->Control = new CSSCompression_Control( $this );

		// Autorun against css passed
		if ( $css ) {
			// Allow passing options/mode only
			if ( is_array( $css ) || ( strlen( $css ) < 15 && array_key_exists( $css, self::$modes ) ) ) {
				$this->Control->Option->merge( $options );
			}
			else {
				$this->css = $this->Control->compress( $css, $options );
			}
		}
		// Merge passed options
		else if ( $options ) {
			$this->Control->Option->merge( $options );
		}
	}

	/**
	 * (Proxy function) Control access to properties
	 *
	 *	- Getting stats/_mode/css returns the current value of that property
	 *	- Getting options will return the current full options array
	 *	- Getting anything else returns that current value in the options array or NULL
	 *
	 * @param (string) name: Name of property that you want to access
	 */ 
	public function __get( $name ) {
		return $this->Control->get( $name );
	}

	/**
	 * (Proxy function) The setter method only allows 
	 * access to setting values in the options array
	 *
	 * @param (string) name: Key name of the option you want to set
	 * @param (any) value: Value of the option you want to set
	 */ 
	public function __set( $name, $value ) {
		return $this->Control->set( $name, $value );
	}

	/**
	 * (Proxy function) Merges a predefined set options
	 *
	 * @param (string) mode: Name of mode to use.
	 */
	public function mode( $mode = NULL ) {
		return $this->Control->Option->merge( $mode );
	}

	/**
	 * (Proxy function) Maintainable access to the options array
	 *
	 *	- Passing no arguments returns the entire options array
	 *	- Passing a single name argument returns the value for the option
	 * 	- Passing both a name and value, sets the value to the name key, and returns the value
	 *	- Passing an array will merge the options with the array passed, for object like extension
	 *
	 * @param (string|array) name: The key name of the option
	 * @param (any) value: Value to set the option
	 */
	public function option( $name = NULL, $value = NULL ) {
		return $this->Control->Option->option( $name, $value );
	}

	/**
	 * (Proxy function) Run compression on the sheet passed
	 *
	 * @param (string) css: Stylesheet to be compressed
	 * @param (array|string) options: Array of options or mode to use.
	 */
	public function compress( $css = NULL, $options = NULL ) {
		return $this->Control->compress( $css, $options );
	}

	/**
	 * Static access for direct compression
	 *
	 * @instance expressi: Use a separate instance from singleton access 
	 * @param (string) css: Stylesheet to be compressed
	 * @param (array|string) options: Array of options or mode to use.
	 */
	private static $expressi;
	public static function express( $css = NULL, $options = NULL ) {
		if ( ! self::$expressi ) {
			self::$expressi = new CSSCompression();
		}

		self::$expressi->reset();
		return self::$expressi->compress( $css, $options );
	}

	/**
	 * Cleans out compressor and it's subclasses to defaults
	 *
	 * @params none
	 */
	public function reset(){
		$this->Control->reset();
	}

	/**
	 * The Singleton access method (for those that want it)
	 *
	 * @instance instance: Saved instance of CSSCompression
	 */
	private static $instance;
	public static function getInstance(){
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Reads JOSN based files, strips comments and converts to array
	 *
	 * @param (string) file: Filename
	 */
	public static function getJSON( $file ) {
		// Assume helper file if full path not given
		$file = $file[ 0 ] == '/' ? $file : dirname(__FILE__) . '/helpers/' . $file;

		// Strip comments
		$json = preg_replace(
			array( "/\/\*(.*?)\*\//s", "/(\t|\s)+\/\/.*/" ),
			array( '', '' ),
			file_get_contents( $file )
		);

		// Decode json
		$json = json_decode( $json, true );

		// Check for errors
		if ( $json === NULL ) {
			// JSON Errors, taken directly from http://php.net/manual/en/function.json-last-error.php
			switch( json_last_error() ) {
				case JSON_ERROR_NONE:
					$json = new Exception( 'JSON Error - No error has occurred' );
					break;
				case JSON_ERROR_DEPTH:
					$json = new Exception( 'JSON Error - The maximum stack depth has been exceeded' );
					break;
				case JSON_ERROR_CTRL_CHAR:
					$json = new Exception( 'JSON Error - Control character error, possibly incorrectly encoded' );
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$json = new Exception( 'JSON Error - Invalid or malformed JSON' );
					break;
				case JSON_ERROR_SYNTAX:
					$json = new Exception( 'JSON Error - Syntax error' );
					break;
				case JSON_ERROR_UTF8:
					$json = new Exception( 'JSON Error - Malformed UTF-8 characters, possibly incorrectly encoded' );
					break;
				default:
					$json = new Exception( 'Unknown JSON Error' );
					break;
			}
		}

		// Good to go
		return $json;
	}

	/**
	 * Backdoor access to subclasses
	 * ONLY FOR DEVELOPMENT/TESTING.
	 *
	 * @param (string) class: Name of the focus class
	 * @param (string) method: Method function to call
	 * @param (array) args: Array of arguments to pass in
	 */
	public function access( $class = NULL, $method = NULL, $args = NULL ) {
		if ( ! self::DEV ) {
			throw new Exception( "CSSCompression is not in development mode." );
		}
		else if ( $class === NULL || $method === NULL || $args === NULL ) {
			throw new Exception( "Invalid Access Call." );
		}
		else if ( ! is_array( $args ) ) {
			throw new Exception( "Expecting array of arguments." );
		}

		return $this->Control->access( $class, $method, $args );
	}
};

?>
