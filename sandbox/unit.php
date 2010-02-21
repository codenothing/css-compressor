<?php
/**
 * CSS Compressor - Test Suite
 * September 5, 2009
 * Corey Hart @ http://www.codenothing.com
 */

// Before/After directories
define('BEFORE', dirname(__FILE__).'/files/before/');
define('AFTER', dirname(__FILE__).'/files/after/');


Class CSScompressionTestUnit
{
	/**
	 * Class Variables
	 *
	 * @param (int) errors: Number of errors found
	 */ 
	var $errors = 0;

	/**
	 * Constructor - runs the class
	 *
	 * @params none
	 */ 
	function __construct(){
		$this->mark('Start Test', 0, $this->errors == 0);
		$this->initialTrim();
		$this->lineTesting();
	}

	/**
	 * Runs a test on the initalTrim() method of CSSC, uses
	 * a before/after system of files to do matching
	 *
	 * @params none
	 */ 
	function initialTrim(){
		global $CSSC;
		$before = file_get_contents(BEFORE.'initialTrim.css');
		$after = file_get_contents(AFTER.'initialTrim.css');
		$this->mark('initialTrim.css', 'all', $CSSC->initialTrim($before) == trim($after));
	}

	/**
	 * Uses a test-array contain CSSC methods and various
	 * tests to run on each function. Takes special note to
	 * runSpecialCompressions() & lowercaseSelectors() methods
	 *
	 * @params none
	 */ 
	function lineTesting(){
		global $CSSC;
		include('line-testing.php');
		foreach ($testarr as $fn => $tests){
			$i = 0;
			foreach ($tests as $before => $after){
				if ($fn == 'runSpecialCompressions'){
					// Turn on hex2short option (Turned off by default as most browsers don't support them)
					$CSSC->options['color-hex2shortcolor'] = true;
					list ($prop, $val) = explode(':', $before);
					list ($prop, $val) = $CSSC->runSpecialCompressions($prop, $val);
					// Turn back off hex2short option
					$CSSC->options['color-hex2shortcolor'] = false;
					$passed = ("$prop:$val"==$after);
				}else if ($fn == 'lowercaseSelectors'){
					$CSSC->selectors = array($before);
					$CSSC->lowercaseSelectors();
					$passed = ($CSSC->selectors[0] == $after);
				}else{
					// Each function replaces all instances with compressed version of prop, so
					// add the remove multiply definitions for easier testing
					$passed = ($CSSC->removeMultipleDefinitions($CSSC->$fn($before)) == $after);
				}
				$this->mark($fn, $i++, $passed);
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
	function mark($method, $entry, $result){
		if ($result){
			$result = "<b style='color:green;'>Passed</b>";
		}else{
			$this->errors++;
			$result = "<b style='color:red;'>Failed</b>";
		}
		echo "<tr><td>$method</td><td>$entry</td><td>$result</td></tr>";
	}

	/**
	 * Destructor - Displays final errors
	 *
	 * @params none
	 */ 
	function __destruct(){
		$this->mark('<b>Total Errors</b>', '<b>'.$this->errors.'</b>', $this->errors == 0);
	}
}
?>
