<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
require( dirname(__FILE__) . '/../../src/CSSCompression.inc');
require( dirname(__FILE__) . '/../color.php');


Class CompressionBenchmark
{
	private $root = '';
	private $files = array();
	private $averages = array();
	private $instances = array();

	public function __construct(){
		$this->root = dirname(__FILE__) . '/';

		// Create the basice mode instances
		foreach( CSSCompression::$modes as $mode => $config ) {
			$this->instances[ $mode ] = new CSSCompression( NULL, $mode );
			$this->averages[ $mode ] = array(
				'size-before' => 0,
				'size-after' => 0,
				'size-gzip' => 0,
				'time' => 0,
			);
		}

		// Make the dist
		if ( ! is_dir( $this->root . 'dist/') ) {
			mkdir( $this->root . 'dist/' );
		}

		// Run tests
		$this->render();
	}

	private function render(){
		// Do benchmarks on every css file
		$handle = opendir( $this->root . 'src/' );
		while( ( $file = readdir( $handle ) ) !== false ) {
			if ( ! preg_match( "/\.css$/", $file ) ) {
				continue;
			}

			// Get content of file
			$rcomp = '';
			$rgzip = '';
			$css = file_get_contents( $this->root . 'src/' . $file );

			// Some files might not exist when pulled from zengarden
			if ( ! $css || $css == '' ) {
				continue;
			}

			// Do each instance
			foreach( $this->instances as $mode => $instance ) {
				$parts = $this->compress( $file, $css, $instance );
				$rcomp .= "\t" . $parts[ 0 ];
				$rgzip .= "\t" . $parts[ 1 ];
			}

			// Print result to terminal to show progress
			echo $file . "\n" . $rcomp . "\n" . $rgzip . "\n";
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


		// Store results into json object
		file_put_contents( $this->root . 'dist/results.json', json_encode( array( 'files' => $this->files, 'averages' => $this->averages ) ) );
	}

	private function compress( $file = '', $css = '', $instance ) {
		file_put_contents( $this->root . 'dist/' . $file . '.' . $instance->mode, $instance->compress( $css ) );
		$gzip = gzencode( $instance->css );

		// References
		$before = $instance->stats['before'];
		$after = $instance->stats['after'];

		// Create the file array
		if ( ! isset( $this->files[ $file ] ) ) {
			$this->files[ $file ] = array();
		}

		// Store compression results
		$this->files[ $file ][ $instance->mode ] = $instance->stats;

		// Log basic result for averages
		$this->averages[ $instance->mode ]['size-before'] += $before['size'];
		$this->averages[ $instance->mode ]['size-after'] += $after['size'];
		$this->averages[ $instance->mode ]['size-gzip'] += strlen( $gzip );
		$this->averages[ $instance->mode ]['time'] += $after['time'] - $before['time'];

		// Return formatted string result
		return array(
			$instance->mode . ': '
			. Color::yellow( $after['size'] )
			. '(' . Color::green( $after['size'] - $before['size'] ) . ')'
			. '[' . Color::blue( number_format( $after['size'] / $before['size'] * 100, 2 )  . '%' ) . '] ',

			'gzip: '
			. Color::yellow( strlen( $gzip ) )
			. '(' . Color::green( strlen( $gzip ) - $before['size'] ) . ')'
			. '[' . Color::blue( number_format( strlen( $gzip ) / $before['size'] * 100, 2 )  . '%' ) . '] ',
		);
	}
};


new CompressionBenchmark;

?>
