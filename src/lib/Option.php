<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Option extends CSSCompression_Setup
{
	/**
	 * Just passes along the initializer
	 */
	protected function __construct( $css = NULL, $options = NULL ) {
		parent::__construct( $css, $options );
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
	public function resetOptions( $clear = false ) {
		// Reset and return the new options
		return ( $this->options = $clear ? array() : CSSCompression::$defaults );
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
		else if ( $prefs && is_string( $prefs ) && array_key_exists( $prefs, CSSCompression::$modes ) ) {
			$this->_mode = $prefs;

			// Default all to true, the mode has to force false
			foreach ( $this->options as $key => $value ) {
				if ( $key != 'readability' ) {
					$this->options[ $key ] = true;
				}
			}

			// Merge mode into options
			foreach ( CSSCompression::$modes[ $prefs ] as $key => $value ) {
				$this->options[ $key ] = $value;
			}
		}

		return $this->options;
	}
};

?>
