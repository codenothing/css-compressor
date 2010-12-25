<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */

Class CSScompression_Unit_Sandbox
{
	/**
	 * Sandbox Patterns
	 *
	 * @param (array) sandbox: Array containing test suite
	 * @param (regex) rtoken: Token match for replacement
	 * @param (regex) rjson: Match json extension
	 */
	private $sandbox = array();
	private $rtoken = "/(?<!\\\)#\{token\}/";
	private $rjson = "/\.json$/";

	/**
	 * Load each of the sandbox specs on init
	 *
	 * @params none
	 */
	protected function __construct(){
		// Get each of the sandbox specs
		$handle = opendir( $this->root . 'sandbox/' );
		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( preg_match( $this->rjson, $file ) ) {
				$json = CSSCompression::getJSON( $this->root . 'sandbox/' . $file );
				$class = preg_replace( $this->rjson, '', $file );
				$this->sandbox[ $class ] = $json;
			}
		}
	}

	/**
	 * Run through a focused set of tests that runs directly through each function
	 *
	 * @params none
	 */
	protected function sandbox(){
		// Setup compression vars
		$this->compressor->flush();
		foreach ( $this->sandbox as $class => $obj ) {
			foreach ( $obj as $method => $tests ) {
				// Check for special test handler
				if ( isset( $tests['_special'] ) ) {
					$fn = $tests['_special'];
					$this->$fn( $class, $method, $tests );
					continue;
				}

				// Run each test
				foreach ( $tests as $name => $row ) {
					// Readability help
					if ( isset( $row['paramjoin'] ) ) {
						$row['params'] = array( implode( $row['paramjoin'], $row['params'] ) );
					}

					// Token interchange
					if ( isset( $row['token'] ) ) {
						foreach ( $row['params'] as &$item ) {
							if ( is_string( $item ) ) {
								$item = preg_replace( $this->rtoken, CSSCompression::TOKEN, $item );
							}
						}
					}

					// Get the result from that single function
					$result = $this->compressor->access( $class, $method, $row['params'] );

					// Joining of the result
					if ( isset( $row['join'] ) && is_array( $result ) ) {
						$result = implode( $row['join'], $result );
					}

					// For readability, allow for arrays of expectations
					if ( is_array( $row['expect'] ) ) {
						$row['expect'] = implode( $row['expect'] );
					}

					// Token interchange
					if ( isset( $row['token'] ) && is_string( $row['expect'] ) ) {
						$row['expect'] = preg_replace( $this->rtoken, CSSCompression::TOKEN, $row['expect'] );
					}

					// Mark the result
					$this->mark( "${class}.${method}", $name, $result == $row['expect'] );

					// Output failures
					if ( $result != $row['expect'] ) {
						$this->errorstack .= "Sent:\n" . print_r( $row['params'], true ) 
							. "\n======\nExpecting:\n'" . $row['expect'] . "'"
							. "\n======\nResult:\n'$result'\n";
					}
				}
			}
		}
	}

	/**
	 * Special testing for selector and detail matching
	 *
	 * @param (string) class: Class to be called
	 * @param (string) method: Class method to be called
	 * @param (array) tests: Test layout
	 */
	private function both( $class, $method, $tests ) {
		$params = array( $tests['selectors']['params'], $tests['details']['params'] );
		list ( $selectors, $details ) = $this->compressor->access( $class, $method, $params );

		// Rekey the arrays
		$selectors = array_values( $selectors );
		$details = array_values( $details );

		// Mark the entries
		for ( $i = 0, $max = count( $selectors ); $i < $max; $i++ ) {
			if ( isset( $selectors[ $i ] ) && isset( $details[ $i ] ) && 
				isset( $tests['selectors']['expect'][ $i ] ) &&
				isset( $tests['details']['expect'][ $i ] ) ) {
					$this->mark(
						"$class.$method",
						$i,
						( $selectors[ $i ] === $tests['selectors']['expect'][ $i ] && 
							$details[ $i ] === $tests['details']['expect'][ $i ] )
					);
			}
			else {
				$this->mark( "$class.$method", $i, false );
			}
		}
		$this->mark( "$class.$method", 'Selectors Counted', count( $selectors ) === count( $tests['selectors']['expect'] ) );
		$this->mark( "$class.$method", 'Details Counted', count( $details ) === count( $tests['details']['expect'] ) );
	}

	/**
	 * Special testing for details arrays
	 *
	 * @param (string) class: Class to be called
	 * @param (string) method: Class method to be called
	 * @param (array) tests: Test layout
	 */
	private function details( $class, $method, $tests ) {
		$params = array( array(), $tests['params'] );
		list ( $selectors, $details ) = $this->compressor->access( $class, $method, $params );

		// Rekey the details
		$details = array_values( $details );

		// Mark the entries
		for ( $i = 0, $max = count( $details ); $i < $max; $i++ ) {
			if ( isset( $tests['expect'][ $i ] ) ) {
				// Allow for array entries, for easier reading in sandbox.json
				if ( is_array( $tests['expect'][ $i ] ) ) {
					$tests['expect'][ $i ] = implode( $tests['expect'][ $i ] );
				}

				// Mark the result
				$this->mark( "$class.$method", $i, $details[ $i ] === $tests[ 'expect' ][ $i ] );

				// Show the discrepency
				if ( $details[ $i ] !== $tests[ 'expect' ][ $i ] ) {
					$this->errorstack .= "Expecting:\n" . $tests[ 'expect' ][ $i ]
						. "\n=====\nResult:\n" . $details[ $i ] . "\n=======\n";
				}
			}
			else {
				$this->mark( "$class.$method", $i, false );
				$this->errorstack .= "Expected test $i not found.\n";
			}
		}
		$this->mark( "$class.$method", 'Details Counted', count( $details ) === count( $tests['expect'] ) );

		// Have to eyeball full counts
		if ( count( $details ) !== count( $tests['expect'] ) ) {
			$this->errorstack .= print_r( $details, true ) . print_r( $tests['expect'], true );
		}
	}

	/**
	 * Storage Array Comparison
	 *
	 * @param (string) class: Class to be called
	 * @param (string) method: Class method to be called
	 * @param (array) tests: Test layout
	 */
	private function storage( $class, $method, $tests ) {
		foreach ( $tests as $name => $row ) {
			if ( $name == '_special' ) {
				continue;
			}

			// Readability help
			if ( isset( $row['paramjoin'] ) ) {
				$row['params'] = array( implode( $row['paramjoin'], $row['params'] ) );
			}

			// Get the result from that single function
			$result = $this->compressor->access( $class, $method, $row['params'] );

			// Mark the result
			$this->mark( "${class}.${method}", $name, $result === $row['expect'] );

			// Output failures
			if ( $result !== $row['expect'] ) {
				$this->errorstack .= "Sent:\n" . print_r( $row['params'], true ) 
					. "\n======\nExpecting:\n" . print_r( $row['expect'], true )
					. "\n======\nResult:\n" . print_r( $result, true ) . "\n";
			}
		}
	}
};

?>
