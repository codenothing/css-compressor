<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Setup
{
	/**
	 * Compression Variables
	 *
	 * @param (string) css: Holds compressed css string
	 * @param (array) selectors: Holds CSS Selectors
	 * @param (array) details: Holds definitions of selectors
	 * @param (array) options: Holds compression options
	 * @param (array) stats: Holds compression stats
	 * @param (boolean) media: Media is present
	 * @param (string) mode: Current compression mode state
	 */ 
	protected $css = '';
	protected $selectors = array();
	protected $details = array();
	protected $options = array();
	protected $stats = array();
	protected $media = false;
	protected $media_str = '';
	protected $import_str = '';
	protected $_mode = '';

	/**
	 * Look behind regex's to check for escaped characters
	 *
	 * @param (regex) r_semicolon: Checks for semicolon without an escape ('\') character before it
	 * @param (regex) r_colon: Checks for colon without an escape ('\') character before it
	 * @param (regex) r_space: Checks for space without an escape ('\') character before it
	 */ 
	protected $r_semicolon = "/(?<!\\\);/";
	protected $r_colon = "/(?<!\\\):/";
	protected $r_space = "/(?<!\\\)\s/";

	/**
	 * Just passes along the initializer
	 */
	protected function __construct( $css = NULL, $options = NULL ) {
		// Setup the options
		$this->resetOptions();

		// Automatically compress css if passed
		if ( $css && $css !== '' ) {
			$this->compress( $css, $options );
		}
		else if ( $options ) {
			$this->mergeOptions( $options );
		}
	}

	/**
	 * Centralized function to run css compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	public function compress( $css, $prefs = array() ) {
		// Flush out variables
		$this->flush();
		$this->mergeOptions( $prefs );

		// Start the timer
		$this->stats['before']['time'] = array_sum( explode( ' ', microtime() ) );
		$this->stats['before']['size'] = strlen( $css );

		// Send body through initial trimings for setupd
		$this->css = $this->trim( $css );

		// Do a little tokenizing, compress each property individually
		$this->setup();

		// Mark number of selectors pre-combine
		$this->stats['before']['selectors'] = count( $this->selectors );

		// Do selector specific compressions
		$this->selectors = $this->selectorCompression( $this->selectors );

		// Look at each group of properties as a whole, and compress/combine similiar definitions
		list( $this->selectors, $this->details ) = $this->combine( $this->selectors, $this->details );

		// If order isn't important, run comination functions before and after compressions to catch all instances
		if ( $this->options['multiple-selectors'] && $this->options['multiple-details'] ) {
			list( $this->selectors, $this->details ) = $this->organize( $this->selectors, $this->details );
		}

		// Do final maintenace work, remove injected slashes and property/values
		list( $this->selectors, $this->details ) = $this->cleanup( $this->selectors, $this->details );

		// Format css to users preference
		$this->css = $this->readability( $this->import_str, $this->options['readability'] );

		// Add media string to top
		// TODO: Compress media individually like full css sheets
		if ( $this->media_str ) {
			$this->media = true;
			$this->css = $this->media_str . $this->css;
		}

		// Run final statistics before sending back the css
		$this->runFinalStatistics();

		// Return compressed css
		return $this->css;
	}

	/**
	 * Clear class variables (but not options)
	 *
	 * @params none;
	 */ 
	protected function flush(){
		$this->css = '';
		$this->import_str = '';
		$this->media_str = '';
		$this->media = false;
		$this->selectors = array();
		$this->details = array();
		$this->stats = array(
			'before' => array(
				'props' => 0
			), 
			'after' => array(
				'props' => 0
			),
		);
	}

	/**
	 * Setup selector and details arrays for compression methods
	 *
	 * @params none
	 */ 
	protected function setup(){
		// Seperate the element from the elements details
		$css = explode( "\n", $this->css );
		$SEL_COUNTER = 0;

		foreach ( $css as $details ) {
			$details = trim( $details );
			// Determine whether your looking at the details or element
			if ( $this->media && $details == '}' ) {
				$this->media_str .= "}\n";
				$this->media = false;
			}
			else if ( $this->media ) {
				$this->media_str .= $details;
			}
			else if ( strpos( $details, '{') === 0 ) {
				$details = substr( $details, 1, strlen( $details ) - 2 );
				$details = preg_split( $this->r_semicolon, $details );
				$parts = array();
				$storage = '';

				foreach ( $details as $line ) {
					if ( preg_match( "/^(url|@import)/i", $line ) ) {
						$storage .= $line.";";
						continue;
					}

					// Grab the property and its value
					unset( $property, $value );
					$parts = preg_split( $this->r_colon, $line, 2 );

					// Property
					if ( isset( $parts[ 0 ] ) && ( $parts[ 0 ] = trim( $parts[ 0 ] ) ) != '' ) {
						$property = $parts[ 0 ];
					}

					// Value
					if ( isset( $parts[ 1 ] ) && ( $parts[ 1 ] = trim( $parts[ 1 ] ) ) != '' ) {
						$value = $parts[1];
					}

					// Fail safe, remove unspecified property/values
					if ( ! isset( $property ) || ! isset( $value ) ) {
						continue;
					}

					// Run the tag/element through each compression
					list ( $property, $value ) = $this->individuals( $property, $value );

					// Add counter to before stats
					$this->stats['before']['props']++;

					// Store the compressed element
					$storage .= "$property:$value;";
				}
				// Store as the last known selector
				$this->details[ $SEL_COUNTER ] = $storage;
			}
			else if ( strpos( $details, '@import' ) === 0 || strpos( $details, '@charset' ) === 0 ) {
				// Seperate out each import string
				$arr = preg_split( $this->r_semicolon, $details );

				// Add to selector counter for details storage
				$SEL_COUNTER++;
				// Store the last entry as the selector
				$this->selectors[ $SEL_COUNTER ] = trim( $arr[ count( $arr ) - 1 ] );

				// Clear out the last entry(the actual selector) and add to the import string
				unset( $arr[ count( $arr ) - 1 ] );
				$this->import_str .= trim( implode( ';', $arr ) ) . ';';
			}
			else if ( strpos( $details, '@' ) === 0 ){
				$this->media = true;
				$this->media_str .= $details;
			}
			else if ( $details ) {
				// Add to selector counter for details storage
				$SEL_COUNTER++;
				$this->selectors[ $SEL_COUNTER ] = $details;
			}
		}
	}

	/**
	 * Runs final counts on selectors and props
	 *
	 * @params none
	 */ 
	protected function runFinalStatistics(){
		// Selectors and props
		$this->stats['after']['selectors'] = count( $this->selectors );
		foreach ( $this->details as $item ) {
			$props = preg_split( $this->r_semicolon, $item );

			// Make sure count is true
			foreach ( $props as $k => $v ) {
				if ( ! isset( $v ) || $v == '' ) {
					unset( $props[ $k ] );
				}
			}
			$this->stats['after']['props'] += count( $props );
		}

		// Final count for stats
		$this->stats['after']['size'] = strlen( $this->css );
		$this->stats['after']['time'] = array_sum( explode( ' ', microtime() ) );
	}

	protected static function getJSON( $file ) {
		$json = file_get_contents( dirname(__FILE__) . '/../helpers/' . $file );
		$json = json_decode( $json, true );
		return $json;
	}
};

?>
