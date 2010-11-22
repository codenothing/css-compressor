<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Option
{
	/**
	 * Option Patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Instance settings
	 */
	private $Control;
	public $options = array();

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->options = CSSCompression::$defaults;
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
			return $this->merge( $name );
		}
		else if ( $value === NULL ) {
			return isset( $this->options[ $name ] ) ? $this->options[ $name ] : NULL;
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
			return ( CSSCompression::$modes[ $mode ] = $config );
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
	public function reset( $clear = false ) {
		// Reset and return the new options
		return ( $this->options = $clear ? array() : CSSCompression::$defaults );
	}

	/**
	 * Extend like function to merge an array of preferences into
	 * the options array.
	 *
	 * @param (array) options: Array of preferences to merge into options
	 */ 
	public function merge( $options = array() ) {
		if ( $options && is_array( $options ) && count( $options ) ) {
			foreach ( $this->options as $key => $value ) {
				if ( ! isset( $options[ $key ] ) ) {
					continue;
				}
				else if ( $options[ $key ] && $options[ $key ] == 'on' ) {
					$this->options[ $key ] = true;
				}
				else if ( $options[ $key ] && $options[ $key ] == 'off' ) {
					$this->options[ $key ] = false;
				}
				else {
					$this->options[ $key ] = intval( $options[ $key ] );
				}
			}
		}
		else if ( $options && is_string( $options ) && array_key_exists( $options, CSSCompression::$modes ) ) {
			$this->Control->mode = $options;

			// Default all to true, the mode has to force false
			foreach ( $this->options as $key => $value ) {
				if ( $key != 'readability' ) {
					$this->options[ $key ] = true;
				}
			}

			// Merge mode into options
			foreach ( CSSCompression::$modes[ $options ] as $key => $value ) {
				$this->options[ $key ] = $value;
			}
		}

		return $this->options;
	}

	/**
	 * Access to private methods for testing
	 *
	 * @param (string) method: Method to be called
	 * @param (array) args: Array of paramters to be passed in
	 */
	public function access( $method, $args ) {
		if ( method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}
		else {
			throw new Exception( "Unknown method in Color Class - " . $method );
		}
	}
};

?>
