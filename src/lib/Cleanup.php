<?php

Class CSSCompression_Cleanup extends CSSCompression_Organize
{
	/**
	 * Cleanup patterns
	 *
	 * @param (regex) semicolon: Checks for last semit colon in details
	 * @param (regex) url: Matches url definition
	 * @param (array) escaped: Contains patterns and replacements for espaced characters
	 */
	private $semicolon = "/;$/";
	private $url = "/url\((.*?)\)/";
	private $escaped = array(
		'patterns'=> array( "\\:", "\\;", "\\ " ),
		'replacements' => array( ':', ';', ' ' )
	);

	/**
	 * Just passes along the initializer
	 */
	protected function __construct( $css = NULL, $options = NULL ) {
		parent::__construct( $css, $options );
	}

	/**
	 * Central cleanup process, removes all injections
	 *
	 * @param (array) selectors: Array of selectors
	 * @param (array) details: Array of details
	 */
	protected function cleanup( $selectors, $details ) {
		foreach ( $details as &$value ) {
			$value = $this->removeMultipleDefinitions( $value );
			$value = $this->removeEscapedURLs( $value );
			$value = $this->removeUnnecessarySemicolon( $value );
		}

		return array( $selectors, $details );
	}

	/**
	 * Removes multiple definitions that were created during compression
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	private function removeMultipleDefinitions( $val = '' ) {
		$storage = array();
		$arr = preg_split( $this->r_semicolon, $val );

		foreach ( $arr as $x ) {
			if ( $x ) {
				list( $a, $b ) = preg_split( $this->r_colon, $x, 2 );
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
	 * Removes '\' from possible splitter characters in URLs
	 *
	 * @params none
	 */ 
	private function removeEscapedURLs($str){
		preg_match_all( $this->url, $str, $matches, PREG_OFFSET_CAPTURE );

		for ( $i = 0, $imax = count( $matches[0] ); $i < $imax; $i++ ) {
			$value = 'url(' . str_replace( $this->escaped['patterns'], $this->escaped['replacements'], $matches[1][$i][0] ) . ')';
			$str = substr_replace( $str, $value, $matches[0][$i][1], strlen( $matches[0][$i][0] ) );
		}

		// Return unescaped string
		return $str;
	}

	/**
	 * Removes last semicolons on the final property of a set
	 *
	 * @params none
	 */ 
	private function removeUnnecessarySemicolon( $value ) {
		return preg_replace( $this->semicolon, '', $value );
	}

};

?>
