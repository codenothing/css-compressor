<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */

// Before/After directories
// $root is borrowed from index.php
define('SPECIAL_BEFORE', $root . '/special/before/');
define('SPECIAL_AFTER', $root . '/special/after/');
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

		/*
		$this->initialTrimTest();
		$this->lineTesting();

		// Full sheet tests (security checks)
		$this->setOptions();
		$this->testSheets();

		if ( isset( $_SERVER['argv'][ 1 ] ) && $_SERVER['argv'][ 1 ] == 'all' ) {
			$this->testDoubles();
		}
		*/
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

	private function focus(){
		foreach ( $this->sandbox as $class => $obj ) {
			foreach ( $obj as $method => $tests ) {
				if ( $class == 'Organize' && ( $method == 'reduceSelectors' || $method == 'reduceDetails' ) ) {
					$this->organize( $method, $tests );
					continue;
				}
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

					/*
					echo $row['expect'] . "\n=========\n";
					echo $result . "\n======\n";
					*/
					$this->mark( "${class}.${method}", $name, $result == $row['expect'] );
				}
			}
		}
	}

	private function organize( $method, $tests ) {
		$params = array( $tests['selectors']['params'], $tests['details']['params'] );
		list ( $selectors, $details ) = $this->compressor->access( 'Organize', $method, $params );

		// Rekey the arrays
		$selectors = array_values( $selectors );
		$details = array_values( $details );

		// Mark the entries
		for ( $i = 0, $max = count( $selectors ); $i < $max; $i++ ) {
			if ( isset( $selectors[ $i ] ) && isset( $details[ $i ] ) && 
				isset( $tests['selectors']['expect'][ $i ] ) &&
				isset( $tests['details']['expect'][ $i ] ) ) {
					$this->mark(
						"Organize.$method",
						$i,
						( $selectors[ $i ] === $tests['selectors']['expect'][ $i ] && 
							$details[ $i ] === $tests['details']['expect'][ $i ] )
					);
			}
			else {
				$this->mark( "Organize.$method", $i, false );
			}
		}
		$this->mark( "Organize.$method", 'Selectors Counted', count( $selectors ) === count( $tests['selectors']['expect'] ) );
		$this->mark( "Organize.$method", 'Details Counted', count( $details ) === count( $tests['details']['expect'] ) );
	}

	/**
	 * Runs a test on the initalTrim() method of CSSC, uses
	 * a before/after system of files to do matching
	 *
	 * @params none
	 */ 
	private function initialTrimTest(){
		$this->css = file_get_contents( SPECIAL_BEFORE . 'initialTrim.css' );
		$after = file_get_contents( SPECIAL_AFTER . 'initialTrim.css' );
		$this->initialTrim();
		$this->mark( 'initialTrim.css', 'all', trim( $this->css ) == trim( $after ) );
	}

	/**
	 * Uses a test-array contain CSSC methods and various
	 * tests to run on each function. Takes special note to
	 * individuals()  methods
	 *
	 * @params none
	 */ 
	private function lineTesting(){
		foreach ( $this->sandbox as $fn => $tests ) {
			if ( $fn == 'combineMultiplyDefinedSelectors' || $fn == 'combineMultiplyDefinedDetails' ) {
				list ( $selectors, $details ) = $this->$fn( $tests['selectors']['test'], $tests['details']['test'] );

				// Rekey the arrays
				$selectors = array_values( $selectors );
				$details = array_values( $details );

				// Mark the entries
				for ( $i = 0, $max = count( $selectors ); $i < $max; $i++ ) {
					if ( isset( $selectors[ $i ] ) && isset( $details[ $i ] ) && 
						isset( $tests['selectors']['expect'][ $i ] ) &&
						isset( $tests['details']['expect'][ $i ] ) ) {
							$this->mark(
								$fn,
								$i,
								( $selectors[ $i ] === $tests['selectors']['expect'][ $i ] && 
									$details[ $i ] === $tests['details']['expect'][ $i ] )
							);
					}
					else {
						$this->mark( $fn, $i, false );
					}
				}
				$this->mark( $fn, 'Selectors Counted', count( $selectors ) === count( $tests['selectors']['expect'] ) );
				$this->mark( $fn, 'Details Counted', count( $details ) === count( $tests['details']['expect'] ) );
				continue;
			}

			foreach ( $tests as $entry => $set ) {
				// Some strings are too long and wrap, so
				// we force them into an array for better
				// readability
				if ( is_array( $set['test'] ) ) {
					$set['test'] = implode( '', $set['test'] );
				}
				// Sam with expect
				if ( is_array( $set['expect'] ) ) {
					$set['expect'] = implode( '', $set['expect'] );
				}

				// Inididual tests are run on each prop
				if ( $fn == 'individuals' ) {
					list ( $prop, $val ) = explode( ':', $set['test'], 2 );
					list ( $prop, $val ) = $this->individuals( $prop, $val );
					$passed = ( "$prop:$val" == $set['expect'] );
				}
				else {
					$passed = ( $this->$fn( $set['test'] ) == $set['expect'] );
				}
				$this->mark( $fn, $entry, $passed );
			}
		}
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
				$this->mark( $file, "full", $this->compress( $before ) === $after );
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
		foreach ( self::$modes as $mode => $options ) {
			$this->instances[ $mode ] = new CSSCompression( '', $mode );
		}

		$handle = opendir( BENCHMARK );
		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( preg_match( "/\.css$/", $file ) ) {
				$before = trim( file_get_contents( BENCHMARK . $file ) );
				foreach ( $this->instances as $mode => $instance ) {
					$first = $instance->compress( $before );
					$a = array( 'selectors' => $instance->selectors, 'details' => $instance->details );
					$size = $instance->stats['after']['size'];
					$second = $instance->compress( $first );
					$b = array( 'selectors' => $instance->selectors, 'details' => $instance->details );
					$this->mark( 'Double CSS ' . $file, $mode, $first === $second );
					$this->mark( 'Double Size ' . $file, $mode, $size === $instance->stats['after']['size'] );
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
