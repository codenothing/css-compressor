<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
require( dirname(__FILE__) . '/../src/css-compression.php');


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
			if ( preg_match( "/\.css$/", $file ) ) {
				$css = file_get_contents( $this->root . 'src/' . $file );
				$result = '';

				foreach( $this->instances as $mode => $instance ) {
					$result .= "\t" . $this->compress( $file, $css, $instance );
				}

				// Print result to terminal to show progress
				echo $file . $result . "\n";
			}
		}

		file_put_contents( $this->root . 'dist/results.json', json_encode( array( 'files' => $this->files ) ) );
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

		return $instance->_mode . ': '
			. $after['size']
			. '(' . ( $after['size'] - $before['size'] ) . ')'
			. '[' . number_format( $after['size'] / $before['size'] * 100, 2 ) . '%] ';
	}
};


new CompressionBenchmark;

?>
