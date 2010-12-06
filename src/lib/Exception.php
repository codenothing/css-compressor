<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */

// Custom exception handler for CSSCompression
Class CSSCompression_Exception extends Exception
{
	public function __construct( $message = 'Unknown Exception', $code = 0, Exception $previous = NULL ) {
		parent::__construct( $message, $code, $previous );
	}

	public function __toString(){
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
};

?>
