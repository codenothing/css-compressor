<?php
/**
 * CSS Compressor [VERSION] - Test Suite
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */

// Before/After directories
define('BEFORE', dirname(__FILE__).'/files/before/');
define('AFTER', dirname(__FILE__).'/files/after/');


Class CSScompressionTestUnit Extends CSSCompression
{
	/**
	 * Class Variables
	 *
	 * @param (int) errors: Number of errors found
	 */ 
	private $errors = 0;

	/**
	 * Constructor - runs the class
	 *
	 * @params none
	 */ 
	public function __construct(){
		parent::__construct('');
		$this->setOptions();
		$this->mark('Start Test', 0, $this->errors == 0);
		$this->initialTrimTest();
		$this->lineTesting();
		$this->testSemicolon();
		$this->testSelectorCombination();
		$this->testDetailCombination();
	}

	/**
	 * Turn all options to true to test every possible function
	 *
	 * @params none
	 */ 
	private function setOptions(){
		foreach ($this->options as $key => $value)
			$this->options[$key] = true;
	}

	/**
	 * Runs a test on the initalTrim() method of CSSC, uses
	 * a before/after system of files to do matching
	 *
	 * @params none
	 */ 
	private function initialTrimTest(){
		$this->css = file_get_contents(BEFORE . 'initialTrim.css');
		$after = file_get_contents(AFTER . 'initialTrim.css');
		$this->initialTrim();
		$this->mark('initialTrim.css', 'all', trim($this->css) == trim($after));
	}

	/**
	 * Uses a test-array contain CSSC methods and various
	 * tests to run on each function. Takes special note to
	 * runSpecialCompressions() & lowercaseSelectors() methods
	 *
	 * @params none
	 */ 
	private function lineTesting(){
		include('line-testing.php');
		foreach ($testarr as $fn => $tests){
			$i = 0;
			foreach ($tests as $before => $after){
				if ($fn == 'runSpecialCompressions'){
					list ($prop, $val) = explode(':', $before);
					list ($prop, $val) = $this->runSpecialCompressions($prop, $val);
					$passed = ("$prop:$val"==$after);
				}else if ($fn == 'lowercaseSelectors'){
					$this->selectors = array($before);
					$this->$fn();
					$passed = ($this->selectors[0] == $after);
				}else if ($fn == 'removeUnnecessarySemicolon'){
					$this->details = array($before);
					$this->$fn();
					$passed = ($this->details[0] == $after);
				}else{
					// Each function replaces all instances with compressed version of prop, so
					// add the remove multiply definitions for easier testing
					$passed = ($this->$fn($before) == $after);
				}
				$this->mark($fn, $i++, $passed);
			}
		}
	}

	/**
	 * Runs unit testing on ending semicolon removal
	 *
	 * @params none
	 */ 
	private function testSemicolon(){
		$this->details = array(
			'color:blue;',
			'color:blue;font-size:12pt;',
		);
		$after = array(
			'color:blue',
			'color:blue;font-size:12pt',
		);
		$this->removeUnnecessarySemicolon();
		$max = array_pop(array_keys($this->details))+1;
		for ($i=0; $i<$max; $i++){
			$this->mark('Unnecessary Semicolons', $i, ($this->details[$i] === $after[$i]));
		}
	}

	/**
	 * Runs unit testing on the selector combination
	 *
	 * @params none
	 */ 
	private function testSelectorCombination(){
		include(BEFORE . 'combineMultiplyDefinedSelectors.php');
		$this->selectors = $selectors;
		$this->details = $details;
		$this->combineMultiplyDefinedSelectors();
		include(AFTER . 'combineMultiplyDefinedSelectors.php');
		$max = array_pop(array_keys($this->selectors))+1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( isset( $this->selectors[ $i ] ) && isset( $this->details[ $i ] ) ) {
				$this->mark(
					'Selector Combination', 
					$i, 
					( $this->selectors[ $i ] === $selectors[ $i ] && $this->details[ $i ] === $details[ $i ] )
				);
			}
		}
	}

	/**
	 * Runs unit testing on the details combination
	 *
	 * @params none
	 */ 
	private function testDetailCombination(){
		include(BEFORE . 'combineMultiplyDefinedDetails.php');
		$this->selectors = $selectors;
		$this->details = $details;
		$this->combineMultiplyDefinedDetails();
		include(AFTER . 'combineMultiplyDefinedDetails.php');
		$max = array_pop(array_keys($this->selectors))+1;
		for ($i=0; $i<$max; $i++){
			if ( isset( $this->selectors[ $i ] ) && isset( $this->details[ $i ] ) ) {
				$this->mark(
					'Selector Combination',
					$i,
					( $this->selectors[ $i ] === $selectors[ $i ] && $this->details[ $i ] === $details[ $i ] )
				);
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
	private function mark($method, $entry, $result){
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
	public function __destruct(){
		$this->mark('<b>Total Errors</b>', '<b>'.$this->errors.'</b>', $this->errors == 0);
	}
}

?>
