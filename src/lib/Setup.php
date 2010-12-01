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
	 * @param (regex) rliner: Matching known 1-line intros
	 * @param (regex) rnested: Matching known subsection handlers
	 * @param (array) rfontface: Array of patterns and replacements
	 * @param (array) rsetup: Expanding stylesheet for semi-tokenizing
	 */
	private $Control;
	private $Individuals;
	private $options = array();
	private $stats = array();
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rliner = "/^@(import|charset|namespace)/";
	private $rnested = "/^@(media|keyframes|-webkit-keyframes)/";
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
			"/(?<!\\\){/",
			"/(?<!\\\)}/",
			"/(?<!\\\)@/",
			"/(@(charset|import)[^;]*;)/",
		),
		'replacements' => array(
			"\n{\n",
			"\n}\n",
			"\n@",
			"$1\n",
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
		$newline = $this->options['readability'] > CSSCompression::READ_NONE ? "\n" : '';
		$setup = array(
			'selectors' => array(),
			'details' => array(),
			'unknown' => array(),
			'intro' => '',
			'-webkit-keyframes' => '',
			'keyframes' => '',
			'media' => '',
			'fontface' => '',
			'introliner' => '',
			'namespace' => '',
			'import' => '',
			'charset' => '',
		);

		while ( count( $css ) ) {
			$row = trim( array_shift( $css ) );

			if ( $row == '' ) {
				continue;
			}
			// Font-face
			else if ( strpos( $row, '@font-face' ) === 0 && count( $css ) >= 3 ) {
				$setup['fontface'] .= $row . $this->fontface( trim( $css[ 1 ] ) ) . $newline;

				// drop the details from the stack
				$css = array_slice( $css, 3 );
			}
			// Single block At-Rule set
			else if ( $row[ 0 ] == '@' && $css[ 0 ] == '{' && trim( $css[ 1 ] ) != '' && $css[ 2 ] == '}' ) {
				// Stash selector
				array_unshift( $setup['selectors'], $row );

				// Stash details (after the opening brace)
				array_unshift( $setup['details'], $this->details( trim( $css[ 1 ] ) ) );

				// drop the details from the stack
				$css = array_slice( $css, 3 );
			}
			// Nested declaration blocks (media and keyframes)
			else if ( preg_match( $this->rnested, $row, $match ) ) {
				$setup[ $match[ 1 ] ] .= $row . $this->nested( $css, $match[ 1 ] == 'media' ) . $newline;
			}
			// Single line At-Rules (import/charset/namespace)
			else if ( preg_match( $this->rliner, $row, $match ) ) {
				$setup[ $match[ 1 ] ] .= $row . $newline;
			}
			// Unknown nested block At-Rules
			else if ( $row[ 0 ] == '@' && $css[ 0 ] == '{' ) {
				$setup[ 'intro' ] .= $row . $this->nested( $css ) . $newline;
			}
			// Unknown single line At-Rules
			else if ( $row[ 0 ] == '@' && substr( $row, -1 ) == ';' ) {
				$setup[ 'introliner' ] .= $row . $newline;
			}
			// Declaration Block
			else if ( count( $css ) >= 3 && $css[ 0 ] == '{' && $css[ 2 ] == '}' ) {
				// Stash selector
				array_push( $setup['selectors'], $row );

				// Stash details (after the opening brace)
				array_push( $setup['details'], $this->details( trim( $css[ 1 ] ) ) );

				// drop the details from the stack
				$css = array_slice( $css, 3 );
			}
			// Last catch
			else {
				array_push( $setup['unknown'], $row );
			}
		}

		return $setup;
	}

	/**
	 * Run nested elements through a separate instance of compression
	 *
	 * @param (array) css: Reference to the original css array
	 * @param (bool) organize: Whether or not to organize the subsection (only true for media sections)
	 */
	private function nested( &$css = array(), $organize = false ) {
		$options = $this->options;
		$left = 0;
		$right = 0;
		$row = '';
		$independent = '';
		$content = '';
		$spacing = '';
		$newline = $this->options['readability'] > CSSCompression::READ_NONE ? "\n" : '';

		// Find the end of the nested section
		while ( count( $css ) && ( $left < 1 || $left > $right ) ) {
			$row = trim( array_shift( $css ) );

			if ( $row == '' ) {
				continue;
			}
			else if ( $row == '{' ) {
				$left++;
			}
			else if ( $row == '}' ) {
				$right++;
			}
			else if ( count( $css ) && substr( $row, 0, 1 ) != '@' && substr( $css[ 0 ], 0, 1 ) == '@' && substr( $row, -1 ) == ';' ) {
				$independent .= $row;
				continue;
			}

			$content .= $row;
		}

		// Compress the nested section independently after removing the wrapping braces
		// Also make sure to only organize media sections
		if ( $options['organize'] == true && $organize == false ) {
			$options['organize'] = false;
		}
		// Independent sections should be prepended to the next compressed section
		$content = ( $independent == '' ? '' : $independent . $newline )
			. CSSCompression::express( substr( $content, 1, -1 ), $options, true );

		// Formatting for anything higher then 0 readability
		if ( $newline == "\n" ) {
			$content = "\n\t" . str_replace( "\n", "\n\t", $content ) . "\n";
			$spacing = $this->options['readability'] > CSSCompression::READ_MIN ? ' ' : '';
		}

		// Stash the compressed nested script
		return "$spacing{" . $content . "}$newline";
	}

	/**
	 * Fontface only has whitespace compression
	 *
	 * @param (string) row: Font-face properties
	 */
	private function fontface( $row ) {
		$row = preg_replace( $this->rfontface['patterns'], $this->rfontface['replacements'], $row );

		if ( $this->options['readability'] > CSSCompression::READ_NONE ) {
			$row = " {\n\t" . preg_replace( $this->rsemicolon, ";\n\t", $row ) . "\n}\n";
		}
		else {
			$row = "{" . $row . "}";
		}

		return preg_replace( "/;}$/", "}", $row );
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
