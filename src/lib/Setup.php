<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Setup
{
	/**
	 * Trim Patterns
	 *
	 * @class Control: Compression Controller
	 * @class Individuals: Individuals Instance
	 * @param (array) options: Reference to options
	 * @param (array) stats: Reference to stats
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (regex) rspace: Checks for space without an escape '\' character before it
	 */
	private $Control;
	private $Individuals;
	private $options = array();
	private $stats = array();
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rspace = "/(?<!\\\)\s/";
	private $rsetup = array(
		'patterns' => array( "/{/", "/}/", "/@(charset|media|import)/i" ),
		'replacements' => array( "\n{", "}\n", "\n@$1" ),
	);

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->Individuals = $control->Individuals;
		$this->options = &$control->Option->options;
		$this->stats = &$control->stats;
	}

	/**
	 * Setup selector and details arrays for compression methods
	 *
	 * @params none
	 */ 
	public function setup( $css ) {
		// Seperate the element from the elements details
		$css = explode( "\n", preg_replace( $this->rsetup['patterns'], $this->rsetup['replacements'], $css ) );
		$selectors = array();
		$details = array();
		$media = '';
		$media_instance = NULL;
		$import = '';
		$fontface = '';
		$SEL_COUNTER = 0;

		while ( count( $css ) ) {
			$row = trim( array_shift( $css ) );

			// Determine whether your looking at the details or element
			if ( strpos( $row, '{' ) === 0 ) {
				$row = substr( $row, 1, -1 );
				$row = preg_split( $this->rsemicolon, $row );
				$parts = array();
				$storage = '';

				foreach ( $row as $line ) {
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
			else if ( strpos( $row, '@media' ) === 0 ) {
				$media .= $row;
				$left = 0;
				$right = 0;
				$content = '';

				// Find the end of the media section
				while ( count( $css ) && ( $left < 1 || $left > $right ) ) {
					$row = trim( array_shift( $css ) );
					$left += substr_count( $row, '{' );
					$right += substr_count( $row, '}' );
					$content .= $row;
				}

				// Get new instance for compression
				if ( ! $media_instance ) {
					$media_instance = new CSSCompression( '', $this->options );
				}

				// Remove the first and last braces from the content
				$content = substr( $content, 1 );
				$content = substr( $content, 0, -1 );

				// Compress the media section separatley
				$content = $media_instance->compress( $content );

				// Formatting for anything higher then 0 readability
				$newline = '';
				if ( $this->options['readability'] > CSSCompression::READ_NONE ) {
					$content = "\n\t" . str_replace( "\n", "\n\t", $content ) . "\n";
					$newline = "\n";
				}

				// Stash the compressed media script
				$media .= "{" . $content . "}$newline";
			}
			else if ( strpos( $row, '@font-face' ) === 0 ) {
				$fontface .= $row;
				$fontface .= count( $css ) ? preg_replace( "/(\s+)?:(\s+)?/s", ":", trim( array_shift( $css ) ) ) : '';
			}
			else if ( $row ) {
				// Add to selector counter for details storage
				$SEL_COUNTER++;
				$selectors[ $SEL_COUNTER ] = $row;
			}
		}

		return array( $selectors, $details, $import, $media, $fontface );
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
