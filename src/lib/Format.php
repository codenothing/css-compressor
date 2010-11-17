<?php

Class CSSCompression_Format extends CSSCompression_Option
{
	/**
	 * Readability Constants
	 *
	 * @param (int) READ_MAX: Maximum readability of output
	 * @param (int) READ_MED: Medium readability of output
	 * @param (int) READ_MIN: Minimal readability of output
	 * @param (int) READ_NONE: No readability of output (full compression into single line)
	 */ 
	const READ_MAX = 3;
	const READ_MED = 2;
	const READ_MIN = 1;
	const READ_NONE = 0;

	/**
	 * Just passes along the initializer
	 */
	protected function __construct( $css = NULL, $options = NULL ) {
		parent::__construct( $css, $options );
	}

	/**
	 * Runs initial formatting to setup for compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	protected function trim( $css ) {
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
		$css = trim( preg_replace( $search, $replace, $css ) );

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

	/**
	 * Reformats compressed CSS into specified format
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 */ 
	protected function readability( $import = '' ) {
		if ( $this->options['readability'] == self::READ_MAX ) {
			$css = str_replace( ';', ";\n", $import );
			if ( $import ) {
				$css .= "\n";
			}

			foreach ( $this->selectors as $k => $v ) {
				if ( ! $this->details[ $k ] || trim( $this->details[ $k ] ) == '' ) {
					continue;
				}

				$v = str_replace( '>', ' > ', $v );
				$v = str_replace( '+', ' + ', $v );
				$v = str_replace( ',', ', ', $v );
				$css .= "$v {\n";
				$arr = preg_split( $this->r_semicolon, $this->details[ $k ] );

				foreach ( $arr as $item ) {
					if ( ! $item ) {
						continue;
					}

					list( $prop, $val ) = preg_split( $this->r_colon, $item, 2 );
					$css .= "\t$prop: $val;\n";
				}

				// Last semicolon isn't necessay, so don't keep it if possible
				if ( $this->options['unnecessary-semicolons'] ) {
					$css = preg_replace( "/;\n$/", "\n", $css );
				}

				$css .= "}\n\n";
			}
		}
		else if ( $this->options['readability'] == self::READ_MED ) {
			$css = str_replace( ';', ";\n", $import );
			foreach ( $this->selectors as $k => $v ) {
				if ( $this->details[ $k ] && $this->details[ $k ] != '' ) {
					$css .= "$v {\n\t" . $this->details[ $k ] . "\n}\n";
				}
			}
		}
		else if ( $this->options['readability'] == self::READ_MIN ) {
			$css = str_replace( ';', ";\n", $import );
			foreach ( $this->selectors as $k => $v ) {
				if ( $this->details[ $k ] && $this->details[ $k ] != '' ) {
					$css .= "$v{" . $this->details[ $k ] . "}\n";
				}
			}
		}
		else if ( $this->options['readability'] == self::READ_NONE ) {
			$css = $import;
			foreach ( $this->selectors as $k => $v ) {
				if ( isset( $this->details[ $k ] ) && $this->details[ $k ] != '' ) {
					$css .= trim( "$v{" . $this->details[ $k ] . "}" );
				}
			}
		}
		else {
			$css = 'Invalid Readability Value';
		}

		// Return formatted script
		return trim( $css );
	}

	/**
	 * Byte format return of file sizes
	 *
	 * @param (int) size: File size in Bytes
	 */ 
	public function convertSize( $size = 0 ) {
		$ext = array( 'B', 'K', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
		for( $c = 0; $size > 1024; $c++ ) {
			$size /= 1024;
		}
		return round( $size, 2 ) . $ext[ $c ];
	}
};

?>
