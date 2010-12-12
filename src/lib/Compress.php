<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Compress
{
	/**
	 * Trim Patterns
	 *
	 * @class Control: Compression Controller
	 * @class Trim: Trim Instance
	 * @class Setup: Setup Instance
	 * @class Format: Formatting Instance
	 * @class Combine: Combine Instance
	 * @class Cleanup: Cleanup Instance
	 * @class Organize: Organize Instance
	 * @class Selectors: Selectors Instance
	 * @param (array) options: Reference to options
	 * @param (array) stats: Reference to stats
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 * @param (regex) rcolon: Checks for colon without an escape '\' character before it
	 * @param (regex) rspace: Checks for space without an escape '\' character before it
	 */
	private $Control;
	private $Trim;
	private $Setup;
	private $Format;
	private $Combine;
	private $Cleanup;
	private $Organize;
	private $Selectors;
	private $options = array();
	private $stats = array();
	private $rsemicolon = "/(?<!\\\);/";
	private $rcolon = "/(?<!\\\):/";
	private $rspace = "/(?<!\\\)\s/";

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->Trim = $control->Trim;
		$this->Setup = $control->Setup;
		$this->Format = $control->Format;
		$this->Combine = $control->Combine;
		$this->Cleanup = $control->Cleanup;
		$this->Organize = $control->Organize;
		$this->Selectors = $control->Selectors;
		$this->options = &$control->Option->options;
		$this->stats = &$control->stats;
	}

	/**
	 * Centralized function to run css compression.
	 *
	 * @param (string) css: Stylesheet to compresss
	 */ 
	public function compress( $css ) {
		$setup = $this->setup( $css );

		// Do selector specific compressions
		$this->Selectors->selectors( $setup['selectors'] );

		// Look at each group of properties as a whole, and compress/combine similiar definitions
		$this->Combine->combine( $setup['selectors'], $setup['details'] );

		// If order isn't important, run comination functions before and after compressions to catch all instances
		// Be sure to prune before hand for higher chance of matching
		if ( $this->options['organize'] ) {
			$this->Cleanup->cleanup( $setup['selectors'], $setup['details'] );
			$this->Organize->organize( $setup['selectors'], $setup['details'] );
			$this->Combine->combine( $setup['selectors'], $setup['details'] );
		}

		// Do final maintenace work, remove injected property/values
		$this->Cleanup->cleanup( $setup['selectors'], $setup['details'] );

		// Run final counters before full cleanup
		$this->finalCount( $setup['selectors'], $setup['details'] );

		// Format css to users preference
		$css = $this->Format->readability( $this->options['readability'], $setup['selectors'], $setup['details'] );

		// Intros
		foreach ( $setup as $value ) {
			if ( $value && is_string( $value ) ) {
				$css = $value . $css;
			}
		}

		// Remove escapables
		$css = $this->Cleanup->removeInjections( $css );

		// Attach plea to top of page with unknown blocks
		if ( $this->options['add-unknown'] && count( $setup['unknown'] ) ) {
			$css = "/*\nThere are unknown blocks in the sheet, please please please open an issue with your sheet attached to it:\n"
				. "https://github.com/codenothing/css-compressor/issues\n"
				. "Thank You --\n\n"
				. implode( "\n", $setup['unknown'] )
				. "\n*/\n"
				. $css;
		}

		// Mark final file size
		$this->stats['after']['size'] = strlen( $css = trim( $css ) );

		// Return compressed css
		return $css;
	}

	/**
	 * Runs css through initial setup handlers
	 *
	 * @param (string) css: Sheet to compress
	 */
	private function setup( $css ) {
		// Initial stats
		$this->stats['before']['time'] = microtime( true );
		$this->stats['before']['size'] = strlen( $css );

		// Initial trimming
		$css = $this->Trim->trim( $css );

		// Do a little tokenizing, compress each property individually
		$setup = $this->Setup->setup( $css );

		// Mark number of selectors pre-combine
		$this->stats['before']['selectors'] = count( $setup['selectors'] );

		return $setup;
	}

	/**
	 * Runs final counts on selectors and props
	 *
	 * @param (array) selectors: Selector rules
	 * @param (array) details: Rule sets
	 */ 
	private function finalCount( $selectors, $details ) {
		// Selectors and props
		$this->stats['after']['selectors'] = count( $selectors );
		foreach ( $details as $item ) {
			$props = preg_split( $this->rsemicolon, $item );

			// Make sure count is true
			foreach ( $props as $k => $v ) {
				if ( ! isset( $v ) || $v == '' ) {
					unset( $props[ $k ] );
				}
			}
			$this->stats['after']['props'] += count( $props );
		}

		// Final count for stats
		$this->stats['after']['time'] = microtime( true );
	}

	/**
	 * Access to private methods for testing
	 *
	 * @param (string) method: Method to be called
	 * @param (array) args: Array of paramters to be passed in
	 */
	public function access( $method, $args ) {
		if ( method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}
		else {
			throw new CSSCompression_Exception( "Unknown method in Color Class - " . $method );
		}
	}
};

?>
