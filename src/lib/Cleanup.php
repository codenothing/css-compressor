<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Cleanup
{

	/**
	 * Cleanup patterns
	 *
	 * @class $Control: Compression Controller
	 * @param (array) options: Reference to options
	 * @param (regex) rsemi: Checks for last semit colon in details
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (array) rescape: Array of patterns for groupings that should be escaped
	 * @param (array) escaped: Contains patterns and replacements for espaced characters
	 */
	private $Control;
	private $options = array();
	private $rsemi = "/;$/";
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rescape = array(
		"/((?<!\\\)\")(.*?)((?<!\\\)\")/",
		"/((?<!\\\)')(.*?)((?<!\\\)')/",
		"/(url\()(.*?)(\))/",
	);
	private $escaped = array(
		'search' => array(
			"\\:",
			"\\;",
			"\\}",
			"\\{",
			"\\@",
			"\\,",
			"\\>",
			"\\+",
			"\\~",
			"\\/",
			"\\*",
			"\\r",
			"\\n",
			"\\t",
			"\\ ",
		),
		'replace' => array(
			":",
			";",
			"}",
			"{",
			"@",
			",",
			">",
			"+",
			"~",
			"/",
			"*",
			"\r",
			"\n",
			"\t",
			" ",
		),
	);

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->options = &$control->Option->options;
	}

	/**
	 * Central cleanup process, removes all injections
	 *
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */
	public function cleanup( &$selectors, &$details ) {
		foreach ( $details as &$value ) {
			if ( $this->options['rm-multi-define'] ) {
				$value = $this->removeMultipleDefinitions( $value );
			}
			$value = $this->removeUnnecessarySemicolon( $value );
		}

		return array( $selectors, $details );
	}

	/**
	 * Removes '\' from possible splitter characters in URLs
	 *
	 * @params none
	 */ 
	public function removeEscapedCharacters( $css ) {
		foreach ( $this->rescape as $regex ) {
			$start = 0;
			while ( preg_match( $regex, $css, $match, PREG_OFFSET_CAPTURE, $start ) ) {
				$value = $match[ 1 ][ 0 ]
					. str_replace( $this->escaped['search'], $this->escaped['replace'], $match[ 2 ][ 0 ] )
					. $match[ 3 ][ 0 ];
				$css = substr_replace( $css, $value, $match[ 0 ][ 1 ], strlen( $match[ 0 ][ 0 ] ) );
				$start = $match[ 0 ][ 1 ] + strlen( $value ) + 1;
			}
		}

		return $css;
	}

	/**
	 * Removes multiple definitions that were created during compression
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function removeMultipleDefinitions( $val = '' ) {
		$storage = array();
		$arr = preg_split( $this->rsemicolon, $val );

		foreach ( $arr as $x ) {
			if ( $x ) {
				list( $a, $b ) = preg_split( $this->rcolon, $x, 2 );
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
	 * Removes last semicolons on the final property of a set
	 *
	 * @params none
	 */ 
	private function removeUnnecessarySemicolon( $value ) {
		return preg_replace( $this->rsemi, '', $value );
	}

	/**
	 * Access to private methods for testing
	 *
	 * @param (string) method: Method to be called
	 * @param (array) args: Array of paramters to be passed in
	 */
	public function access( $method, $args ) {
		if ( method_exists( $this, $method ) ) {
			if ( $method == 'cleanup' ) {
				return $this->cleanup( $args[ 0 ], $args[ 1 ] );
			}
			else {
				return call_user_func_array( array( $this, $method ), $args );
			}
		}
		else {
			throw new CSSCompression_Exception( "Unknown method in Color Class - " . $method );
		}
	}
};

?>
