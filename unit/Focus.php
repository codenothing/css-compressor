<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( E_ALL );
require( dirname( __FILE__ ) . '/../src/CSSCompression.inc' );
require( dirname( __FILE__ ) . '/Color.php' );


Class FocusedTest
{
	/**
	 * Configuration for the focused test (Manipulate these!)
	 *
	 * @param (string) mode: What mode you want the compressor in
	 * @param (array) options: Any extra set of options needed
	 * @param (string) class: Subclass to focus on
	 * @param (string) method: Class method to focus on
	 * @param (mixed) params: Parameters to pass into the method
	 * @param (mixed) expect: Expected result
	 */
	private $mode = 'small';
	private $options = array();
	private $class = 'Combine.Border';
	private $method = 'replace';
	private $params = array(
		"border-top:1px solid red;color:blue;border-left:1px solid red;border-right:1px solid red;border-bottom:1px solid red;",
	);
	private $expect = "border:1px solid red;";



	/**
	 * Other helpers (this comment is meant to separate context for the manips-able above)
	 *
	 * @param (CSSCompression instance) instance: Instance of CSSCompression class
	 * @param (mixed) result: Result of focused test
	 */
	private $instance;
	private $result;


	/**
	 * Auto-running script, run everything from the constructor
	 *
	 * @params none
	 */
	public function __construct(){
		$this->setup();
		$this->result = $this->instance->access( $this->class, $this->method, $this->params );
		$this->output();
	}

	/**
	 * Initializes the compression instance and sets modes/options
	 *
	 * @params none
	 */
	private function setup(){
		$this->instance = new CSSCompression();

		// Set mode if availiable
		if ( $this->mode ) {
			$this->instance->mode( $this->mode );
		}

		// Set custom options if needed
		if ( $this->options ) {
			$this->instance->option( $this->options );
		}
	}

	/**
	 * Prints out meta data about the test, and the final result
	 *
	 * @params none
	 */
	private function output(){
		// Printable copies of expected and actual results
		$expect = is_array( $this->expect ) ? print_r( $this->expect, true ) : $this->expect;
		$result = is_array( $this->result ) ? print_r( $this->result, true ) : $this->result;

		// Print out meta data
		echo Color::blue( 'Subclass: ' ) . $this->class . "\n";
		echo Color::blue( 'Method: ' ) . $this->method . "\n";
		echo Color::blue( 'Mode: ' ) . $this->mode . "\n";
		echo Color::blue( 'Options: ' ) . print_r( $this->options, true ) . "\n";
		echo Color::gray( '---------------------------------------' ) . "\n";
		echo Color::blue( 'Parameters Sent: ' ) . "\n" . print_r( $this->params, true ) . "\n";
		echo Color::gray( '---------------------------------------' ) . "\n";
		echo Color::blue( 'Expected Result: ' ) . "\n$expect\n";
		echo Color::gray( '---------------------------------------' ) . "\n";
		echo Color::blue( 'Actual Result: ' ) . "\n$result\n";
		echo "\n\n";

		if ( $this->result === $this->expect ) {
			echo Color::boldgreen( "Focused Test Passed" ) . "\n\n";
			exit( 0 );
		}
		else {
			echo Color::boldred( "Focused Test Failed" ) . "\n\n";
			exit( 1 );
		}
	}
};

new FocusedTest;

?>
