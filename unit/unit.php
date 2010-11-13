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


Class CSScompressionTestUnit Extends CSSCompression
{
	/**
	 * Class Variables
	 *
	 * @param (int) errors: Number of errors found
	 * @param (int) passes: Number of tests passed
	 * @param (array) sandbox: Array containing test suite
	 * @param (string) results: Result of all tests string for table
	 * @param (array) instances: Array of default instance modes
	 */
	private $errors = 0;
	private $passes = 0;
	private $sandbox = array();
	private $results = '';
	private $instances = array();

	/**
	 * Constructor - runs the test suite
	 *
	 * @params none
	 */ 
	public function __construct( $sandbox ) {
		parent::__construct('');

		// Reset the local class vars
		$this->sandbox = $sandbox;
		$this->errors = 0;
		$this->results = '';

		// Run through sandbox tests
		$this->setOptions();
		$this->initialTrimTest();
		$this->lineTesting();

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
		foreach ( $this->options as $key => $value ) {
			$this->options[ $key ] = true;
		}
		$this->options[ 'readability' ] = self::READ_NONE;
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
				list ( $selectors, $details ) = $this->$fn( $tests['selectors']['before'], $tests['details']['before'] );
				$max = array_pop( array_keys( $selectors ) ) + 1;
				for ( $i = 0; $i < $max; $i++ ) {
					if ( isset( $selectors[ $i ] ) && isset( $details[ $i ] ) ) {
						$this->mark(
							$fn,
							$i,
							( $selectors[ $i ] === $tests['selectors']['expected'][ $i ] && 
								$details[ $i ] === $tests['details']['expected'][ $i ] )
						);
					}
				}
				$this->mark( $fn, 'Selectors Counted', count( $selectors ) === count( $tests['selectors']['expected'] ) );
				$this->mark( $fn, 'Details Counted', count( $details ) === count( $tests['details']['expected'] ) );
				continue;
			}

			foreach ( $tests as $entry => $set ) {
				$before = $set[0];
				$after = $set[1];

				if ( $fn == 'individuals' ) {
					list ( $prop, $val ) = explode( ':', $before, 2 );
					list ( $prop, $val ) = $this->individuals( $prop, $val );
					$passed = ( "$prop:$val" == $after );
				}
				else {
					// Each function replaces all instances with compressed version of prop, so
					// add the remove multiply definitions for easier testing
					$passed = ( $this->$fn( $before ) == $after );
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
