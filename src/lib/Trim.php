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
	 * @param (array) rescape: Array of patterns of groupings that should be escaped
	 * @param (array) trimmings: Stylesheet trimming patterns/replacements
	 * @param (array) escaped: Array of characters that need to be escaped
	 */
	private $Control;
	private $options = array();
	private $rescape = array(
		"/(url\()([^'\"].*?)(\))/s",
		"/((?<!\\\)\")(.*?)((?<!\\\)\")/s",
		"/((?<!\\\)')(.*?)((?<!\\\)')/s",
	);
	private $trimmings = array(
		'patterns' => array(
			"/(\s+)?([,{};>\~\+])(\s+)?/s", // Remove un-needed spaces around special characters
			"/url\((?<!\\\)\"(.*?)(?<!\\\)\"\)/s", // Remove quotes from urls
			"/url\((?<!\\\)'(.*?)(?<!\\\)'\)/s", // Remove quotes from urls
			"/(?<!\\\);{2,}/", // Remove unecessary semi-colons
			"/(?<!\\\)\s+/s", // Compress all spaces into single space
		),
		'replacements' => array(
			'$2',
			'url($1)',
			'url($1)',
			';',
			' ',
		)
	);
	private $escaped = array(
		'search' => array(
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
		'replace' => array(
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
	 * Central trim handler
	 *
	 * @param (string) css: Stylesheet to trim
	 */
	public function trim( $css ) {
		$css = $this->comments( $css );
		$css = $this->escape( $css );
		$css = $this->strip( $css );
		return $css;
	}

	/**
	 * Does a quick run through the script to remove all comments from the sheet,
	 *
	 * @param (string) css: Stylesheet to trim
	 */
	private function comments( $css ) {
		$length = strlen( $css );
		$i = -1;
		$instring = false;
		$incomment = false;
		$match = '';
		$clean = '';
		$row = '';

		while ( ++$i < $length ) {
			$row = $css[ $i ];

			if ( $incomment ) {
				if ( $row == "*" && $css[ $i - 1 ] != "\\" && $css[ $i + 1 ] == "/" ) {
					$i++;
					$incomment = false;
				}
				continue;
			}
			else if ( $row == "\\" ) {
				$clean .= $row . $css[ ++$i ];
				continue;
			}
			else if ( $instring ) {
				$instring = $row != $match;
			}
			else if ( $row == "\"" || $row == "'" ) {
				$match = $row;
				$instring = true;
			}
			else if ( $row == "/" && $css[ $i + 1 ] == "*" ) {
				$incomment = true;
				continue;
			}

			$clean .= $row;
		}

		return $clean;
	}

	/**
	 * Runs initial formatting to setup for compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	private function strip( $css ) {
		// Run replacements
		return trim( preg_replace( $this->trimmings['patterns'], $this->trimmings['replacements'], $css ) );
	}

	/**
	 * Escape out possible splitter characters within urls
	 *
	 * @param (string) css: Full stylesheet
	 */
	private function escape( $css ) {
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
