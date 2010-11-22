<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Control
{
	/**
	 * Compression Variables
	 *
	 * @param (string) css: Holds compressed css string
	 * @param (string) mode: Current compression mode state
	 * @param (array) options: Holds compression options
	 * @param (array) stats: Holds compression stats
	 */ 
	public $css = '';
	public $mode = '';
	public $options = array();
	public $stats = array();

	/**
	 * Subclasses that do the ground work for this compressor
	 *
	 * @class Option: Option handling
	 * @class Trim: Does the initial trimming for the css
	 * @class Format: Formats the output
	 * @class Individuals: Runs compression algorithms on individual properties and values
	 * @class Numeric: Handles numeric compression
	 * @class Color: Handles color compression
	 * @class Selectors: Runs selector specific compressions
	 * @class Combine: Handles combining of various properties
	 * @class Organize: Reorganizes the sheet for futher compression
	 * @class Cleanup: Cleans out all injected characters during compression
	 * @class Compress: Central compression unit.
	 * @param subclasses: Array holding all the subclasses for inlusion
	 */
	public $CSSCompression;
	private $subclasses = array(
		'Option',
		'Trim',
		'Format',
		'Numeric',
		'Color',
		'Individuals',
		'Selectors',
		'Combine',
		'Organize',
		'Cleanup',
		'Compress',
	);

	/**
	 * Pull in the Compression instance and build the subclasses
	 *
	 * @param (class) CSSCompression: CSSCompression Instance
	 */
	public function __construct( CSSCompression $CSSCompression ) {
		$this->CSSCompression = $CSSCompression;

		// Load all subclasses on demand
		if ( ! class_exists( "CSSCompression_Option", false ) ) {
			$path = dirname(__FILE__) . '/';
			foreach ( $this->subclasses as $class ) {
				require( $path . $class . '.php' );
			}
		}

		// Initialize each subclass
		foreach ( $this->subclasses as $class ) {
			$full = "CSSCompression_$class";
			$this->$class = new $full( $this );
		}
	}

	/**
	 * Control access to properties
	 *
	 *	- Getting stats/_mode/css returns the current value of that property
	 *	- Getting options will return the current full options array
	 *	- Getting anything else returns that current value in the options array or NULL
	 *
	 * @param (string) name: Name of property that you want to access
	 */ 
	public function get( $name ) {
		if ( $name == 'css' || $name == 'mode' ) {
			return $this->$name;
		}
		else if ( $name == 'options' ) {
			return $this->Option->options;
		}
		else if ( $name == 'stats' ) {
			return $this->stats;
		}
		else {
			return $this->Option->option( $name );
		}
	}

	/**
	 * The setter method only allows access to setting values in the options array
	 *
	 * @param (string) name: Key name of the option you want to set
	 * @param (any) value: Value of the option you want to set
	 */ 
	public function set( $name, $value ) {
		// Allow for passing array of options to merge into current ones
		if ( $name === 'options' && is_array( $value ) ) {
			return $this->Option->merge( $value );
		} else {
			return $this->Option->option( $name, $value );
		}
	}

	/**
	 * Merges a predefined set options
	 *
	 * @param (string) mode: Name of mode to use.
	 */
	public function mode( $mode = NULL ) {
		return $this->Options->merge( $mode );
	}

	/**
	 * Cleans out class variables for next run
	 *
	 * @params none
	 */
	public function reset(){
		$this->css = '';
		$this->stats = array(
			'before' => array(
				'props' => 0,
				'selectors' => 0,
				'size' => 0,
				'time' => 0,
			), 
			'after' => array(
				'props' => 0,
				'selectors' => 0,
				'size' => 0,
				'time' => 0,
			),
		);
	}

	/**
	 * Proxy to run Compression on the sheet passed
	 *
	 * @param (string) css: Stylesheet to be compressed
	 * @param (array|string) options: Array of options or mode to use.
	 */
	public function compress( $css = NULL, $options = NULL ) {
		// Reset and merge options
		$this->reset();
		$this->Option->merge( $options );

		// Initial stats
		$this->stats['before']['time'] = array_sum( explode( ' ', microtime() ) );
		$this->stats['before']['size'] = strlen( $css );

		// Initial trimming
		$css = $this->Trim->trim( $css );

		// Run compression
		$css = $this->Compress->compress( $css, $options );

		// Return the compressed css
		return $this->css = $css;
	}

	/**
	 * Backdoor access to subclasses
	 * ONLY FOR DEVELOPMENT/TESTING.
	 *
	 * @param (string) class: Name of the focus class
	 * @param (array) config: Contains name reference and test arguments
	 */
	public function access( $class, $method, $args ) {
		if ( $class == 'Control' ) {
			return call_user_func_array( array( $class, $method ), $args );
		}
		else if ( in_array( $class, $this->subclasses ) ) {
			return $this->$class->access( $method, $args );
		}
		else {
			throw new Exception( "Unknown Class Access - " . $class );
		}
	}
};

?>
