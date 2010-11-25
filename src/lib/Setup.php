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
	 * @param (instance) instance: Holder for secondary instance of CSSCompression
	 * @param (array) options: Reference to options
	 * @param (array) stats: Reference to stats
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (array) rfontface: Array of patterns and replacements
	 */
	private $Control;
	private $Individuals;
	private $instance;
	private $options = array();
	private $stats = array();
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rfontface = array(
		'patterns' => array(
			"/(\s+)?:(\s+)?/s",
			"/;$/",
		),
		'replacements' => array(
			":",
			"",
		),
	);
	private $rsetup = array(
		'patterns' => array(
			"/{/",
			"/}/",
			"/(@(charset|import)[^;]*;)/",
			"/@(media|font-face)/i"
		),
		'replacements' => array(
			"\n{\n",
			"\n}\n",
			"\n$1\n",
			"\n@$1"
		),
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
	 * @param (string) css: Trimed stylesheet
	 */ 
	public function setup( $css ) {
		// Seperate the element from the elements details
		$css = explode( "\n", preg_replace( $this->rsetup['patterns'], $this->rsetup['replacements'], $css ) );
		$selectors = array();
		$details = array();
		$unknown = array();
		$media = '';
		$import = '';
		$fontface = '';

		while ( count( $css ) ) {
			$row = trim( array_shift( $css ) );

			if ( $row == '' ) {
				continue;
			}
			else if ( strpos( $row, '@media' ) === 0 ) {
				$media .= $row . $this->media( $css );
			}
			else if ( strpos( $row, '@font-face' ) === 0 && count( $css ) >= 3 ) {
				$fontface .= $row . $this->fontface( $css[ 1 ] );

				// drop the details from the stack
				$css = array_slice( $css, 3 );
			}
			else if ( strpos( $row, '@import' ) === 0 || strpos( $row, '@charset' ) === 0 ) {
				$import .= $row;
			}
			else if ( count( $css ) >= 3 && $css[ 0 ] == '{' ) {
				// Stash selector
				array_push( $selectors, $row );

				// Stash details (after the opening brace)
				array_push( $details, $this->details( trim( $css[ 1 ] ) ) );

			}
			else {
				array_push( $unknown, $row );
			}
		}

		return array( $selectors, $details, $import, $media, $fontface, $unknown );
	}

	/**
	 * Run media elements through a separate instance of compression
	 *
	 * @param (array) css: Reference to the original css array
	 */
	private function media( &$css = array() ) {
		$left = 0;
		$right = 0;
		$row = '';
		$content = '';
		$newline = '';

		// Get new instance for compression
		if ( ! $this->instance ) {
			$this->instance = new CSSCompression( '', $this->options );
		}

		// Find the end of the media section
		while ( count( $css ) && ( $left < 1 || $left > $right ) ) {
			$row = trim( array_shift( $css ) );
			$left += substr_count( $row, '{' );
			$right += substr_count( $row, '}' );
			$content .= $row;
		}


		// Remove the first and last braces from the content
		$content = substr( $content, 1 );
		$content = substr( $content, 0, -1 );

		// Compress the media section separatley
		$content = $this->instance->compress( $content, $this->options );

		// Formatting for anything higher then 0 readability
		if ( $this->options['readability'] > CSSCompression::READ_NONE ) {
			$content = "\n\t" . str_replace( "\n", "\n\t", $content ) . "\n";
			$newline = "\n";
		}

		// Stash the compressed media script
		return "{" . $content . "}$newline";
	}

	/**
	 * Fontface only has whitespace compression
	 *
	 * @param (string) row: Font-face properties
	 */
	private function fontface( $row ) {
		$row = preg_replace( $this->rfontface['patterns'], $this->rfontface['replacements'], trim( $row ) );
		if ( $this->options['readability'] > CSSCompression::READ_NONE ) {
			return " {\n\t" . preg_replace( $this->rsemicolon, ";\n\t", $row ) . "\n}\n";
		}
		else {
			return "{" . $row . "}";
		}
	}

	/**
	 * Run individual compression techniques on each property of a selector
	 *
	 * @param (string) row: Selector properties
	 */
	private function details( $row ) {
		$row = preg_split( $this->rsemicolon, $row );
		$parts = array();
		$details = '';

		foreach ( $row as $line ) {
			// Set loopers
			$parts = preg_split( $this->rcolon, $line, 2 );
			$prop = '';
			$value = '';

			// Property
			if ( isset( $parts[ 0 ] ) && ( $parts[ 0 ] = trim( $parts[ 0 ] ) ) != '' ) {
				$prop = $parts[ 0 ];
			}

			// Value
			if ( isset( $parts[ 1 ] ) && ( $parts[ 1 ] = trim( $parts[ 1 ] ) ) != '' ) {
				$value = $parts[1];
			}

			// Fail safe, remove unspecified property/values
			if ( $prop == '' || $value == '' ) {
				continue;
			}

			// Run the tag/element through each compression
			list ( $prop, $value ) = $this->Individuals->individuals( $prop, $value );

			// Add counter to before stats
			$this->stats['before']['props']++;

			// Store the compressed element
			$details .= "$prop:$value;";
		}

		return $details;
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
			throw new CSSCompression_Exception( "Unknown method in Color Class - " . $method );
		}
	}
};

?>
