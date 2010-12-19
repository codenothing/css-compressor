<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
require( dirname( __FILE__ ) . '/../../src/CSSCompression.inc' );
require( dirname( __FILE__ ) . '/Color.php' );


Class CSScompression_Test
{
	/**
	 * Class Variables
	 *
	 * @class compressor: CSSCompression Instance
	 * @param (string) root: Root path to unit dir
	 * @param (string) original: Path to original test sheets
	 * @param (string) expected: Path to expected test sheets
	 * @param (string) benchmark: Path to benchmark test sheets
	 * @param (int) errors: Number of errors found
	 * @param (int) passes: Number of tests passed
	 * @param (array) sandbox: Array containing test suite
	 * @param (array) instances: Array of default instance modes
	 * @param (array) modes: Copy of default modes
	 * @param (array) block: Array of special markings on test files
	 * @param (regex) rtoken: Token match for replacement
	 * @param (array) sheetspecials: Special configurations for marked test files
	 */
	private $compressor;
	private $root = '';
	private $original = '';
	private $expected = '';
	private $benchmark = '';
	private $errors = 0;
	private $passes = 0;
	private $errorstack = '';
	private $sandbox = array();
	private $instances = array();
	private $modes = array();
	private $block = array();
	private $rtoken = "/(?<!\\\)#\{token\}/";
	private $sheetspecials = array(
		'maxread' => array(
			'files' => array(
				'pit.css',
				'intros.css',
			),
			'mode' => NULL,
			'options' => array(
				'readability' => CSSCompression::READ_MAX,
			),
		),
		'maxsane' => array(
			'files' => array(
				'border-radius.css',
			),
			'mode' => 'sane',
			'options' => array(
				'readability' => CSSCompression::READ_MAX,
			),
		),
		'sane' => array(
			'files' => array(
				'id.css',
				'class.css',
			),
			'mode' => 'sane',
			'options' => array(),
		),
		'safe' => array(
			'files' => array(
				'box-model.css',
				'preserve-strings.css',
				'preserve-newline.css',
				'font-face.css',
			),
			'mode' => 'safe',
			'options' => array(),
		),
	);

	/**
	 * Constructor - runs the test suite
	 *
	 * @param (array) block: Array of files to temporarily focus on/ignore
	 */ 
	public function __construct( $block = array() ) {
		$this->block = $block;
		$this->setup();

		// Run through sandbox tests
		$this->reset();
		$this->focus();

		// Test express compression
		$this->express();

		// Test singleton access
		$this->singleton();

		// Full sheet tests (security checks)
		$this->reset();
		$this->testSheets();

		// Multi compression checks (just added sugar)
		if ( isset( $_SERVER['argv'][ 1 ] ) && $_SERVER['argv'][ 1 ] == 'all' ) {
			$this->testDoubles();
		}
	}

	/**
	 * Setup test suite
	 *
	 * @params none
	 */
	private function setup(){
		// Rootpath
		$this->root = realpath( dirname( __FILE__ ) . '/../' ) . '/' ;
		$this->original = $this->root . 'sheets/original/';
		$this->expected = $this->root . 'sheets/expected/';
		$this->benchmark = $this->root . 'benchmark/src/';

		// Clean out the errors directory
		$this->clean( $this->root . 'errors/' );

		// Reset the local class vars
		$this->compressor = new CSSCompression();
		$this->modes = CSSCompression::modes();
		$this->sandbox = CSSCompression::getJSON( dirname( __FILE__ ) . '/sandbox.json' );
		$this->errors = 0;

		// CSS Compressor doesn't currently throw exceptions, so we have to
		if ( $this->sandbox instanceof CSSCompression_Exception ) {
			throw $this->sandbox;
		}

		// Stash copies of each of the common modes
		foreach ( $this->modes as $mode => $options ) {
			$this->instances[ $mode ] = new CSSCompression( $mode );
			$singleton = CSSCompression::getInstance( $mode );
			$singleton->mode( $mode );
		}
	}

	/**
	 * Turns all options to true
	 *
	 * @params none
	 */ 
	private function reset(){
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

	/**
	 * Make sure express is working correctly
	 *
	 * @params none
	 */
	private function express(){
		$content = file_get_contents( $this->original . 'pit.css' );
		foreach ( $this->instances as $mode => $instance ) {
			$this->mark( "CSSCompression.express", $mode, CSSCompression::express( $content, $mode ) === $instance->compress( $content ) );
		}
	}

	/**
	 * Singleton testing
	 *
	 * @params none
	 */
	private function singleton(){
		$content = file_get_contents( $this->original . 'pit.css' );
		foreach ( $this->instances as $mode => $instance ) {
			$singleton = CSSCompression::getInstance( $mode );
			$this->mark( "CSSCompression.getInstance", $mode, $singleton->compress( $content ) === $instance->compress( $content ) );
		}
	}

	/**
	 * Run all test sheets through full compressor to see outcome
	 *
	 * @params none
	 */
	private function testSheets(){
		$handle = opendir( $this->original );
		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( ( count( $this->block['only'] ) > 0 && ! in_array( $file, $this->block['only'] ) ) ||
				in_array( $file, $this->block['temp'] ) ) {
					continue;
			}
			else if ( preg_match( "/\.css$/", $file ) ) {
				$this->reset();
				foreach ( $this->sheetspecials as $config ) {
					if ( in_array( $file, $config['files'] ) ) {
						// Use default mode if wanted
						if ( $config['mode'] ) {
							$this->compressor->mode( $config['mode'] );
						}

						// Use custom options
						if ( $config['options'] ) {
							$this->compressor->option( $config['options'] );
						}

						break;
					}
				}

				// Mark the result
				$original = trim( file_get_contents( $this->original . $file ) );
				$expected = trim( file_get_contents( $this->expected . $file ) );
				$result = trim( $this->compressor->compress( $original, NULL ) );
				$this->mark( $file, "full", $result === $expected );

				// Stash errors for diff tooling
				if ( $result !== $expected ) {
					file_put_contents( $this->root . "errors/$file-result.css", $result );
					$this->errorstack .= "diff " . $this->expected . $file . ' ' . $this->root . "errors/$file-result.css\n";
				}
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
		// Max readability for diff tooling
		foreach ( $this->instances as $mode => $instance ) {
			$instance->option( 'readability', CSSCompression::READ_MAX );
		}

		// Read each file in the direcotry
		$handle = opendir( $this->benchmark );
		while ( ( $file = readdir( $handle ) ) !== false ) {
			if ( ( count( $this->block['only'] ) > 0 && ! in_array( $file, $this->block['only'] ) ) ||
				in_array( $file, $this->block['temp'] ) ) {
					continue;
			}
			else if ( preg_match( "/\.css$/", $file ) ) {
				$original = trim( file_get_contents( $this->benchmark . $file ) );
				foreach ( $this->instances as $mode => $instance ) {
					// Small and full mode have to be run multiple times
					// to get finished output
					// TODO: Find a sane way to test for this
					if ( $mode == 'small' || $mode == 'full' ) {
						continue;
					}
					// Media elements should not be organized, so skip them if instance does that
					else if ( strpos( $original, '@media' ) !== false && $instance->option('organize') ) {
						continue;
					}

					// Double compression
					$first = $instance->compress( $original );
					$size = $instance->stats['after']['size'];
					$second = $instance->compress( $first );
					$this->mark( 'Double CSS ' . $file, $mode, $first === $second );
					$this->mark( 'Double Size ' . $file, $mode, $size === $instance->stats['after']['size'] );

					// Store inaccuracies for diff'ing
					if ( $first !== $second ) {
						file_put_contents( $this->root . "errors/$file-$mode-first.css", $first );
						file_put_contents( $this->root . "errors/$file-$mode-second.css", $second );
						$this->errorstack .= "diff " . $this->root . "errors/$file-$mode-first.css "
							. $this->root . "errors/$file-$mode-second.css\n";
					}
				}
			}
		}
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
			if ( $file != '.' && $file != '..' && strpos( $file, 'README' ) === false ) {
				unlink( $dir . $file );
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
		// Add warnings to before the last report
		if ( count( $this->block['only'] ) > 0 ) {
			echo "\r\n\r\n";
			foreach ( $this->block['only'] as $file ) {
				echo Color::yellow( "Warning: $file is the only file being tested." ) . "\r\n";
			}
		}
		else if ( count( $this->block['temp'] ) ) {
			echo "\r\n\r\n";
			foreach ( $this->block['temp'] as $file ) {
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
		echo "\r\n\r\n$final\r\n\r\n";
		exit( $exit );
	}
};

?>
