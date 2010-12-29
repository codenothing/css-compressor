<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
require( dirname(__FILE__) . '/../../src/CSSCompression.php');
require( dirname(__FILE__) . '/../src/Color.php');


Class CompressionBenchmark
{
	/**
	 * Benchmark Patterns
	 *
	 * @param (string) root: Path to this current directory
	 * @param (string) dist: Path to dist directory
	 * @param (string) compare: Path to regression comparison directory
	 * @param (string) version: Current version storage
	 * @param (bool) regressiontest: True if doing regression testing, false for just benchmarks
	 * @param (array) files: List of files read
	 * @param (array) averages: List of averages of compressions
	 * @param (array) instances: List of each modes instance
	 * @param (array) prev: prev marked comparison results
	 * @param (array) tainted: List of tainted files
	 * @param (array) regression: List of files in regression
	 * @param (array) gains: List of files with gains
	 */
	private $root = '';
	private $dist = '';
	private $compare = '';
	private $version = '';
	private $regressiontest = true;
	private $files = array();
	private $averages = array();
	private $instances = array();
	private $prev = array();
	private $tainted = array();
	private $regression = array();
	private $gains = array();

	/**
	 * Run compressions through every src file with each mode
	 *
	 * @params none;
	 */
	public function __construct(){
		$this->build();

		// Create the basice mode instances
		$modes = CSSCompression::modes();
		foreach( $modes as $mode => $config ) {
			$this->instances[ $mode ] = new CSSCompression( $mode );
			$this->averages[ $mode ] = array(
				'size-before' => 0,
				'size-after' => 0,
				'size-gzip' => 0,
				'time' => 0,
			);

			// Regression testing
			if ( $this->regressiontest ) {
				$this->instances[ $mode ]->option( 'readability', CSSCompression::READ_MAX );
			}
		}

		// Run Benchmarks
		$this->loadprev();
		$this->render();

		// If just doing benchmarks, clear out
		if ( ! $this->regressiontest ) {
			if ( strpos( CSSCompression::VERSION, 'VERSION' ) === false ) {
				$this->store();
			}
			return;
		}
		// Only store results in non-comparrison mode
		else if ( $this->conflicts() === false ) {
			echo "\n\n" . Color::boldred( "There are conflicts that need to be handled" ) . "\n\n";
		}
		else if ( $_SERVER['argv'][ 1 ] != 'temp' ) {
			echo "\n\n" . Color::boldred( "Only a comparrison run on " . $_SERVER['argv'][ 1 ] ) . "\n\n";
		}
		else {
			$this->store();
		}
	}

	/**
	 * Build the directory structure
	 *
	 * @params none
	 */
	private function build(){
		$this->root = dirname(__FILE__) . '/';
		$this->regressiontest = isset( $_SERVER['argv'][ 1 ] );
		$this->version = strpos( CSSCompression::VERSION, 'VERSION') === false ? CSSCompression::VERSION : 'temp-' . time();

		// Make the dist
		if ( ! is_dir( $this->root . 'dist/') ) {
			mkdir( $this->root . 'dist/' );
		}

		// Make version dist dump
		$this->dist = $this->root . 'dist/' . $this->version . '/';
		if ( ! is_dir( $this->dist ) ) {
			mkdir( $this->dist );
		}
	}

	/**
	 * Load the previous benchmark results for comparrison
	 *
	 * @params none
	 */
	private function loadprev(){
		if ( ! $this->regressiontest ) {
			return;
		}
		else if ( $_SERVER['argv'][ 1 ] != 'temp' ) {
			if ( file_exists( $this->root . 'results/' . $_SERVER['argv'][ 1 ] . '.json' ) ) {
				$this->compare = $this->root . 'dist/' . $_SERVER['argv'][ 1 ] . '/';
				$this->prev = json_decode( file_get_contents( $this->root . 'results/' . $_SERVER['argv'][ 1 ] . '.json' ), true );
			}
			else {
				throw new CSSCompression_Exception( "Unknown Benchmark " . $_SERVER['argv'][ 1 ] );
			}
		}
		else if ( file_exists( $this->root . 'dist/lastrun.txt' ) ) {
			$version = trim( file_get_contents( $this->root . 'dist/lastrun.txt' ) );
			if ( is_file( $this->root . "results/$version.json" ) ) {
				$this->compare = $this->root . 'dist/' . $version . '/';
				$this->prev = json_decode( file_get_contents( $this->root . "results/$version.json" ), true );
			}
			else {
				throw new CSSCompression_Exception( "Could not resolve last Benchmark ($version). Might have to clean first." );
			}
		}
		else {
			echo Color::yellow( 'No previous results found, running benchmark anyway.' ) . "\n";
		}
	}

	/**
	 * Handes overall compression needs
	 *
	 * @params none;
	 */
	private function render(){
		// Do benchmarks on every css file
		$handle = opendir( $this->root . 'src/' );
		while( ( $file = readdir( $handle ) ) !== false ) {
			if ( ! preg_match( "/\.css$/", $file ) ) {
				continue;
			}

			// Get content of file
			$css = file_get_contents( $this->root . 'src/' . $file );
			$output = '';

			// Some files might not exist when pulled from zengarden
			if ( ! $css || $css == '' ) {
				continue;
			}

			// Filemarker
			$output .= $file . "\n";
			
			// Do each instance
			foreach( $this->instances as $mode => $instance ) {
				list( $compression, $gzip, $info ) = $this->compress( $file, $css, $mode, $instance );
				$output .= "\t$mode:\t$info"
					. "\t$compression"
					. "\t$gzip\n";
			}
			$output .= Color::gray( "\n------------------------------\n" );

			echo $output;
		}

		// Final Averages
		echo "Average Savings:\n";
		foreach( $this->instances as $mode => $instance ) {
			$perc = number_format( $this->averages[ $mode ]['size-after'] / $this->averages[ $mode ]['size-before'] * 100, 2 );
			echo "\t$mode: " . Color::blue( $perc . "%" ) . "\t";
		}
		echo "\n";
		foreach( $this->instances as $mode => $instance ) {
			$perc = number_format( $this->averages[ $mode ]['size-gzip'] / $this->averages[ $mode ]['size-before'] * 100, 2 );
			echo "\tgzip: " . Color::blue( $perc . "%" ) . "\t";
		}
		echo "\n\n";
	}

	/**
	 * Individual mode on file compression
	 *
	 * @param (string) file: filename
	 * @param (string) css: File contents
	 * @param (string) mode: Instance mode
	 * @param (CSSCompression instance) instance: Compression instance
	 */
	private function compress( $file = '', $css = '', $mode = '', CSSCompression $instance ) {
		file_put_contents( $this->dist . $file . '.' . $mode . '.css', $instance->compress( $css ) );
		$gzip = gzencode( $instance->css, 1 );

		// References
		$before = $instance->stats['before'];
		$after = $instance->stats['after'];

		// Create the file array
		if ( ! isset( $this->files[ $file ] ) ) {
			$this->files[ $file ] = array();
		}

		// Store compression results
		$this->files[ $file ][ $mode ] = $instance->stats;
		$this->files[ $file ][ $mode ]['gzip'] = strlen( $gzip );

		// Log basic result for averages
		$this->averages[ $mode ]['size-before'] += $before['size'];
		$this->averages[ $mode ]['size-after'] += $after['size'];
		$this->averages[ $mode ]['size-gzip'] += strlen( $gzip );
		$this->averages[ $mode ]['time'] += $after['time'] - $before['time'];

		// Return formatted string result
		return array(
			// Compression results
			Color::yellow( $after['size'] )
			. '(' . Color::green( $after['size'] - $before['size'] ) . ')'
			. '[' . Color::blue( number_format( $after['size'] / $before['size'] * 100, 2 )  . '%' ) . '] ',

			// Gzip results
			'gzip: '
			. Color::yellow( strlen( $gzip ) )
			. '(' . Color::green( strlen( $gzip ) - $before['size'] ) . ')'
			. '[' . Color::blue( number_format( strlen( $gzip ) / $before['size'] * 100, 2 )  . '%' ) . '] ',

			// Compare to last benchmark
			$this->compare( $file, $mode, $this->files[ $file ][ $mode ] ),
		);
	}

	/**
	 * Mark file as tainted, and return colorized output
	 *
	 * @param (string) file: Filename
	 * @param (string) msg: Tainted message
	 */
	private function tainted( $file, $msg ){
		if ( ! in_array( $file, $this->tainted ) ) {
			array_push( $this->tainted, $file );
		}

		return Color::yellow( $msg );
	}

	/**
	 * Mark file as regressed, and return colorized output
	 *
	 * @param (string) file: Filename
	 * @param (string) msg: Regression message
	 */
	private function regression( $file, $msg ){
		if ( ! in_array( $file, $this->regression ) ) {
			array_push( $this->regression, $file );
		}

		return Color::red( $msg );
	}

	/**
	 * Mark file as a gain and return colorized output
	 *
	 * @param (string) file: Filename
	 * @param (string) msg: Regression message
	 */
	private function gains( $file, $msg ) {
		if ( ! in_array( $file, $this->gains ) ) {
			array_push( $this->gains, $file );
		}

		return Color::green( $msg );
	}

	/**
	 * Compare results to last benchmarks
	 *
	 * @param (string) file: Name of file being tested
	 * @param (string) mode: Compression mode
	 * @param (array) stats: COmpression results
	 */
	private function compare( $file, $mode, $stats ) {
		// Ignore if in benchmark test
		if ( ! $this->regressiontest ) {
			return '';
		}

		// Get true dist filename
		$filename = $file . ".$mode.css";

		// Initial sanity checks
		if ( ! $this->prev || ! isset( $this->prev['files'] ) ) {
			return '';
		}
		else if ( ! isset( $this->prev['files'][ $file ] ) ) {
			return $this->tainted( $filename, "Tainted Test - this file wasn't tested before" );
		}
		else if ( ! isset( $this->prev['files'][ $file ][ $mode ] ) ) {
			return $this->tainted( $filename, "Tainted Test - $mode wasn't tested before" );
		}

		// Do matching with previous benchmarks
		$prev = $this->prev['files'][ $file ][ $mode ];
		if ( $prev['before']['size'] !== $stats['before']['size'] ) {
			return $this->tainted( $filename, "Tainted Test - $file isn't the same size as before" );
		}
		else if ( $stats['after']['size'] > $prev['after']['size'] ) {
			return $this->regression( $filename, "Regression[" . ( $stats['after']['size'] - $prev['after']['size'] ) . "B]" );
		}
		else if ( $stats['gzip'] > $prev['gzip'] ) {
			return $this->regression( $filename, "Gzip Regression[" . ( $stats['gzip'] - $prev['gzip'] ) . "B]" );
		}
		else if ( $prev['after']['size'] > $stats['after']['size'] ) {
			return $this->gains( $filename, ( $prev['after']['size'] - $stats['after']['size'] ) . " Byte Gain!" );
		}
		else if ( $stats['after']['size'] === $prev['after']['size'] ) {
			return Color::gray( "No Change" );
		}
		else {
			return $this->regression( $filename, "Unknown Comparrison(Benchmark Error)." );
		}
	}

	/**
	 * Prints out comparrison results
	 *
	 * @params none
	 */
	private function conflicts(){
		$conflict = true;

		// File gains
		if ( count( $this->gains ) ) {
			echo Color::gray( "\n-------------------------\n" );
			echo Color::boldgreen( "There are " . count( $this->gains ) . " files with gains." ) . "\n";
			foreach ( $this->gains as $file ) {
				echo Color::green( $file ) . "\n";
			}
		}

		// Tainted files
		if ( count( $this->tainted ) ) {
			$conflict = false;
			echo Color::gray( "\n-------------------------\n" );
			echo Color::boldyellow( "There are " . count( $this->tainted ) . " tainted files." ) . "\n";
			foreach ( $this->tainted as $file ) {
				echo Color::yellow( $file ) . "\n";
			}
		}

		// Regressed files
		if ( count( $this->regression ) ) {
			$conflict = false;
			echo Color::gray( "\n-------------------------\n" );
			echo Color::boldred( "There are " . count( $this->regression ) . " files in regression." ) . "\n";
			foreach ( $this->regression as $file ) {
				echo Color::red( $this->dist . $file ) . "\n";
				echo 'diff ' . $this->compare . $file . ' ' . $this->dist . $file . "\n\n";
			}
		}

		return $conflict;
	}

	/**
	 * Store the current results
	 *
	 * @params none
	 */
	private function store(){
		$content = json_encode( array( 'files' => $this->files, 'averages' => $this->averages ) );

		// Push out results
		if ( file_put_contents( $this->root . "results/$this->version.json", $content ) === false ) {
			throw new CSSCompression( "Failed to store results file - " . $this->root . "results/$this->version.json" );
		}

		// Push out this version number for the records
		if ( file_put_contents( $this->root . 'dist/lastrun.txt', $this->version ) === false ) {
			throw new CSSCompression( "Failed to store lastrun file - " . $this->root . "results/$this->version.json" );
		}
	}
};


// Autostart the benchmarks
new CompressionBenchmark;

?>
