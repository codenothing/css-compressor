<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
error_reporting( E_ALL );

$lib = dirname(__FILE__) . '/lib/';
require( $lib . 'Setup.php' );
require( $lib . 'Option.php' );
require( $lib . 'Format.php' );
require( $lib . 'Individuals.php' );
require( $lib . 'Numeric.php' );
require( $lib . 'Color.php' );
require( $lib . 'Selectors.php' );
require( $lib . 'Combine.php' );
require( $lib . 'Organize.php' );
require( $lib . 'Cleanup.php' );


Class CSSCompression extends CSSCompression_Cleanup
{
	/**
	 * WARNING: This should ALWAYS BE FALSE in production
	 * When DEV is true, backdoor access to private methods is opened.
	 * Only used for unit testing and development.
	 */
	const DEV = true;

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
			'multiple-selectors' => false,
			'multiple-details' => false,
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

		// Combines multiply defined selectors
		// (p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;})
		'multiple-selectors' => true,

		// Combines selectors with same details
		// (p{color:blue;} a{color:blue;} -> p,a{color:blue;})
		'multiple-details' => true,

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
	 * Extend the default options with user defined POST vars.
	 *
	 * @param (string) css: CSS to compress on initialization if needed
	 * @param (array) prefs: Array of preferences to override the defaults
	 */ 
	public function __construct( $css = NULL, $prefs = NULL ) {
		// Compression trigger is up the class tree
		parent::__construct( $css, $prefs );
	}

	/**
	 * Only allow access to stats/css/media/options/_mode
	 *
	 *	- Getting stats/media/css returns the current value of that class var
	 *	- Getting option will return the current full options array
	 *	- Getting anything else returns that current value in the options array or NULL
	 *
	 * @param (string) name: Name of variable that you want to access
	 */ 
	public function __get( $name ) {
		if ( $name === 'stats' || $name === 'media' || $name === 'css' || $name === 'options' || $name == '_mode' ) {
			return $this->$name;
		}
		else if ( isset( $this->options[ $name ] ) ) {
			return $this->options[ $name ];
		}
		else {
			return NULL;
		}
	}

	/**
	 * The setter method only allows access to setting values in the options array
	 *
	 * @params (string) name: Key name of the option you want to set
	 * @params (any) value: Value of the option you want to set
	 */ 
	public function __set( $name, $value ) {
		// Allow for passing array of options to merge into current ones
		if ( $name === 'option' && is_array( $value ) ) {
			$this->mergeOptions( $value );
			return $this->options;
		} else {
			$this->options[ $name ] = $value;
			return $this->options[ $name ];
		}
	}

	/**
	 * The Singleton access method (for those that want it)
	 *
	 * @params none.
	 */
	private static $instance;
	public static function getInstance(){
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Backdoor access to private methods in parent classes.
	 * ONLY FOR DEVELOPMENT.
	 *
	 * @param (string) class: Name of the focus class
	 * @param (array) config: Contains name reference and test arguments
	 */
	public function access( $class = NULL, $config = NULL ) {
		if ( ! self::DEV ) {
			throw new Exception("CSSCompression is not in development mode.");
		}
		else if ( $class === NULL || $config === NULL ) {
			return NULL;
		}

		return parent::access( $class, $config );
	}
};

?>
