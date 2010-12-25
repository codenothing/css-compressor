<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */
error_reporting( -1 );
require( dirname( __FILE__ ) . '/src/Core.php' );



/**
 * Temporary files to block in unit test until fix is found
 *
 * @param (array) temp: Files that are only temporarily blocked until fix is found
 * @param (array) only: For testing, focus only on this set of test files
 */
$block = array(
);


/**
 * Special handling of test sheets. All base options are set to true before
 * these special cases are applied
 *
 * @entry (array) files: List of test files that apply to this special
 * @entry (string) mode: Mode of compression
 * @entry (array) options: Any secondary options ontop of the mode set
 */
$specials = array(
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
 * Start the unit tests along with the blockers and specials
 *
 * @param (array) block: Files that are only temporarily blocked until fix is found
 * @param (array) specials: Special handling for certain sheets
 */
new CSScompression_Unit_Core( $block, $specials );

?>
