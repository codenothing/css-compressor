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


Class CSScompressionTestUnit Extends CSSCompression
{
	/**
	 * Class Variables
	 *
	 * @param (int) errors: Number of errors found
	 * @param (int) passes: Number of tests passed
	 * @param (array) sandbox: Array containing test suite
	 * @param (string) results: Result of all tests string for table
	 */
	private $errors = 0;
	private $passes = 0;
	private $sandbox = array();
	private $results = '';

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

		$this->setOptions();
		$this->initialTrimTest();
		$this->lineTesting();
		$this->testSelectorCombination();
		$this->testDetailCombination();

		// Full sheet tests (security checks)
		$this->setOptions();
		$this->testSheets();
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
			foreach ( $tests as $entry => $set ) {
				$before = $set[0];
				$after = $set[1];

				if ( $fn == 'individuals' ) {
					list ( $prop, $val ) = explode( ':', $before );
					list ( $prop, $val ) = $this->individuals( $prop, $val );
					$passed = ( "$prop:$val" == $after );
				}
				else{
					// Each function replaces all instances with compressed version of prop, so
					// add the remove multiply definitions for easier testing
					$passed = ( $this->$fn( $before ) == $after );
				}
				$this->mark( $fn, $entry, $passed );
			}
		}
	}

	/**
	 * Runs unit testing on the selector combination
	 *
	 * @params none
	 */ 
	private function testSelectorCombination(){
		// Before
		$this->selectors = array(
			0 => '#id div.class',
			1 => '#secondary .oops',
			3 => '#today p.boss',
			4 => '#id div.class',
			8 => '#today p.boss',
			15 => '#id div.class',
			16 => '#id div.class',
			17 => '#secondary .oops',
		);
		$this->details = array(
			0 => 'test1;',
			1 => 'test2;',
			3 => 'test3;',
			4 => 'test4;',
			8 => 'test5;',
			15 => 'test6;',
			16 => 'test7;',
			17 => 'test8;',
		);

		// After
		$selectors = array(
			0 => '#id div.class',
			1 => '#secondary .oops',
			3 => '#today p.boss',
		);

		$details = array(
			0 => 'test1;test4;test6;test7;',
			1 => 'test2;test8;',
			3 => 'test3;test5;',
		);

		// Run compression
		$this->combineMultiplyDefinedSelectors();
		$max = array_pop( array_keys( $this->selectors ) ) + 1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( isset( $this->selectors[ $i ] ) && isset( $this->details[ $i ] ) ) {
				$this->mark(
					'Selector Combination', 
					$i, 
					( $this->selectors[ $i ] === $selectors[ $i ] && $this->details[ $i ] === $details[ $i ] )
				);
			}
		}
	}

	/**
	 * Runs unit testing on the details combination
	 *
	 * @params none
	 */ 
	private function testDetailCombination(){
		// Before
		$this->selectors = array(
			0 => '#id div.class',
			1 => '#secondary .oops',
			3 => '#today p.boss',
			4 => '#id div.class',
			8 => '#today p.boss',
			15 => '#id div.class',
			16 => '#id div.class',
			17 => '#secondary .oops',
		);
		$this->details = array(
			0 => 'color:red;font-size:12pt;font-weight:bold;',
			1 => 'margin-left:10px;margin-top:20px;',
			3 => 'font-size:12pt;font-weight:bold;color:red;',
			4 => 'background:white;',
			8 => 'border:1px solid black;border-radius:20px;',
			15 => 'margin-top:20px;margin-left:10px;',
			16 => 'font-weight:bold;color:red;font-size:12pt;',
			17 => 'border-radius:20px;border:1px solid black;',
		);

		// After
		$selectors = array(
			0 => '#id div.class,#today p.boss,#id div.class',
			1 => '#secondary .oops,#id div.class',
			4 => '#id div.class',
			8 => '#today p.boss,#secondary .oops',
		);

		$details = array(
			0 => 'color:red;font-size:12pt;font-weight:bold;',
			1 => 'margin-left:10px;margin-top:20px;',
			4 => 'background:white;',
			8 => 'border:1px solid black;border-radius:20px;',
		);

		// Run compression
		$this->combineMultiplyDefinedDetails();
		$max = array_pop( array_keys( $this->selectors ) ) + 1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( isset( $this->selectors[ $i ] ) && isset( $this->details[ $i ] ) ) {
				$this->mark(
					'Detail Combination',
					$i,
					( $this->selectors[ $i ] === $selectors[ $i ] && $this->details[ $i ] === $details[ $i ] )
				);
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
