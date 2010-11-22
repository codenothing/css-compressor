<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Selectors
{
	/**
	 * Selector patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Reference to options
	 * @param (regex) lowercase: Looks for element selectors
	 * @param (array) pseudos: Contains pattterns and replacments to space out pseudo selectors
	 */
	private $Control;
	private $options = array();
	private $lowercase = "/([^a-zA-Z])?([a-zA-Z]+)/i";
	private $pseudos = array(
		'patterns' => array(
			"/(\:[a-z-]+)([^a-z-\.\:\# ])/i",
			"/  /",
			"/(\:[a-z-]+)$/i",
		),
		'replacements' => array(
			"$1 $2",
			" ",
			"$1 ",
		)
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
	 * Selector specific optimizations
	 *
	 * @param (array) selectors: Array of selectors
	 */
	public function selectors( $selectors = array() ) {
		foreach ( $selectors as &$selector ) {
			// Lowercase selectors for combining
			if ( $this->options['lowercase-selectors'] ) {
				$selector = $this->lowercaseSelectors( $selector );
			}

			// Add space after pseduo selectors (so ie6 doesn't complain)
			if ( $this->options['pseduo-space'] ) {
				$selector = $this->pseduoSpace( $selector );
			}
		}

		return $selectors;
	}

	/**
	 * Converts selectors like BODY => body, DIV => div
	 *
	 * @param (string) selector: CSS Selector
	 */ 
	private function lowercaseSelectors( $selector ) {
		preg_match_all( $this->lowercase, $selector, $matches, PREG_OFFSET_CAPTURE );
		for ( $i = 0, $imax = count( $matches[0] ); $i < $imax; $i++ ) {
			if ( $matches[1][$i][0] !== '.' && $matches[1][$i][0] !== '#' ) {
				$match = $matches[2][$i];
				$selector = substr_replace( $selector, strtolower( $match[0] ), $match[1], strlen( $match[0] ) );
			}
		}

		return $selector;
	}

	/**
	 * Adds space after pseduo selector for ie6 like a:first-child{ => a:first-child {
	 *
	 * @param (string) selector: CSS Selector
	 */ 
	private function pseduoSpace( $selector ) {
		return preg_replace( $this->pseudos['patterns'], $this->pseudos['replacements'], $selector );
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
