<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
require( dirname(__FILE__) . '/../../src/css-compression.php');
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
			$this->averages[ $mode ] = array();
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
		$handle = opendir( $this->root . 'src/');
		while( ( $file = readdir( $handle ) ) !== false ) {
			if ( ! preg_match( "/\.css$/", $file ) ) {
				continue;
			}

			// Get content of file
			$result = '';
			$css = file_get_contents( $this->root . 'src/' . $file );

			// Some files might not exist when pulled from zengarden
			if ( ! $css || $css == '' ) {
				continue;
			}

			// Do each instance
			foreach( $this->instances as $mode => $instance ) {
				$result .= "\t" . $this->compress( $file, $css, $instance );
			}

			// Print result to terminal to show progress
			echo $file . "\n" . $result . "\n";
		}

		// Final Averages
		echo "Average Savings:\n";
		foreach( $this->instances as $mode => $instance ) {
			$bytes = intval( $this->averages[ $mode ]['size-after'] / count( $this->files ) );
			$perc = number_format( $this->averages[ $mode ]['size-after'] / $this->averages[ $mode ]['size-before'] * 100, 2 );
			echo "\t\t$mode: " . Color::green( '-' . $bytes ) . "[" . Color::blue( $perc . "%" ) . "]";
		}
		echo "\n\n";


		// Store results into json object
		file_put_contents( $this->root . 'dist/results.json', json_encode( array( 'files' => $this->files, 'averages' => $this->averages ) ) );
	}

	private function compress( $file = '', $css = '', $instance ) {
		file_put_contents( $this->root . 'dist/' . $file . '.' . $instance->_mode, $instance->compress( $css ) );

		// References
		$before = $instance->stats['before'];
		$after = $instance->stats['after'];

		// Create the file array
		if ( ! isset( $this->files[ $file ] ) ) {
			$this->files[ $file ] = array();
		}

		// Store compression results
		$this->files[ $file ][ $instance->_mode ] = $instance->stats;

		// Log basic result for averages
		$this->averages[ $instance->_mode ]['size-before'] += $before['size'];
		$this->averages[ $instance->_mode ]['size-after'] += $after['size'];
		$this->averages[ $instance->_mode ]['time'] += $after['time'] - $before['time'];

		// Return formatted string result
		return $instance->_mode . ': '
			. Color::yellow( $after['size'] )
			. '(' . Color::green( $after['size'] - $before['size'] ) . ')'
			. '[' . Color::blue( number_format( $after['size'] / $before['size'] * 100, 2 )  . '%' ) . '] ';
	}
};


new CompressionBenchmark;

?>
