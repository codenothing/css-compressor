<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Trim
{
	/**
	 * Trim Patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Reference to options
	 */
	private $Control;
	private $options = array();

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
	 * Central trim handler
	 *
	 * @param (string) css: Stylesheet to trim
	 */
	public function trim( $css ) {
		$css = $this->strip( $css );
		$css = $this->escape( $css );
		return $css;
	}

	/**
	 * Runs initial formatting to setup for compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	private function strip( $css ) {
		// Regex
		$search = array(
			1 => "/(\/\*|\<\!\-\-)(.*?)(\*\/|\-\-\>)/s", // Remove all comments
			2 => "/(\s+)?([,{};>\+])(\s+)?/s", // Remove un-needed spaces around special characters
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
		return trim( preg_replace( $search, $replace, $css ) );
	}

	private function escape( $css ) {
		// Escape out possible splitter characters within urls
		$search = array( ':', ';', ' ' );
		$replace = array( "\\:", "\\;", "\\ " );
		preg_match_all( "/url\((.*?)\)/", $css, $matches, PREG_OFFSET_CAPTURE );

		for ( $i=0, $imax=count( $matches[0] ); $i < $imax; $i++ ) {
			$value = 'url(' . str_replace( $search, $replace, $matches[1][$i][0] ) . ')';
			$css = substr_replace( $css, $value, $matches[0][$i][1], strlen( $matches[0][$i][0] ) );
		}

		return $css;
	}
};

?>
