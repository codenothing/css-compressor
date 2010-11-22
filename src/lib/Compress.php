<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Compress
{
	/**
	 * Trim Patterns
	 *
	 * @class Control: Compression Controller
	 * @class Individuals: Individuals Instance
	 * @class Format: Formatting Instance
	 * @class Combine: Combine Instance
	 * @class Cleanup: Cleanup Instance
	 * @class Organize: Organize Instance
	 * @class Selectors: Selectors Instance
	 * @param (array) options: Reference to options
	 * @param (array) stats: Reference to stats
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (regex) rspace: Checks for space without an escape '\' character before it
	 */
	private $Control;
	private $Individuals;
	private $Format;
	private $Combine;
	private $Cleanup;
	private $Organize;
	private $Selectors;
	private $options = array();
	private $stats = array();
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rspace = "/(?<!\\\)\s/";

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->Individuals = $control->Individuals;
		$this->Format = $control->Format;
		$this->Combine = $control->Combine;
		$this->Cleanup = $control->Cleanup;
		$this->Organize = $control->Organize;
		$this->Selectors = $control->Selectors;
		$this->options = &$control->Option->options;
		$this->stats = &$control->stats;
	}

	/**
	 * Centralized function to run css compression.
	 * Assumes trimming has already been done.
	 *
	 * @param (string) css: CSS Contents
	 */ 
	public function compress( $css ) {
		// Do a little tokenizing, compress each property individually
		list( $selectors, $details, $import, $media ) = $this->setup( $css );

		// Mark number of selectors pre-combine
		$this->stats['before']['selectors'] = count( $selectors );

		// Do selector specific compressions
		$selectors = $this->Selectors->selectors( $selectors );

		// Look at each group of properties as a whole, and compress/combine similiar definitions
		list( $selectors, $details ) = $this->Combine->combine( $selectors, $details );

		// If order isn't important, run comination functions before and after compressions to catch all instances
		// Be sure to prune before hand for higher chance of matching
		if ( $this->options['organize'] ) {
			list( $selectors, $details ) = $this->Cleanup->cleanup( $selectors, $details );
			list( $selectors, $details ) = $this->Organize->organize( $selectors, $details );
		}

		// Do final maintenace work, remove injected slashes and property/values
		list( $selectors, $details ) = $this->Cleanup->cleanup( $selectors, $details );

		// Run final counters before full cleanup
		$this->finalCount( $selectors, $details );

		// Format css to users preference
		$css = $this->Format->readability( $this->options['readability'], $import, $selectors, $details );

		// Remove escapables
		$css = $this->Cleanup->removeEscapedCharacters( $css );

		// Add media string to top
		// TODO: Compress media individually like full css sheets
		if ( $media ) {
			$css = $media . $css;
		}

		// Mark final file size
		$this->stats['after']['size'] = strlen( $css );

		// Return compressed css
		return $css;
	}

	/**
	 * Setup selector and details arrays for compression methods
	 *
	 * @params none
	 */ 
	private function setup( $css ) {
		// Seperate the element from the elements details
		$css = explode( "\n", str_replace( array( "{", "}" ), array( "\n{", "}\n" ), $css ) );
		$selectors = array();
		$details = array();
		$media = false;
		$media_str = '';
		$media_content = '';
		$media_instance = NULL;
		$import = '';
		$SEL_COUNTER = 0;

		foreach ( $css as $row ) {
			$row = trim( $row );
			// Determine whether your looking at the details or element
			if ( $media && $row == '}' ) {
				if ( ! $media_instance ) {
					$media_instance = new CSSCompression( '', $this->options );
				}

				// Compress the media section separatley
				$media_content = $media_instance->compress( substr( $media_content, 1 ) );

				// Formatting for anything higher then 0 readability
				if ( $this->options['readability'] > CSSCompression::READ_NONE ) {
					$media_content = "\n\t" . str_replace( "\n", "\n\t", $media_content ) . "\n";
				}

				// Stash the compressed media script, and reset the media vars
				$media_str .= "{" . $media_content . "}\n";
				$media_content = '';
				$media = false;
			}
			else if ( $media ) {
				$media_content .= $row;
			}
			else if ( strpos( $row, '{') === 0 ) {
				$row = substr( $row, 1, strlen( $row ) - 2 );
				$row = preg_split( $this->rsemicolon, $row );
				$parts = array();
				$storage = '';

				foreach ( $row as $line ) {
					if ( preg_match( "/^(url|@import)/i", $line ) ) {
						$storage .= $line.";";
						continue;
					}

					// Grab the property and its value
					unset( $property, $value );
					$parts = preg_split( $this->rcolon, $line, 2 );

					// Property
					if ( isset( $parts[ 0 ] ) && ( $parts[ 0 ] = trim( $parts[ 0 ] ) ) != '' ) {
						$property = $parts[ 0 ];
					}

					// Value
					if ( isset( $parts[ 1 ] ) && ( $parts[ 1 ] = trim( $parts[ 1 ] ) ) != '' ) {
						$value = $parts[1];
					}

					// Fail safe, remove unspecified property/values
					if ( ! isset( $property ) || ! isset( $value ) ) {
						continue;
					}

					// Run the tag/element through each compression
					list ( $property, $value ) = $this->Individuals->individuals( $property, $value );

					// Add counter to before stats
					$this->stats['before']['props']++;

					// Store the compressed element
					$storage .= "$property:$value;";
				}
				// Store as the last known selector
				$details[ $SEL_COUNTER ] = $storage;
			}
			else if ( strpos( $row, '@import' ) === 0 || strpos( $row, '@charset' ) === 0 ) {
				// Seperate out each import string
				$arr = preg_split( $this->rsemicolon, $row );

				// Add to selector counter for details storage
				$SEL_COUNTER++;
				// Store the last entry as the selector
				$selectors[ $SEL_COUNTER ] = trim( $arr[ count( $arr ) - 1 ] );

				// Clear out the last entry(the actual selector) and add to the import string
				unset( $arr[ count( $arr ) - 1 ] );
				$import .= trim( implode( ';', $arr ) ) . ';';
			}
			else if ( strpos( $row, '@' ) === 0 ) {
				$media = true;
				$media_str .= $row;
			}
			else if ( $row ) {
				// Add to selector counter for details storage
				$SEL_COUNTER++;
				$selectors[ $SEL_COUNTER ] = $row;
			}
		}

		return array( $selectors, $details, $import, $media_str );
	}

	/**
	 * Runs final counts on selectors and props
	 *
	 * @params none
	 */ 
	private function finalCount( $selectors, $details ) {
		// Selectors and props
		$this->stats['after']['selectors'] = count( $selectors );
		foreach ( $details as $item ) {
			$props = preg_split( $this->rsemicolon, $item );

			// Make sure count is true
			foreach ( $props as $k => $v ) {
				if ( ! isset( $v ) || $v == '' ) {
					unset( $props[ $k ] );
				}
			}
			$this->stats['after']['props'] += count( $props );
		}

		// Final count for stats
		$this->stats['after']['time'] = array_sum( explode( ' ', microtime() ) );
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
