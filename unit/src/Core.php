<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
require( dirname( __FILE__ ) . '/../../src/CSSCompression.php' );
require( dirname( __FILE__ ) . '/Color.php' );
require( dirname( __FILE__ ) . '/Sandbox.php' );
require( dirname( __FILE__ ) . '/Sheets.php' );


Class CSScompression_Unit_Core extends CSScompression_Unit_Sheets
{
	/**
	 * Core Patterns
	 *
	 * @param (CSSCompression instance) compressor: Compression instance
	 * @param (string) root: Root path to unit dir
	 * @param (int) errors: Number of errors found
	 * @param (int) passes: Number of tests passed
	 * @param (string) errorstack: Extra information at then end of output
	 * @param (array) block: Array of special markings on test files
	 * @param (array) specials: Special configurations for marked test files
	 */
	protected $compressor;
	protected $root = '';
	protected $errors = 0;
	protected $passes = 0;
	protected $errorstack = '';
	protected $block = array();
	protected $specials = array();

	/**
	 * Constructor - runs the test suite
	 *
	 * @param (array) block: Array of files to temporarily ignore
	 * @param (array) specials: List of special settings for certain files
	 */ 
	public function __construct( $block = array(), $specials = array() ) {
		$this->block = $block;
		$this->specials = $specials;

		// Common compression instance
		$this->compressor = new CSSCompression();

		// Root unit directory
		$this->root = realpath( dirname( __FILE__ ) . '/../' ) . '/' ;

		// Clean out any lingering errors
		$this->clean( $this->root . 'errors/' );

		// Construct up the tree
		parent::__construct();

		// Run focused tests first, then sheet tests
		$this->reset();
		$this->sandbox();
		$this->sheets();
	}

	/**
	 * Removes all files in a directory (NOT RECURSIVE)
	 *
	 * @param (string) dir: Full directory path
	 */
	private function clean( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return mkdir( $dir );
		}

		$handle = opendir( $dir );
		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( $file != '.' && $file != '..' && is_file( $dir . $file ) && strpos( $file, 'README' ) === false ) {
				unlink( $dir . $file );
			}
		}
	}

	/**
	 * Turns all options to true
	 *
	 * @params none
	 */ 
	protected function reset(){
		$options = $this->compressor->option();
		foreach ( $options as $key => $value ) {
			$this->compressor->option( $key, true );
		}
		$this->compressor->option( 'readability', CSSCompression::READ_NONE );
	}

	/**
	 * Outputs result onto table
	 *
	 * @param (string) method: CSSC Method
	 * @param (string) entry: Entry of test array
	 * @param (boolean) result: Result of test matching
	 */ 
	protected function mark( $method, $entry, $result ) {
		if ( $result ) {
			$this->passes++;
			echo Color::green( "Passed: ${method}[$entry]" ) . "\r\n";
		}
		else{
			$this->errors++;
			$this->errorstack .= Color::red( "Failed: ${method}[$entry]" ) . "\r\n";
			echo Color::red( "Failed: ${method}[$entry]" ) . "\r\n";
		}
	}

	/**
	 * Destructor - Displays final errors
	 *
	 * @params none
	 */ 
	public function __destruct(){
		// Add warnings before the last report
		if ( count( $this->block ) ) {
			echo "\r\n\r\n";
			foreach ( $this->block as $file ) {
				echo Color::yellow( "Warning: $file is NOT being tested." ) . "\r\n";
			}
		}

		// Final count
		if ( $this->errors > 0 ) {
			$final = Color::boldred( "Test Failed: " . $this->errors . " total errors. -- " ) . "\r\n" . $this->errorstack;
			$exit = 1;
		}
		else {
			$final = Color::boldgreen( "All " . $this->passes . " Tests Passed" );
			$exit = 0;
		}

		// Output final and exit accordingly
		echo "\r\n\r\n$final\r\n\r\n";
		exit( $exit );
	}
};

?>
