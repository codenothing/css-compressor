<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */

Class CSScompression_Unit_Sheets extends CSScompression_Unit_Sandbox
{
	/**
	 * Sheet Patterns
	 *
	 * @param (string) original: Path to original test sheets
	 * @param (string) expected: Path to expected test sheets
	 * @param (string) benchmark: Path to benchmark test sheets
	 * @param (array) instances: Array of default instance modes
	 * @param (array) modes: Copy of default modes
	 */
	private $original = '';
	private $expected = '';
	private $benchmark = '';
	private $instances = array();
	private $modes = array();

	/**
	 * Constructor - runs the test suite
	 *
	 * @param (array) block: Array of files to temporarily focus on/ignore
	 */ 
	public function __construct(){
		// Set directory paths
		$this->original = $this->root . 'sheets/original/';
		$this->expected = $this->root . 'sheets/expected/';
		$this->benchmark = $this->root . 'benchmark/src/';

		// Default set of modes
		$this->modes = CSSCompression::modes();

		// Stash copies of each of the common modes
		foreach ( $this->modes as $mode => $options ) {
			$this->instances[ $mode ] = new CSSCompression( $mode );
			$singleton = CSSCompression::getInstance( $mode );
			$singleton->mode( $mode );
		}

		// Pass along the initializer
		parent::__construct();
	}

	/**
	 * Central handler for running sheet tests
	 *
	 * @params none
	 */
	protected function sheets(){
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
			if ( ! preg_match( "/\.css$/", $file ) || in_array( $file, $this->block ) ) {
				continue;
			}

			// Configurations
			$this->reset();
			foreach ( $this->specials as $config ) {
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
			if ( ! preg_match( "/\.css$/", $file ) || in_array( $file, $this->block ) ) {
				continue;
			}

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
};

?>
