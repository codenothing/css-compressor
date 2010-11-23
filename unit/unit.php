<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */

// Before/After directories
// $root is borrowed from index.php
define('BEFORE', $root . '/sheets/before/');
define('AFTER', $root . '/sheets/after/');
define('BENCHMARK', $root . '/benchmark/src/');


Class CSScompressionUnitTest
{
	/**
	 * Class Variables
	 *
	 * @class compressor: CSSCompression Instance
	 * @param (int) errors: Number of errors found
	 * @param (int) passes: Number of tests passed
	 * @param (array) sandbox: Array containing test suite
	 * @param (array) instances: Array of default instance modes
	 */
	private $compressor;
	private $errors = 0;
	private $passes = 0;
	private $sandbox = array();
	private $instances = array();

	/**
	 * Constructor - runs the test suite
	 *
	 * @params none
	 */ 
	public function __construct(){
		// Reset the local class vars
		$this->compressor = new CSSCompression();
		$this->sandbox = CSSCompression::getJSON( dirname(__FILE__) . '/sandbox.json' );
		$this->errors = 0;

		// CSS Compressor doesn't currently throw exceptions, so we have to
		if ( $this->sandbox instanceof Exception ) {
			throw $this->sandbox;
		}

		// Run through sandbox tests
		$this->setOptions();
		$this->focus();

		// Full sheet tests (security checks)
		$this->setOptions();
		$this->testSheets();

		if ( isset( $_SERVER['argv'][ 1 ] ) && $_SERVER['argv'][ 1 ] == 'all' ) {
			$this->testDoubles();
		}
	}

	/**
	 * Turn all options to true to test every possible function
	 *
	 * @params none
	 */ 
	private function setOptions(){
		$options = $this->compressor->option();
		foreach ( $options as $key => $value ) {
			$this->compressor->option( $key, true );
		}
		$this->compressor->option( 'readability', CSSCompression::READ_NONE );
	}

	/**
	 * Run through a focused set of tests that runs directly through each function
	 *
	 * @params none
	 */
	private function focus(){
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
						$row['params'] = array( implode( $row['params'] ) );
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

					// Mark the result
					$this->mark( "${class}.${method}", $name, $result == $row['expect'] );
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
			}
			else {
				$this->mark( "$class.$method", $i, false );
			}
		}
		$this->mark( "$class.$method", 'Details Counted', count( $details ) === count( $tests['expect'] ) );
	}

	/**
	 * Run all test sheets through full compressor to see outcome
	 *
	 * @params none
	 */
	private function testSheets(){
		$handle = opendir( BEFORE );

		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( preg_match( "/\.css$/", $file ) ) {
				$before = trim( file_get_contents( BEFORE . $file ) );
				$after = trim( file_get_contents( AFTER . $file ) );
				$this->mark( $file, "full", $this->compressor->compress( $before ) === $after );
			}
		}
	}

	/**
	 * Run all test sheets through each mode multiple times
	 * to ensure everything is compressed the first time
	 *
	 * @params none
	 */
	private function testDoubles(){
		foreach ( CSSCompression::$modes as $mode => $options ) {
			$this->instances[ $mode ] = new CSSCompression( '', $mode );
		}

		$handle = opendir( BENCHMARK );
		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( preg_match( "/\.css$/", $file ) ) {
				$before = trim( file_get_contents( BENCHMARK . $file ) );
				foreach ( $this->instances as $mode => $instance ) {
					$first = $instance->compress( $before );
					$size = $instance->stats['after']['size'];
					$second = $instance->compress( $first );
					$this->mark( 'Double CSS ' . $file, $mode, $first === $second );
					$this->mark( 'Double Size ' . $file, $mode, $size === $instance->stats['after']['size'] );
					break;
				}
			}
		}
	}

	/**
	 * Outputs result onto table
	 *
	 * @param (string) method: CSSC Method
	 * @param (string) entry: Entry of test array
	 * @param (boolean) result: Result of test matching
	 */ 
	private function mark( $method, $entry, $result ) {
		if ( $result ) {
			$this->passes++;
			echo Color::green( "Passed: ${method}[$entry]" ) . "\r\n";
		}
		else{
			$this->errors++;
			echo Color::red( "Failed: ${method}[$entry]" ) . "\r\n";
		}
	}

	/**
	 * Destructor - Displays final errors
	 *
	 * @params none
	 */ 
	public function __destruct(){
		if ( $this->errors > 0 ) {
			$final = Color::boldred( "Test Failed: " . $this->errors . " total errors." );
			$exit = 1;
		}
		else {
			$final = Color::boldgreen( "All " . $this->passes . " Tests Passed" );
			$exit = 0;
		}
		echo "\r\n\r\n$final\r\n\r\n";
		exit( $exit );
	}
};

?>
