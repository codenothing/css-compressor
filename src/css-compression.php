<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

// Define path to vars directory
define( 'CSSC_VARS_DIR', dirname(__FILE__) . '/vars/' );


Class CSSCompression
{
	/**
	 * Class Variables
	 *
	 * @param (string) css: Holds compressed css string
	 * @param (array) selectors: Holds CSS Selectors
	 * @param (array) details: Holds definitions of selectors
	 * @param (array) options: Holds compression options
	 * @param (array) stats: Holds compression stats
	 * @param (boolean) media: Media is present
	 * @param (string) mode: Current compression mode state
	 */ 
	protected $css = '';
	protected $selectors = array();
	protected $details = array();
	protected $options = array();
	protected $stats = array();
	protected $media = false;
	protected $media_str = '';
	protected $import_str = '';
	protected $_mode = '';

	/**
	 * Look behind regex's to check for escaped characters
	 *
	 * @param (regex) r_semicolon: Checks for semicolon without an escape ('/') character before it
	 * @param (regex) r_colon: Checks for colon without an escape ('/') character before it
	 * @param (regex) r_space: Checks for space without an escape ('/') character before it
	 */ 
	private $r_semicolon = "/(?<!\\\);/";
	private $r_colon = "/(?<!\\\):/";
	private $r_space = "/(?<!\\\)\s/";

	/**
	 * Modes are predefined sets of configuration for referencing
	 * When creating a mode, all options are set to true, and the mode array
	 * defines which options are to be false
	 *
	 * @mode safe: Keeps selector and detail order, and prevents hex to shortname conversion
	 * @mode medium: Prevents hex to shortname conversion
	 * @small: Full compression
	 */
	public static $modes = array(
		'safe' => array(
			'color-hex2shortcolor' => false,
			'multiple-selectors' => false,
			'multiple-details' => false,
		),
		'medium' => array(
			'color-hex2shortcolor' => false,
		),
		'small' => array(),
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
	 * Extend the default options with user defined POST vars.
	 *
	 * @param (string) css: CSS to compress on initialization if needed
	 * @param (array) prefs: Array of preferences to override the defaults
	 */ 
	public function __construct( $css = '', $prefs = array() ) {
		// Setup the options
		$this->resetOptions();

		// Automatically compress css if passed
		if ( $css && $css !== '' ) {
			$this->compress( $css, $prefs );
		}
		else if ( $prefs ) {
			$this->mergeOptions( $prefs );
		}
	}

	/**
	 * Only allow access to stats/css/media/options
	 *
	 *	- Getting stats/media/css returns the current value of that class var
	 *	- Getting option will return the current full options array
	 *	- Getting anything else returns that current value in the options array or NULL
	 *
	 * @param (string) name: Name of variable that you want to access
	 */ 
	public function __get( $name ) {
		if ( $name === 'stats' || $name === 'media' || $name === 'css' || $name === 'option' || $name == '_mode' ) {
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
	 * Maintainable access to the options array
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
		if ( $name === NULL ) {
			return $this->options;
		}
		else if ( is_array( $name ) ) {
			return $this->mergeOptions( $name );
		}
		else if ( $value === NULL ) {
			return $this->options[ $name ];
		}
		else {
			return $this->options[ $name ] = $value;
		}
	}

	/**
	 * Adds or sets the current instances mode
	 *
	 * @param (string) mode: Name of the mode to use
	 * @param (array) config: Array of config values to assign to a mode
	 */
	public function mode( $mode = NULL, $config = array() ) {
		if ( $config && $mode && is_array( $config ) && count( $config ) ) {
			return self::$modes[ $mode ] = $config;
		}
		else if ( $mode ) {
			return $this->mergeOptions( $mode );
		}
	}

	/**
	 * Reset's the default options
	 *
	 * @param (boolean) clear: When true, options array is cleared
	 */ 
	public function resetOptions( $clear = false ) {
		if ( $clear ) {
			$this->options = array();
			return true;
		}

		$this->options = array(
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

			// Removes multiply defined properties
			// STRONGLY SUGGESTED TO KEEP THIS TRUE
			'rm-multi-define' => true,

			// Readibility of Compressed Output, Defaults to none
			'readability' => self::READ_NONE,
		);

		// Return the reset options
		return $this->options;
	}

	/**
	 * Extend like function to merge an array of preferences into
	 * the options array.
	 *
	 * @param (array) prefs: Array of preferences to merge into options
	 */ 
	protected function mergeOptions( $prefs = array() ) {
		if ( $prefs && is_array( $prefs ) && count( $prefs ) ) {
			foreach ( $this->options as $key => $value ) {
				if ( ! isset( $prefs[ $key ] ) ) {
					continue;
				}
				else if ( $prefs[ $key ] && $prefs[ $key ] == 'on' ) {
					$this->options[ $key ] = true;
				}
				else if ( $prefs[ $key ] && $prefs[ $key ] == 'off' ) {
					$this->options[ $key ] = false;
				}
				else {
					$this->options[ $key ] = intval( $prefs[ $key ] );
				}
			}
		}
		else if ( $prefs && is_string( $prefs ) && array_key_exists( $prefs, self::$modes ) ) {
			$this->_mode = $prefs;

			// Default all to true, the mode has to force false
			foreach ( $this->options as $key => $value ) {
				if ( $key != 'readability' ) {
					$this->options[ $key ] = true;
				}
			}

			// Merge mode into options
			foreach ( self::$modes[ $prefs ] as $key => $value ) {
				$this->options[ $key ] = $value;
			}
		}

		return $this->options;
	}

	/**
	 * Centralized function to run css compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	public function compress( $css, $prefs = array() ) {
		// Start the timer
		$initialTime = array_sum( explode( ' ', microtime() ) );
		$initialSize = strlen( $css );

		// Options
		if ( $prefs ) {
			$this->mergeOptions( $prefs );
		}

		// Flush out variables
		$this->flush();
		$this->css = $css;

		// Send body through initial trimings and setup for compression methods
		$this->initialTrim();
		$this->setup();

		// Store all intial data
		$this->stats['before']['time'] = $initialTime;
		$this->stats['before']['size'] = $initialSize;
		$this->stats['before']['selectors'] = count( $this->selectors );

		// Run Compression Methods
		$this->runCompressionMethods();

		// Format css to users preference
		$this->css = $this->readability( $this->import_str, $this->options['readability'] );

		// Add media string with comments to compress seperately
		if ( $this->media_str ) {
			$this->media = true;
			$this->css = $this->media_str . $this->css;
		}

		// Run final statistics before sending back the css
		$this->runFinalStatistics();

		// Return compressed css
		return $this->css;
	}

	/**
	 * Clear class variables (but not options)
	 *
	 * @params none;
	 */ 
	protected function flush(){
		$this->css = '';
		$this->import_str = '';
		$this->media_str = '';
		$this->media = false;
		$this->selectors = array();
		$this->details = array();
		$this->stats = array(
			'before' => array(
				'props' => 0
			), 
			'after' => array(
				'props' => 0
			),
		);
	}

	/**
	 * Runs initial formatting to setup for compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	protected function initialTrim(){
		// Regex
		$search = array(
			1 => "/(\/\*|\<\!\-\-)(.*?)(\*\/|\-\-\>)/s", // Remove all comments
			2 => "/(\s+)?([,{};:>\+])(\s+)?/s", // Remove un-needed spaces around special characters
			3 => "/url\(['\"](.*?)['\"]\)/s", // Remove quotes from urls
			4 => "/;{2,}/", // Remove unecessary semi-colons
			5 => "/\s+/s", // Compress all spaces into single space
			// Leave section open for additional entries

			// Break apart elements for setup of further compression
			20 => "/{/",
			21 => "/}/",
		);

		// Replacements
		$replace = array(
			1 => ' ',
			2 => '$2',
			3 => 'url($1)',
			4 => ';',
			5 => ' ',
			// Leave section open for additional entries

			// Add new line for setup of further compression
			20 => "\n{",
			21 => "}\n",
		);

		// Run replacements
		$this->css = trim( preg_replace( $search, $replace, $this->css ) );

		// Escape out possible splitter characters within urls
		$search = array( ':', ';', ' ' );
		$replace = array( "\\:", "\\;", "\\ " );
		preg_match_all( "/url\((.*?)\)/", $this->css, $matches, PREG_OFFSET_CAPTURE );

		for ( $i=0, $imax=count( $matches[0] ); $i < $imax; $i++ ) {
			$value = 'url(' . str_replace( $search, $replace, $matches[1][$i][0] ) . ')';
			$this->css = substr_replace( $this->css, $value, $matches[0][$i][1], strlen( $matches[0][$i][0] ) );
		}
	}

	/**
	 * Setup selector and details arrays for compression methods
	 *
	 * @params none
	 */ 
	protected function setup(){
		// Seperate the element from the elements details
		$css = explode( "\n", $this->css );
		$SEL_COUNTER = 0;

		foreach ( $css as $details ) {
			$details = trim( $details );
			// Determine whether your looking at the details or element
			if ( $this->media && $details == '}' ) {
				$this->media_str .= "}\n";
				$this->media = false;
			}
			else if ( $this->media ) {
				$this->media_str .= $details;
			}
			else if ( strpos( $details, '{') === 0 ) {
				$details = substr( $details, 1, strlen( $details ) - 2 );
				$details = preg_split( $this->r_semicolon, $details );
				$parts = array();
				$storage = '';

				foreach ( $details as $line ) {
					if ( preg_match( "/^(url|@import)/i", $line ) ) {
						$storage .= $line.";";
						continue;
					}

					// Grab the property and its value
					unset( $property, $value );
					$parts = preg_split( $this->r_colon, $line, 2 );

					if ( isset( $parts[0] ) && $parts[0] != '' ) {
						$property = $parts[0];
					}

					if ( isset( $parts[1] ) && $parts[1] != '' ) {
						$value = $parts[1];
					}

					// Fail safe, remove unknown tag/elements
					if ( ! isset( $property ) || ! isset( $value ) ) {
						continue;
					}

					// Run the tag/element through each compression
					list ( $property, $value ) = $this->runSpecialCompressions( $property, $value );

					// Add counter to before stats
					$this->stats['before']['props']++;

					// Store the compressed element
					$storage .= "$property:$value;";
				}
				// Store as the last known selector
				$this->details[ $SEL_COUNTER ] = $storage;
			}
			else if ( strpos( $details, '@import' ) === 0 ) {
				// Seperate out each import string
				$arr = preg_split( $this->r_semicolon, $details );

				// Add to selector counter for details storage
				$SEL_COUNTER++;
				// Store the last entry as the selector
				$this->selectors[ $SEL_COUNTER ] = trim( $arr[ count( $arr ) - 1 ] );

				// Clear out the last entry(the actual selector) and add to the import string
				unset( $arr[ count( $arr ) - 1 ] );
				$this->import_str .= trim( implode( ';', $arr ) ) . ';';
			}
			else if ( strpos( $details, '@media' ) === 0 ){
				$this->media = true;
				$this->media_str .= $details;
			}
			else if ( $details ) {
				// Add to selector counter for details storage
				$SEL_COUNTER++;
				$this->selectors[ $SEL_COUNTER ] = $details;
			}
		}
	}

	/**
	 * Runs special unit/directional compressions
	 *
	 * @param (string) prop: CSS Property
	 * @param (string) val: Value of CSS Property
	 */ 
	protected function runSpecialCompressions( $prop, $val ) {
		// Properties should always be lowercase
		$prop = strtolower( $prop );

		// Remove uneeded side definitions if possible
		if ( $this->options['directional-compress'] && preg_match( "/^(margin|padding)/i", $prop ) ) {
			$val = $this->sidesDirectional( $val );
		}

		// Font-weight converter
		if ( $this->options['fontweight2num'] && $prop === 'font-weight' ) {
			$val = $this->fontweightConversion( $val );
		}

		// Remove uneeded decimals/units
		if ( $this->options['format-units'] ) {
			$val = $this->removeDecimal( $val );
			$val = $this->removeUnits( $val );
		}

		// Convert none vals to 0
		if ( preg_match( "/^(border|background)/i", $prop ) && $val == 'none' ) {
			$val = 0;
		}

		// Seperate out by multi-values if possible
		$parts = preg_split( $this->r_space, $val );
		foreach ( $parts as $k => $v ) {
			$parts[ $k ] = $this->runColorChanges( $v );
		}

		$val = trim( implode( ' ', $parts ) );

		// Return for list retrival
		return array( $prop, $val );
	}

	/**
	 * Converts font-weight names to numbers
	 *
	 * @param (string) val: font-weight prop value
	 */ 
	protected function fontweightConversion( $val ) {
		// Holds font weight conversions
		static $fontweight2num;
		if ( ! $fontweight2num ) {
			include( CSSC_VARS_DIR . 'fontweight2num.php' );
		}

		// All font-weights are lower-case
		$low = strtolower( $val );
		if ( isset( $fontweight2num[ $low ] ) ) {
			$val = $fontweight2num[ $low ];
		}

		// Return converted value
		return $val;
	}

	/**
	 * Finds directional compression on methods like margin/padding
	 *
	 * @param (string) val: Value of CSS Property
	 */ 
	protected function sidesDirectional( $val ) {
		// Check if side definitions already reduced down to a single definition
		if ( strpos( $val, ' ' ) === false ) {
			// Redundent, but just in case
			if ( $this->options['format-units'] ) {
				$val = $this->removeDecimal( $val );
				$val = $this->removeUnits( $val );
			}
			return $val;
		}

		// Split up each definiton
		$direction = preg_split( $this->r_space, $val );

		// Zero out and remove units if possible
		if ( $this->options['format-units'] ) {
			foreach ( $direction as &$v ) {
				$v = $this->removeDecimal( $this->removeUnits( $v ) );
			}
		}

		// 4 Direction reduction
		$count = count( $direction );
		if ( $count == 4 ) {
			if ( $direction[0] == $direction[1] && $direction[2] == $direction[3] && $direction[0] == $direction[3] ) {
				// All 4 sides are the same, combine into 1 definition
				$val = $direction[0];
			}
			else if ( $direction[0] == $direction[2] && $direction[1] == $direction[3] ) {
				// top-bottom/left-right are the same, reduce definition
				$val = $direction[0] . ' ' . $direction[1];
			}
			else {
				// No reduction found, return in initial form
				$val = implode( ' ', $direction );
			}
		}
		// 3 Direction reduction
		else if ( $count == 3 ) {
			// There can only be compression if the top(first) and bottom(last) are the same
			if ( $direction[0] == $direction[2] ) {
				$val = $direction[0] . ' ' . $direction[1];
			}
			else {
				// No reduction found, return in initial form
				$val = implode( ' ', $direction );
			}
		}
		// 2 Direction reduction
		else if ( $count == 2 ){
			if ( $direction[0] == $direction[1] ) {
				// Both directions are the same, combine into single definition
				$val = $direction[0];
			}
			else {
				// No reduction found, return in initial form
				$val = implode( ' ', $direction );
			}
		}
		// No reduction found, return in initial form
		else{
			$val = implode( ' ', $direction );
		}

		// Return the value of the property
		return $val;
	}

	/**
	 * Remove's unecessary decimal's
	 *
	 * @param (string) str: Unit found
	 */ 
	protected function removeDecimal( $str ) {
		// Find all instances of .0 and remove them
		$pattern = "/^(\d+\.0)(\%|[a-z]{2})/i";
		preg_match_all( $pattern, $str, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$search = $matches[0][$i];
			$replace = intval( $matches[1][$i] ) . $matches[2][$i];
			$str = str_ireplace( $search, $replace, $str );
		}
		return $str;
	}

	/**
	 * Removes suffix from 0 units, ie 0px; => 0;
	 *
	 * @param (string) str: Unit string
	 */ 
	protected function removeUnits( $str ) {
		// Find all instants of 0 size and remove suffix
		$pattern = "/^(\d)(\%|[a-z]{2})/i";
		preg_match_all( $pattern, $str, $matches );
		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			if ( intval( $matches[1][$i] ) == 0 ) {
				$search = $matches[0][$i];
				$replace = '0';
				$str = str_ireplace( $search, $replace, $str );
			}
		}
		return $str;
	}

	/**
	 * Converts long rgb to hex, long hex to short hex, 
	 * short hex to short name(Only works in some browsers)
	 *
	 * @param (string) val: Color to be parsed
	 */ 
	protected function runColorChanges( $val ) {
		// These vars are pulled in externally
		static $long2hex, $hex2short;

		// Transfer rgb colors to hex codes
		if ( $this->options['color-rgb2hex'] ) {
			$pattern = "/rgb\((\d{1,3}\%?(,\d{1,3}\%?,\d{1,3}\%?)?)\)/i";
			preg_match_all( $pattern, $val, $matches );

			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				$hex = '0123456789ABCDEF';
				$str = explode( ',', $matches[1][$i] );
				$new = '';

				// Incase rgb was defined with single val
				if ( ! $str ) {
					$str = array( $matches[1][$i] );
				}

				foreach ( $str as $x ) {
					$x = strpos( $x, '%' ) !== false ? intval( ( intval( $x ) / 100 ) * 255 ) : intval( $x );
					if ( $x > 255 ) {
						$x = 255;
					}
					if ( $x < 0 ) {
						$x = 0;
					}
					$new .= $hex[ ( $x - $x % 16 ) / 16 ];
					$new .= $hex[ $x % 16 ];
				}
				// Repeat hex code to complete 6 digit hex requirement for single definitions
				if ( count( $str ) == 1 ) {
					$new .= $new . $new;
				}

				// Replace within string
				$val = str_ireplace( $matches[0][$i], "#$new", $val );
			}
		}

		// Convert long color names to hex codes
		if ( $this->options['color-long2hex'] ) {
			// Static so file isn't included with every loop
			if ( ! $long2hex ) {
				include( CSSC_VARS_DIR . 'long2hex-colors.php' );
			}

			// Colornames are all lowercase
			$low = strtolower( $val );
			if ( isset( $long2hex[ $low ] ) ) {
				$val = $long2hex[ $low ];
			}
		}

		// Convert 6 digit hex codes to short color names
		if ($this->options['color-hex2shortcolor']){
			// Static so files isn't included with every loop
			if ( ! $hex2short ) {
				include( CSSC_VARS_DIR . 'hex2short-colors.php' );
			}

			// Hex codes are all lowercase
			$low = strtolower( $val );
			if ( isset( $hex2short[ $low ] ) ) {
				$val = $hex2short[ $low ];
			}
		}

		// Convert large hex codes to small codes
		if ( $this->options['color-hex2shorthex'] ) {
			$pattern = "/#([0-9a-f]{6})/i";
			preg_match_all( $pattern, $val, $matches );
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				// Use PHP's string array
				$hex = $matches[1][$i];
				if ( $hex[0] == $hex[1] && $hex[2] == $hex[3] && $hex[4] == $hex[5] ) {
					$search = $matches[0][$i];
					$replace = '#' . $hex[0] . $hex[2] . $hex[4];
					$val = str_ireplace( $search, $replace, $val );
				}
			}
		}

		// Ensure all hex codes are lowercase
		if ( preg_match( "/#([0-9a-f]{6})/i", $val ) ) {
			$val = strtolower( $val );
		}

		// Return transformed value
		return $val;
	}

	/**
	 * Runs all method logic based on order importance
	 *
	 * @params none
	 */ 
	protected function runCompressionMethods(){
		// Lowercase selectors for combining
		if ( $this->options['lowercase-selectors'] ) {
			$this->lowercaseSelectors();
		}

		// If order isn't important, run comination functions before and after compressions to catch all instances
		// Since this creates another addition of looping, keep it seperate from compressions where order is important
		if ( $this->options['multiple-selectors'] && $this->options['multiple-details'] ) {
			$this->combineMultiplyDefinedSelectors();
			$this->combineMultiplyDefinedDetails();

			foreach ( $this->details as &$value ) {
				if ($this->options['csw-combine'])		$value = $this->combineCSWproperties( $value );
				if ($this->options['auralcp-combine'])		$value = $this->combineAuralCuePause( $value );
				if ($this->options['mp-combine']) 		$value = $this->combineMPproperties( $value );
				if ($this->options['border-combine']) 		$value = $this->combineBorderDefinitions( $value );
				if ($this->options['font-combine']) 		$value = $this->combineFontDefinitions( $value );
				if ($this->options['background-combine']) 	$value = $this->combineBackgroundDefinitions($value );
				if ($this->options['list-combine']) 		$value = $this->combineListProperties($value );
			}

			$this->combineMultiplyDefinedSelectors();
			$this->combineMultiplyDefinedDetails();

			if ( $this->options['rm-multi-define'] ) {
				foreach ( $this->details as &$value ) {
					$value = $this->removeMultipleDefinitions( $value );
					$value = $this->removeEscapedURLs( $value );
				}
			}
		}
		// For when order is important, reason above
		else {
			foreach ( $this->details as &$value ) {
				if ($this->options['csw-combine'])		$value = $this->combineCSWproperties( $value );
				if ($this->options['auralcp-combine'])		$value = $this->combineAuralCuePause( $value );
				if ($this->options['mp-combine']) 		$value = $this->combineMPproperties( $value );
				if ($this->options['border-combine']) 		$value = $this->combineBorderDefinitions( $value );
				if ($this->options['font-combine']) 		$value = $this->combineFontDefinitions( $value );
				if ($this->options['background-combine']) 	$value = $this->combineBackgroundDefinitions( $value );
				if ($this->options['list-combine']) 		$value = $this->combineListProperties( $value );
				if ($this->options['rm-multi-define']) 		$value = $this->removeMultipleDefinitions( $value );
				$value = $this->removeEscapedURLs( $value );
			}
		}

		// Kill the last semicolon
		if ( $this->options['unnecessary-semicolons'] ) {
			$this->removeUnnecessarySemicolon();
		}
	}

	/**
	 * Converts selectors like BODY => body, DIV => div
	 *
	 * @params none
	 */ 
	protected function lowercaseSelectors(){
		foreach ( $this->selectors as &$selector ) {
			preg_match_all( "/([^a-zA-Z])?([a-zA-Z]+)/i", $selector, $matches, PREG_OFFSET_CAPTURE );
			for ( $i = 0, $imax = count( $matches[0] ); $i < $imax; $i++ ) {
				if ( $matches[1][$i][0] !== '.' && $matches[1][$i][0] !== '#' ) {
					$match = $matches[2][$i];
					$selector = substr_replace( $selector, strtolower( $match[0] ), $match[1], strlen( $match[0] ) );
				}
			}
		}
	}

	/**
	 * Combines multiply defined selectors by merging the definitions,
	 * latter definitions overide definitions at top of file
	 *
	 * @params none
	 */ 
	protected function combineMultiplyDefinedSelectors(){
		$max = array_pop( array_keys( $this->selectors ) ) + 1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( ! isset( $this->selectors[ $i ] ) ) {
				continue;
			}

			for ( $k = $i + 1; $k < $max; $k++ ) {
				if ( ! isset( $this->selectors[ $k ] ) ) {
					continue;
				}

				if ( $this->selectors[ $i ] == $this->selectors[ $k ] ) {
					$this->details[ $i ] .= $this->details[ $k ];
					unset( $this->selectors[ $k ], $this->details[ $k ] );
				}
			}
		}
	}

	/**
	 * Combines multiply defined details by merging the selectors
	 * in comma seperated format
	 *
	 * @params none
	 */ 
	protected function combineMultiplyDefinedDetails(){
		$max = array_pop( array_keys( $this->selectors ) ) + 1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( ! isset( $this->selectors[ $i ] ) ) {
				continue;
			}

			$arr = preg_split( $this->r_semicolon, $this->details[ $i ] );
			for ( $k = $i + 1; $k < $max; $k++ ) {
				if ( ! isset( $this->selectors[ $k ] ) ) {
					continue;
				}

				$match = preg_split( $this->r_semicolon, $this->details[ $k ] );
				$x = array_diff( $arr, $match );
				$y = array_diff( $match, $arr );

				if ( count( $x ) < 1 && count( $y ) < 1 ) {
					$this->selectors[ $i ] .= ',' . $this->selectors[ $k ];
					unset( $this->details[ $k ], $this->selectors[ $k ] );
				}
			}
		}
	}

	/**
	 * Combines color/style/width of border/outline properties
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function combineCSWproperties( $val ) {
		$storage = array();
		$pattern = "/(border|outline)-(color|style|width):(.*?);/is";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$a = strtolower( $matches[ 1 ][ $i ] );
			$b = strtolower( $matches[ 2 ][ $i ] );

			if ( ! isset( $storage[ $a ] ) ) {
				$storage[ $a ] = array();
			}

			$storage[ $a ][ $b ] = $matches[ 3 ][ $i ];
		}

		// Go through each tag for possible combination
		foreach ( $storage as $tag => $arr ) {
			// Make sure all 3 are defined and they aren't directionals
			if ( count( $arr ) == 3 && ! $this->checkUncombinables( $arr ) ) {
				// String to replace each instance with
				$replace = "$tag:" . $arr['width'] . ' ' . $arr['style'] . ' ' . $arr['color'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ( $arr as $x => $y ) {
					$val = str_ireplace( "$tag-$x:$y", $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines Aural properties (currently being depreciated in W3C Standards)
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function combineAuralCuePause( $val ) {
		$storage = array();
		$pattern = "/(cue|pause)-(before|after):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$a = strtolower( $matches[ 1 ][ $i ] );
			$b = strtolower( $matches[ 2 ][ $i ] );

			if ( ! isset( $storage[ $a ] ) ) {
				$storage[ $a ] = array();
			}

			$storage[ $a ][ $b ] = $matches[ 3 ][ $i ];
		}

		// Go through each tag for possible combination
		foreach ( $storage as $tag => $arr ) {
			if ( count( $arr ) == 2 && ! $this->checkUncombinables( $arr ) ) {
				// String to replace each instance with
				$replace = "$tag:" . $arr['before'] . ' ' . $arr['after'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ( $arr as $x => $y ) {
					$val = str_ireplace( "$tag-$x:$y", $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple directional properties of 
	 * margin/padding into single definition.
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function combineMPproperties( $val ) {
		$storage = array();
		$pattern = "/(margin|padding)-(top|right|bottom|left):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++){
			if ( ! isset( $storage[ $matches[1][$i] ] ) ) {
				$storage[ $matches[1][$i] ] = array( $matches[2][$i] => $matches[3][$i] );
			}

			// Override double written properties
			$storage[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
		}

		// Go through each tag for possible combination
		foreach ( $storage as $tag => $arr ) {
			// Drop capitols
			$tag = strtolower( $tag );

			// Only combine if all 4 definitions are found
			if ( count( $arr ) == 4 && ! $this->checkUncombinables( $arr ) ) {
				// If all definitions are the same, combine into single definition
				if ( $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['left'] ) {
					// String to replace each instance with
					$replace = "$tag:" . $arr['top'];

					// Replace every instance, as multiple declarations removal will correct it
					foreach ( $arr as $a => $b ) {
						$val = str_ireplace( "$tag-$a:$b", $replace, $val );
					}
				}
				// If opposites are the same, combine into single definition
				else if ( $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] ) {
					// String to replace each instance with
					$replace = "$tag:" . $arr['top'] . ' ' . $arr['left'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ( $arr as $a => $b ) {
						$val = str_ireplace( "$tag-$a:$b", $replace, $val );
					}
				}
				else{
					// String to replace each instance with
					$replace = "$tag:" . $arr['top'] . ' ' . $arr['right'] . ' ' . $arr['bottom'] . ' ' . $arr['left'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ( $arr as $a => $b ) {
						$val = str_ireplace( "$tag-$a:$b", $replace, $val );
					}
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple border properties into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */
	protected function combineBorderDefinitions( $val ) {
		$storage = array();
		$pattern = "/(border)-(top|right|bottom|left):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$a = $matches[1][$i];
			$b = $matches[2][$i];

			if ( ! isset( $storage[ $a ] ) ) {
				$storage[ $a ] = array(  $b => $matches[ 3 ][ $i ] );
			}
			else {
				$storage[ $a ][ $b ] = $matches[ 3 ][ $i ];
			}
		}

		foreach ( $storage as $tag => $arr ) {
			if ( count( $arr ) == 4 && $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['right'] ) {
				// String to replace each instance with
				$replace = "$tag:" . $arr['top'];

				// Replace every instance, as multiple declarations removal will correct it
				foreach ( $arr as $a => $b ) {
					$val = str_ireplace( "$tag-$a:$b", $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple font-definitions into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function combineFontDefinitions( $val ) {
		$storage = array();
		$pattern = "/(font|line)-(style|variant|weight|size|height|family):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			// Store each property in it's full state
			$storage[ $matches[1][$i] . '-' . $matches[2][$i] ] = $matches[3][$i];
		}

		// Combine font-size & line-height if possible
		if ( isset( $storage['font-size'] ) && isset( $storage['line-height'] ) ) {
			$storage['size/height'] = $storage['font-size'] . '/' . $storage['line-height'];
			unset( $storage['font-size'], $storage['line-height'] );
		}

		// Setup property groupings
		$fonts = array(
			array( 'font-style', 'font-variant', 'font-weight', 'size/height', 'font-family' ),
			array( 'font-style', 'font-variant', 'font-weight', 'font-size', 'font-family' ),
			array( 'font-style', 'font-variant', 'size/height', 'font-family' ),
			array( 'font-style', 'font-variant', 'font-size', 'font-family' ),
			array( 'font-style', 'font-weight', 'size/height', 'font-family' ),
			array( 'font-style', 'font-weight', 'font-size', 'font-family' ),
			array( 'font-variant', 'font-weight', 'size/height', 'font-family' ),
			array( 'font-variant', 'font-weight', 'font-size', 'font-family' ),
			array( 'font-weight', 'size/height', 'font-family' ),
			array( 'font-weight', 'font-size', 'font-family' ),
			array( 'font-variant', 'size/height', 'font-family' ),
			array( 'font-variant', 'font-size', 'font-family' ),
			array( 'font-style', 'size/height', 'font-family' ),
			array( 'font-style', 'font-size', 'font-family' ),
			array( 'size/height', 'font-family' ),
			array( 'font-size', 'font-family' ),
		);

		// Loop through each property check and see if they can be replaced
		foreach ( $fonts as $props ) {
			if ( $replace = $this->searchDefinitions( 'font', $storage, $props ) ) {
				break;
			}
		}

		// If replacement string found, run it on all options
		if ( $replace ) {
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				if ( ! isset( $storage['line-height'] ) || 
					( isset( $storage['line-height'] ) && stripos( $matches[0][$i], 'line-height') !== 0 ) ) {
						$val = str_ireplace( $matches[0][$i], $replace, $val );
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple background props into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function combineBackgroundDefinitions( $val ) {
		$storage = array();
		$pattern = "/background-(color|image|repeat|attachment|position):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			// Store each property in it's full state
			$storage[ $matches[1][$i] ] = $matches[2][$i];
		}

		// List of background props to check
		$backgrounds = array(
			// With color
			array( 'color', 'image', 'repeat', 'attachment', 'position' ),
			array( 'color', 'image', 'attachment', 'position' ),
			array( 'color', 'image', 'repeat', 'position' ),
			array( 'color', 'image', 'repeat', 'attachment' ),
			array( 'color', 'image', 'repeat' ),
			array( 'color', 'image', 'attachment' ),
			array( 'color', 'image', 'position' ),
			array( 'color', 'image' ),
			// Without Color
			array( 'image', 'attachment', 'position' ),
			array( 'image', 'repeat', 'position' ),
			array( 'image', 'repeat', 'attachment' ),
			array( 'image', 'repeat' ),
			array( 'image', 'attachment' ),
			array( 'image', 'position' ),
			array( 'image' ),
			// Just Color
			array( 'color' ),
		);

		// Run background checks and get replacement str
		foreach ( $backgrounds as $props ) {
			if ( $replace = $this->searchDefinitions( 'background', $storage, $props ) ) {
				break;
			}
		}

		// If replacement string found, run it on all options
		if ( $replace ) {
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				$val = str_ireplace( $matches[0][$i], $replace, $val );
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple list style props into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function combineListProperties( $val ) {
		$storage = array();
		$pattern = "/list-style-(type|position|image):(.*?);/i";
		preg_match_all( $pattern, $val, $matches );

		// Store secondhand prop
		for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
			$storage[ $matches[1][$i] ] = $matches[2][$i];
		}

		// List os list-style props to check against
		$lists = array(
			array( 'type', 'position', 'image' ),
			array( 'type', 'position' ),
			array( 'type', 'image' ),
			array( 'position', 'image' ),
			array( 'type' ),
			array( 'position' ),
			array( 'image' ),
		);

		// Run background checks and get replacement str
		foreach ( $lists as $props ) {
			if ( $replace = $this->searchDefinitions( 'list-style', $storage, $props ) ) {
				break;
			}
		}

		// If replacement string found, run it on all options
		if ( $replace ) {
			for ( $i = 0, $imax = count( $matches[1] ); $i < $imax; $i++ ) {
				$val = str_ireplace( $matches[0][$i], $replace, $val );
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Helper function to ensure flagged words don't get
	 * overridden
	 *
	 * @param (array/string) obj: Array/String of definitions to be checked
	 */ 
	protected function checkUncombinables( $obj ) {
		if ( is_array( $obj ) ) {
			foreach ( $obj as $item ) {
				if ( preg_match( "/inherit|\!important|\s/i", $item ) ) {
					return true;
				}
			}
			return false;
		}
		else {
			return preg_match( "/inherit|\!important|\s/i", $obj );
		}
	}

	/**
	 * Helper function to ensure all values of search array
	 * exist within the storage array
	 *
	 * @param (string) prop: CSS Property
	 * @param (array) storage: Array of definitions found
	 * @param (array) search: Array of definitions requred
	 */ 
	protected function searchDefinitions( $prop, $storage, $search ) {
		// Return storage & search don't match
		if ( count( $storage ) != count( $search ) ) {
			return false;
		}

		$str = "$prop:";
		foreach ( $search as $value ) {
			if ( ! isset( $storage[ $value ] ) || $this->checkUncombinables( $storage[ $value ] ) ) {
				return false;
			}
			$str .= $storage[ $value ] . ' ';
		}
		return trim( $str ) . ';';
	}

	/**
	 * Removes multiple definitions that were created during compression
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	protected function removeMultipleDefinitions( $val = '' ) {
		$storage = array();
		$arr = preg_split( $this->r_semicolon, $val );

		foreach ( $arr as $x ) {
			if ( $x ) {
				list( $a, $b ) = preg_split( $this->r_colon, $x, 2 );
				$storage[ $a ] = $b;
			}
		}

		if ( $storage ) {
			$val = '';
			foreach ( $storage as $x => $y ) {
				$val .= "$x:$y;";
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Removes '\' from possible splitter characters in URLs
	 *
	 * @params none
	 */ 
	protected function removeEscapedURLs($str){
		$search = array( "\\:", "\\;", "\\ " );
		$replace = array( ':', ';', ' ' );
		preg_match_all( "/url\((.*?)\)/", $str, $matches, PREG_OFFSET_CAPTURE );

		for ( $i = 0, $imax = count( $matches[0] ); $i < $imax; $i++ ) {
			$value = 'url(' . str_replace( $search, $replace, $matches[1][$i][0] ) . ')';
			$str = substr_replace( $str, $value, $matches[0][$i][1], strlen( $matches[0][$i][0] ) );
		}

		// Return unescaped string
		return $str;
	}

	/**
	 * Removes last semicolons on the final property of a set
	 *
	 * @params none
	 */ 
	protected function removeUnnecessarySemicolon(){
		foreach ( $this->details as &$value ) {
			$value = preg_replace( "/;$/", '', $value );
		}
	}

	/**
	 * Runs final counts on selectors and props
	 *
	 * @params none
	 */ 
	protected function runFinalStatistics(){
		// Selectors and props
		$this->stats['after']['selectors'] = count( $this->selectors );
		foreach ( $this->details as $item ) {
			$props = preg_split( $this->r_semicolon, $item );

			// Make sure count is true
			foreach ( $props as $k => $v ) {
				if ( ! isset( $v ) || $v == '' ) {
					unset( $props[ $k ] );
				}
			}
			$this->stats['after']['props'] += count( $props );
		}

		// Final count for stats
		$this->stats['after']['size'] = strlen( $this->css );
		$this->stats['after']['time'] = array_sum( explode( ' ', microtime() ) );
	}

	/**
	 * Reformats compressed CSS into specified format
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 */ 
	protected function readability( $import = '' ) {
		$css = '';
		if ( $this->options['readability'] == self::READ_MAX ) {
			$css = str_replace( ';', ";\n", $import );
			if ( $import ) {
				$css .= "\n";
			}

			foreach ( $this->selectors as $k => $v ) {
				if ( ! $this->details[ $k ] || trim( $this->details[ $k ] ) == '' ) {
					continue;
				}

				$v = str_replace( '>', ' > ', $v );
				$v = str_replace( '+', ' + ', $v );
				$v = str_replace( ',', ', ', $v );
				$css .= "$v {\n";
				$arr = preg_split( $this->r_semicolon, $this->details[ $k ] );

				foreach ( $arr as $item ) {
					if ( ! $item ) {
						continue;
					}

					list( $prop, $val ) = preg_split( $this->r_colon, $item, 2 );
					$css .= "\t$prop: $val;\n";
				}

				// Last semicolon isn't necessay, so don't keep it if possible
				if ( $this->options['unnecessary-semicolons'] ) {
					$css = preg_replace( "/;\n$/", "\n", $css );
				}

				$css .= "}\n\n";
			}
		}
		else if ( $this->options['readability'] == self::READ_MED ) {
			$css = str_replace( ';', ";\n", $import );
			foreach ( $this->selectors as $k => $v ) {
				if ( $this->details[ $k ] && $this->details[ $k ] != '' ) {
					$css .= "$v {\n\t" . $this->details[ $k ] . "\n}\n";
				}
			}
		}
		else if ( $this->options['readability'] == self::READ_MIN ) {
			$css = str_replace( ';', ";\n", $import );
			foreach ( $this->selectors as $k => $v ) {
				if ( $this->details[ $k ] && $this->details[ $k ] != '' ) {
					$css .= "$v{" . $this->details[ $k ] . "}\n";
				}
			}
		}
		else if ( $this->options['readability'] == self::READ_NONE ) {
			$css = $import;
			foreach ( $this->selectors as $k => $v ) {
				if ( $this->details[ $k ] && $this->details[ $k ] != '' ) {
					$css .= trim( "$v{" . $this->details[ $k ] . "}" );
				}
			}
		}
		else {
			$css = 'Invalid Readability Value';
		}

		// Return formatted script
		return trim( $css );
	}

	/**
	 * Display's a table containing the result statistics of the compression
	 *
	 * @params none
	 */ 
	public function displayStats(){
		// Set before/after arrays
		$before = $this->stats['before'];
		$after = $this->stats['after'];

		// Calc sizes for template
		$size = array(
			'before' => $this->displaySizes( $before['size'] ),
			'after' => $this->displaySizes( $after['size'] ),
			'final' => $this->displaySizes( $before['size'] - $after['size'] ),
		);

		// Stats Template
		include( CSSC_VARS_DIR . 'stats.php' );
	}

	/**
	 * Byte format return of file sizes
	 *
	 * @param (int) size: File size in Bytes
	 */ 
	public function displaySizes( $size = 0 ) {
		$orig = "(${size}B)";
		$ext = array( 'B', 'K', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
		for( $c = 0; $size > 1024; $c++ ) {
			$size /= 1024;
		}
		return round( $size, 2 ) . $ext[ $c ] . $orig;
	}
};

?>
