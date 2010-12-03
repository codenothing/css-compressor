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
	 * @param (string) token: Copy of the injection token
	 * @param (array) options: Reference to options
	 * @param (regex) rmark: Stop points during selector parsing
	 * @param (regex) ridclassend: End of a id/class string
	 * @param (regex) rquote: Checks for the next quote character
	 * @param (array) pseudos: Contains pattterns and replacments to space out pseudo selectors
	 */
	private $Control;
	private $token = '';
	private $options = array();
	private $rmark = "/(?<!\\\)(#|\.|=)/";
	private $ridclassend = "/(?<!\\\)[:#>~\[\+\*\. ]/";
	private $rquote = "/(?<!\\\)(\"|')?\]/";
	private $pseudos = array(
		'patterns' => array(
			"/\:first-(letter|line)[,]/i",
			"/  /",
			"/:first-(letter|line)$/i",
		),
		'replacements' => array(
			":first-$1 ,",
			" ",
			":first-$1 ",
		)
	);

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->token = $control->token;
		$this->options = &$control->Option->options;
	}

	/**
	 * Selector specific optimizations
	 *
	 * @param (array) selectors: Array of selectors
	 */
	public function selectors( &$selectors = array() ) {
		foreach ( $selectors as &$selector ) {
			// Auto ignore sections
			if ( strpos( $selector, $this->token ) === 0 ) {
				continue;
			}

			// Smart casing and token injection
			$selector = $this->parse( $selector );

			// Add space after pseduo selectors (so ie6 doesn't complain)
			if ( $this->options['pseduo-space'] ) {
				$selector = $this->pseduoSpace( $selector );
			}
		}

		return $selectors;
	}

	/**
	 * Converts selectors like BODY => body, DIV => div
	 * and injects tokens wrappers for attribute values
	 *
	 * @param (string) selector: CSS Selector
	 */ 
	private function parse( $selector ) {
		$clean = '';
		$substr = '';
		$pos = 0;

		while ( preg_match( $this->rmark, $selector, $match, PREG_OFFSET_CAPTURE, $pos ) ) {
			$substr = substr( $selector, $pos, $match[ 0 ][ 1 ] + 1 - $pos );
			$clean .= $this->options['lowercase-selectors'] ? strtolower( $substr ) : $substr;
			$pos = $match[ 0 ][ 1 ] + strlen( $match[ 1 ][ 0 ] );

			if ( $match[ 1 ][ 0 ] == '#' || $match[ 1 ][ 0 ] == '.' ) {
				if ( preg_match( $this->ridclassend, $selector, $m, PREG_OFFSET_CAPTURE, $pos ) ) {
					$clean .= substr( $selector, $pos, $m[ 0 ][ 1 ] - $pos );
					$pos = $m[ 0 ][ 1 ];
				}
				else {
					$clean .= substr( $selector, $pos );
					$pos = strlen( $selector );
					break;
				}
			}
			else if ( preg_match( $this->rquote, $selector, $m, PREG_OFFSET_CAPTURE, $pos ) ) {
				if ( $selector[ $pos ] == "\"" || $selector[ $pos ] == "'" ) {
					$pos++;
				}
				$clean .= $this->token . substr( $selector, $pos, $m[ 0 ][ 1 ] - $pos ) . $this->token . ']';
				$pos = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
			}
			else {
				$clean .= substr( $selector, $pos );
				$pos = strlen( $selector );
				break;
			}
		}

		return $clean . ( $this->options['lowercase-selectors'] ? strtolower( substr( $selector, $pos ) ) : substr( $selector, $pos ) );
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
			if ( $method == 'selectors' ) {
				return $this->selectors( $args[ 0 ] );
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
