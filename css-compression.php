<?php
/**
 * CSS Compressor 1.0
 * September 5, 2009
 * Corey Hart @ http://www.codenothing.com
 *
 * Credit to Martin Zvarík @ http://www.teplaky.net/ for pointing out the url and emtpy definition bug.
 */ 

// Define path to vars directory
define('CSSC_VARS_DIR', dirname(__FILE__).'/vars/');


Class CSSCompression
{
	/**
	 * Class Variables
	 *
	 * @param (array) selectors: Holds CSS Selectors
	 * @param (array) details: Holds definitions of selectors
	 * @param (array) options: Holds compression options
	 * @param (array) stats: Holds compression stats
	 * @param (boolean) media: Media is present
	 */ 
	var $selectors = array();
	var $details = array();
	var $options = array();
	var $stats = array();
	var $media = false;

	/**
	 * Extend the default options with user defined POST vars.
	 *
	 * @params none
	 */ 
	function __construct(){
		// Converts long color names to short hex names (aliceblue -> #f0f8ff)
		$this->options['color-long2hex'] = true;

		// Converts rgb colors to hex (rgb(159,80,98) -> #9F5062, rgb(100%) -> #FFFFFF)
		$this->options['color-rgb2hex'] = true;

		// Converts long hex codes to short color names (#f5f5dc -> beige)
		// Only works on latest browsers, careful when using
		$this->options['color-hex2shortcolor'] = false;

		// Converts long hex codes to short hex codes (#44ff11 -> #4f1)
		$this->options['color-hex2shorthex'] = true;

		// Converts font-weight names to numbers (bold -> 700)
		$this->options['fontweight2num'] = true;

		// Removes zero decimals and 0 units (15.0px -> 15px || 0px -> 0)
		$this->options['format-units'] = true;

		// Lowercases html tags from list (BODY -> body)
		$this->options['lowercase-selectors'] = true;

		// Compresses single defined multi-directional properties (margin: 15px 25px 15px 25px -> margin:15px 25px)
		$this->options['directional-compress'] = true;

		// Combines multiply defined selectors (p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;})
		$this->options['multiple-selectors'] = true;

		// Combines selectors with same details (p{color:blue;} a{color:blue;} -> p,a{color:blue;})
		$this->options['multiple-details'] = true;

		// Combines color/style/width properties (border-style:dashed;border-color:black;border-width:4px; -> border:4px dashed black)
		$this->options['csw-combine'] = true;

		// Combines cue/pause properties (cue-before: url(before.au); cue-after: url(after.au) -> cue:url(before.au) url(after.au))
		$this->options['auralcp-combine'] = true;

		// Combines margin/padding directionals (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -> margin:10px 5px 4px 1px;)
		$this->options['mp-combine'] = true;

		// Combines border directionals (border-top|right|bottom|left:1px solid black -> border:1px solid black)
		$this->options['border-combine'] = true;

		// Combines font properties (font-size:12pt; font-family: arial; -> font:12pt arial)
		$this->options['font-combine'] = true;

		// Combines background properties (background-color: black; background-image: url(bgimg.jpeg); -> background:black url(bgimg.jpeg))
		$this->options['background-combine'] = true;

		// Combines list-style properties (list-style-type: round; list-style-position: outside -> list-style:round outside)
		$this->options['list-combine'] = true;

		// Removes multiply defined properties
		// STRONGLY SUGGESTED TO KEEP THIS TRUE
		$this->options['rm-multi-define'] = true;

		// Readibility of Compressed Output
		$this->options['readability'] = 0;

		// Merge Preferences against defaults
		if ($_POST && count($_POST)){
			$opts = explode(',', 'color-long2hex,color-rgb2hex,color-hex2shortcolor,color-hex2shorthex,fontweight2num,format-units,lowercase-selectors,directional-compress,multiple-selectors,multiple-details,csw-combine,auralcp-combine,mp-combine,border-combine,font-combine,background-combine,list-combine,rm-multi-define,readability');
			foreach ($opts as $key)
				$this->options[$key] = ($_POST[$key] && $_POST[$key] == 'on') ? true : intval($_POST[$key]);
		}
	}

	/**
	 * Cetralized function to run css compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	function compress($css){
		// Start the timer
		$time = explode(' ', microtime());
		$this->stats['before']['time'] = $time[1] + $time[0];

		// Initial count for stats
		$this->stats['before']['size'] = strlen($css);

		// Send body through initial trimings
		$css = $this->initialTrim($css);

		// Seperate the element from the elements details
		$css = explode("\n", $css);
		foreach ($css as $details){
			$details = trim($details);
			// Determine whether your looking at the details or element
			if ($this->media && $details == '}'){
				$MEDIA_STR .= "}\n";
				$this->media = false;
			}
			else if ($this->media){
				$MEDIA_STR .= $details;
			}
			else if (strpos($details, '{') === 0){
				unset($storage);
				$details = substr($details, 1, strlen($details)-2);
				$details = explode(';', $details);
				foreach($details as $line){
					if (preg_match("/^(url|@import)/i", $line)){
						$storage .= $line.";";
						continue;
					}
					list ($property, $value) = explode(':', $line, 2);

					// Fail safe, remove unknown tag/elements
					if (!isset($property) || !isset($value))
						continue;

					// Run the tag/element through each compression
					list ($property, $value) = $this->runSpecialCompressions($property, $value);

					// Add counter to before stats
					$this->stats['before']['props']++;

					// Store the compressed element
					$storage .= "$property:$value;";
				}
				// Store as the last known selector
				$this->details[$SEL_COUNTER] = $storage;
			}
			else if (strpos($details, '@import') === 0){
				// Seperate out each import string
				$arr = explode(';', $details);

				// Add to selector counter for details storage
				$SEL_COUNTER++;
				// Store the last entry as the selector
				$this->selectors[$SEL_COUNTER] = trim($arr[count($arr)-1]);

				// Clear out the last entry(the actual selector) and add to the import string
				unset($arr[count($arr)-1]);
				$IMPORT_STR .= trim(implode(';', $arr)).';';
			}
			else if (strpos($details, '@media') === 0){
				$this->media = true;
				$MEDIA_STR .= $details;
			}
			else if ($details){
				// Add to selector counter for details storage
				$SEL_COUNTER++;
				$this->selectors[$SEL_COUNTER] = $details;
			}
		}
		// Store the number of selectors before compression
		$this->stats['before']['selectors'] = count($this->selectors);

		// Compression Functions
		if ($this->options['lowercase-selectors']) 	$this->lowercaseSelectors();
		if ($this->options['multiple-selectors']) 	$this->combineMultiplyDefinedSelectors();
		if ($this->options['multiple-details']) 	$this->combineMuliplyDefinedDetails();
		foreach ($this->details as &$value){
			if ($this->options['csw-combine'])		$value = $this->combineCSWproperties($value);
			if ($this->options['auralcp-combine'])		$value = $this->combineAuralCuePause($value);
			if ($this->options['mp-combine']) 		$value = $this->combineMPproperties($value);
			if ($this->options['border-combine']) 		$value = $this->combineBorderDefinitions($value);
			if ($this->options['font-combine']) 		$value = $this->combineFontDefinitions($value);
			if ($this->options['background-combine']) 	$value = $this->combineBackgroundDefinitions($value);
			if ($this->options['list-combine']) 		$value = $this->combineListProperties($value);
			if ($this->options['rm-multi-define']) 		$value = $this->removeMultipleDefinitions($value);
		}

		// Run final stats
		$this->runFinalStatistics();

		// Format css to users preference
		$css = $this->readability($IMPORT_STR, $this->options['readability']);

		// Add media string with comments to compress seperately
		if ($MEDIA_STR){
			$this->media = true;
			$css = "/** Media Types are not compressed with this script, cut out and compress each section seperately **/"
				."\n$MEDIA_STR\n\n/** The rest of your CSS File **/\n$css";
		}

		// Final count for stats
		$this->stats['after']['size'] = strlen($css);

		// Compression time
		$time = explode(' ', microtime());
		$this->stats['after']['time'] = $time[1] + $time[0];

		// Return compressed css
		return $css;
	}

	/**
	 * Runs initial formatting to setup for compression
	 *
	 * @param (string) css: CSS Contents
	 */ 
	function initialTrim($css){
		// Regex
		$search = array(
			1 => "/(\/\*|\<\!\-\-)(.*?)(\*\/|\-\-\>)/s", // Remove all comments
			2 => "/(\s+)?([,{};:>\+])(\s+)?/s", // Remove un-needed spaces around special characters
			3 => "/url\(['\"](.*?)['\"]\)/s", // Remove quotes from urls
			4 => "/;{2,}/is", // Remove unecessary semi-colons
			5 => "/\s+/s", // Compress all spaces into single space
			// Leave section open for additional entries

			// Break apart elements for setup of further compression
			20 => "/{/",
			21 => "/}/",
		);

		// Replacements
		$replace = array(
			1 => ' ',
			2 => '$2',
			3 => 'url($1)',
			4 => ';',
			5 => ' ',
			// Leave section open for additional entries

			// Add new line for setup of further compression
			20 => "\n{",
			21 => "}\n",
		);

		// Run replacements
		return trim(preg_replace($search, $replace, $css));
	}

	/**
	 * Runs special unit/directional compressions
	 *
	 * @param (string) prop: CSS Property
	 * @param (string) val: Value of CSS Property
	 */ 
	function runSpecialCompressions($prop, $val){
		// Properties should always be lowercase
		$prop = strtolower($prop);

		// Remove uneeded side definitions if possible
		if ($this->options['directional-compress'] && preg_match("/^(margin|padding)/i", $prop)){
			$val = $this->sidesDirectional($val);
		}

		// Font-weight converter
		if ($this->options['fontweight2num'] && $prop === 'font-weight'){
			// Static so it won't be re-included every loop
			static $fontweight2num;
			if (! $fontweight2num)
				include(CSSC_VARS_DIR . 'fontweight2num.php');

			// All font-weights are lower-case
			$low = strtolower($val);
			if (isset($fontweight2num[$low]))
				$val = $fontweight2num[$low];
		}

		// Remove uneeded decimals/units
		if ($this->options['format-units']){
			$val = $this->removeDecimal($val);
			$val = $this->removeUnits($val);
		}

		// Seperate out by multi-values if possible
		$arr = explode(' ', $val);
		foreach ($arr as $k=>$v)
			$arr[$k] = $this->runColorChanges($v);
		$val = trim(implode(' ', $arr));

		// Return for list retrival
		return array($prop, $val);
	}

	/**
	 * Finds directional compression on methods like margin/padding
	 *
	 * @param (string) val: Value of CSS Property
	 */ 
	function sidesDirectional($val){
		// Check if side definitions already reduced down to a single definition
		if (strpos($val, ' ') === false){
			// Redundent, but just in case
			if ($this->options['format-units']){
				$val = $this->removeDecimal($val);
				$val = $this->removeUnits($val);
			}
			return $val;
		}

		// Split up each definiton
		$direction = explode(" ", $val);

		// Zero out and remove units if possible
		if ($this->options['format-units']){
			foreach ($direction as &$v)
				$v = $this->removeDecimal($this->removeUnits($v));
		}

		// 4 Direction reduction
		$count = count($direction);
		if ($count == 4){
			if ($direction[0] == $direction[1] && $direction[2] == $direction[3] && $direction[0] == $direction[3]){
				// All 4 sides are the same, combine into 1 definition
				$val = $direction[0];
			}
			else if ($direction[0] == $direction[2] && $direction[1] == $direction[3]){
				// top-bottom/left-right are the same, reduce definition
				$val = $direction[0].' '.$direction[1];
			}
			else{
				// No reduction found, return in initial form
				$val = implode(' ', $direction);
			}
		}
		// 2 Direction reduction
		else if ($count == 2){
			if ($direction[0] == $direction[1]){
				// Both directions are the same, combine into single definition
				$val = $direction[0];
			}else{
				// No reduction found, return in initial form
				$val = implode(' ', $direction);
			}
		}
		// No reduction found, return in initial form
		else{
			$val = implode(' ', $direction);
		}

		// Return the value of the property
		return $val;
	}

	/**
	 * Remove's unecessary decimal's
	 *
	 * @param (string) str: Unit found
	 */ 
	function removeDecimal($str){
		// Find all instances of .0 and remove them
		$pattern = "/^(\d+\.0)(\%|\w{2})/i";
		preg_match_all($pattern, $str, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			$search = $matches[0][$i];
			$replace = intval($matches[1][$i]).$matches[2][$i];
			$str = str_ireplace($search, $replace, $str);
		}
		return $str;
	}

	/**
	 * Removes suffix from 0 units, ie 0px; => 0;
	 *
	 * @param (string) str: Unit string
	 */ 
	function removeUnits($str){
		// Find all instants of 0 size and remove suffix
		$pattern = "/^(\d)(\%|\w{2})/i";
		preg_match_all($pattern, $str, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			if (intval($matches[1][$i]) == 0){
				$search = $matches[0][$i];
				$replace = '0';
				$str = str_ireplace($search, $replace, $str);
			}
		}
		return $str;
	}

	/**
	 * Converts long rgb to hex, long hex to short hex, 
	 * short hex to short name(Only works in some browsers)
	 *
	 * @param (string) val: Color to be parsed
	 */ 
	function runColorChanges($val){
		// Transfer rgb colors to hex codes
		if ($this->options['color-rgb2hex']){
			$pattern = "/rgb\((\d{1,3}\%?(,\d{1,3}\%?,\d{1,3}\%?)?)\)/i";
			preg_match_all($pattern, $val, $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				unset($new, $str);
				$hex = '0123456789ABCDEF';
				$str = explode(",", $matches[1][$i]);
				// Incase rgb was defined with single val
				if (!$str) $str = array($matches[1][$i]);
				foreach($str as $x){
					$x = strpos($x, '%') !== false ? intval((intval($x)/100)*255) : intval($x);
					if ($x > 255) $x = 255;
					if ($x < 0) $x = 0;
					$new .= $hex[($x-$x%16)/16];
					$new .= $hex[$x%16];
				}
				// Repeat hex code to complete 6 digit hex requirement for single definitions
				if (count($str) == 1) $new .= $new.$new;
				// Replace within string
				$val = str_ireplace($matches[0][$i], "#$new", $val);
			}
		}

		// Convert long color names to hex codes
		if ($this->options['color-long2hex']){
			// Static so file isn't included with every loop
			static $long2hex;
			if (!$long2hex)
				include(CSSC_VARS_DIR.'long2hex-colors.php');

			// Colornames are all lowercase
			$low = strtolower($val);
			if (isset($long2hex[$low]))
				$val = $long2hex[$low];
		}

		// Convert 6 digit hex codes to short color names
		if ($this->options['color-hex2shortcolor']){
			// Static so files isn't included with every loop
			static $hex2short;
			if (!$hex2short)
				include(CSSC_VARS_DIR.'hex2short-colors.php');

			// Hex codes are all lowercase
			$low = strtolower($val);
			if (isset($hex2short[$low]))
				$val = $hex2short[$low];
		}

		// Convert large hex codes to small codes
		if ($this->options['color-hex2shorthex']){
			$pattern = "/#([0-9a-f]{6})/i";
			preg_match_all($pattern, $val, $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				// Use PHP's string array
				$hex = $matches[1][$i];
				if ($hex[0] == $hex[1] && $hex[2] == $hex[3] && $hex[4] == $hex[5]){
					$search = $matches[0][$i];
					$replace = '#'.$hex[0].$hex[2].$hex[4];
					$val = str_ireplace($search, $replace, $val);
				}
			}
		}

		// Return transformed value
		return $val;
	}

	/**
	 * Converts selectors like BODY => body, DIV => div
	 *
	 * @params none
	 */ 
	function lowercaseSelectors(){
		foreach ($this->selectors as &$selector){
			$comma_arr = explode(',', $selector);
			foreach ($comma_arr as &$comma_val){
				$spaces_arr = explode(' ', $comma_val);
				foreach ($spaces_arr as &$space_val){
					if (strpos($space_val, '>') !== false){
						$b_arr = explode('>', $space_val);
						foreach ($b_arr as &$b_val){
							if (strpos($b_val, '+') !== false){
								$c_arr = explode('+', $b_val);
								foreach ($c_arr as &$c_val){
									$c_val = $this->lowerSelectorExtras($c_val);
								}
								$b_val = trim(implode('+', $c_arr));
							}else{
								$b_val = $this->lowerSelectorExtras($b_val);
							}
						}
						$space_val = trim(implode('>', $b_arr));
					}
					else if (strpos($space_val, '+') !== false){
						$b_arr = explode('+', $space_val);
						foreach ($b_arr as &$b_val){
							$b_val = $this->lowerSelectorExtras($b_val);
						}
						$space_val = trim(implode('+', $b_arr));
					}
					else{
						$space_val = $this->lowerSelectorExtras($space_val);
					}
				}
				$comma_val = trim(implode(' ', $spaces_arr));
			}
			// Don't add commas if none were found
			$selector = ($comma_arr[1] != '') ? trim(implode(',', $comma_arr)) : trim($comma_arr[0]);
		}
	}

	/**
	 * Helper method for above lowercaseSelectors() function,
	 * Does the actual strtolower function on the parsed selector
	 *
	 * @param (string) val: CSS Selector
	 */ 
	function lowerSelectorExtras($val){
		// Check for pseudo
		if (strpos($val, ':') !== false){
			list ($sel, $pseudo) = explode(':', $val);
			// Check for pure html tag
			if (preg_match("/^[a-z0-9]+$/i", $sel)){
				$sel = strtolower($sel);
			}
			// Check for class attachment
			else if (preg_match("/^[a-z0-9]+\.\S+$/i", $sel)){
				list ($tag, $class) = explode('.', $val, 2);
				$sel = strtolower($tag).".$class";
			}
			// Check for id attachment
			else if (preg_match("/^[a-z0-9]+#\S+$/i", $sel)){
				list ($tag, $class) = explode('#', $val, 2);
				$sel = strtolower($tag)."#$class";
			}
			return "$sel:".strtolower($pseudo);
		}
		// Check for class attachment
		else if (preg_match("/^[a-z0-9]+\.\S+$/i", $val)){
			list ($sel, $class) = explode('.', $val, 2);
			return strtolower($sel).".$class";
		}
		// Check for id attachment
		else if (preg_match("/^[a-z0-9]+#\S+$/i", $val)){
			list ($sel, $class) = explode('#', $val, 2);
			return strtolower($sel)."#$class";
		}
		// Check for pure html tag
		else if (preg_match("/^[a-z0-9]+$/i", $val)){
			return strtolower($val);
		}
		else{
			return $val;
		}
	}

	/**
	 * Combines multiply defined selectors by merging the definitions,
	 * latter definitions overide definitions at top of file
	 *
	 * @params none
	 */ 
	function combineMultiplyDefinedSelectors(){
		$max = array_pop(array_keys($this->selectors))+1;
		for ($i=0; $i<$max; $i++){
			if (!$this->selectors[$i]) continue;
			for ($k=$i+1; $k<$max; $k++){
				if (!$this->selectors[$k]) continue;
				if ($this->selectors[$i] == $this->selectors[$k]){
					$this->details[$i] .= $this->details[$k];
					unset($this->selectors[$k], $this->details[$k]);
				}
			}
		}
	}

	/**
	 * Combines multiply defined details by merging the selectors
	 * in comma seperated format
	 *
	 * @params none
	 */ 
	function combineMuliplyDefinedDetails(){
		$max = array_pop(array_keys($this->selectors))+1;
		for ($i=0; $i<$max; $i++){
			if (!$this->selectors[$i]) continue;
			$arr = explode(';', $this->details[$i]);
			for ($k=$i+1; $k<$max; $k++){
				if (!$this->selectors[$k]) continue;
				$match = explode(';', $this->details[$k]);
				$x = array_diff($arr, $match);
				$y = array_diff($match, $arr);
				if (count($x) < 1 && count($y) < 1){
					$this->selectors[$i] .= ','.$this->selectors[$k];
					unset($this->details[$k], $this->selectors[$k]);
				}
			}
		}
	}

	/**
	 * Combines color/style/width of border/outline properties
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function combineCSWproperties($val){
		$storage = array();
		$pattern = "/(border|outline)-(color|style|width):(.*?);/is";
		preg_match_all($pattern, $val, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			$storage[strtolower($matches[1][$i])][strtolower($matches[2][$i])] = $matches[3][$i];
		}

		// Go through each tag for possible combination
		foreach($storage as $tag => $arr){
			// Make sure all 3 are defined and they aren't directionals
			if (count($arr) == 3 && !$this->checkUncombinables($arr)){
				// String to replace each instance with
				$replace = "$tag:".$arr['width'].' '.$arr['style'].' '.$arr['color'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ($arr as $x=>$y)
					$val = str_ireplace("$tag-$x:$y", $replace, $val);
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines Aural properties (currently being depreciated in W3C Standards)
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function combineAuralCuePause($val){
		$storage = array();
		$pattern = "/(cue|pause)-(before|after):(.*?);/i";
		preg_match_all($pattern, $val, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			$storage[strtolower($matches[1][$i])][strtolower($matches[2][$i])] = $matches[3][$i];
		}

		// Go through each tag for possible combination
		foreach($storage as $tag => $arr){
			if (count($arr) == 2 && !$this->checkUncombinables($arr)){
				// String to replace each instance with
				$replace = "$tag:".$arr['before'].' '.$arr['after'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ($arr as $x=>$y)
					$val = str_ireplace("$tag-$x:$y", $replace, $val);
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple directional properties of 
	 * margin/padding into single definition.
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function combineMPproperties($val){
		$storage = array();
		$pattern = "/(margin|padding)-(top|right|bottom|left):(.*?);/i";
		preg_match_all($pattern, $val, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			if (!isset($storage[$matches[1][$i]])) $storage[$matches[1][$i]] = array($matches[2][$i] => $matches[3][$i]);
			// Override double written properties
			$storage[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
		}

		// Go through each tag for possible combination
		foreach($storage as $tag => $arr){
			// Drop capitols
			$tag = strtolower($tag);
			// Only combine if all 4 definitions are found
			if (count($arr) == 4 && !$this->checkUncombinables($arr)){
				// If all definitions are the same, combine into single definition
				if ($arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['left']){
					// String to replace each instance with
					$replace = "$tag:".$arr['top'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ($arr as $a=>$b)
						$val = str_ireplace("$tag-$a:$b", $replace, $val);
				}
				// If opposites are the same, combine into single definition
				else if ($arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right']){
					// String to replace each instance with
					$replace = "$tag:".$arr['top'].' '.$arr['left'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ($arr as $a=>$b)
						$val = str_ireplace("$tag-$a:$b", $replace, $val);
				}
				else{
					// String to replace each instance with
					$replace = "$tag:".$arr['top'].' '.$arr['right'].' '.$arr['bottom'].' '.$arr['left'];
					// Replace every instance, as multiple declarations removal will correct it
					foreach ($arr as $a=>$b)
						$val = str_ireplace("$tag-$a:$b", $replace, $val);
				}
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple border properties into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */
	function combineBorderDefinitions($val){
		$storage = array();
		$pattern = "/(border)-(top|right|bottom|left):(.*?);/i";
		preg_match_all($pattern, $val, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			if (!isset($storage[$matches[1][$i]])) $storage[$matches[1][$i]] = array($matches[2][$i] => $matches[3][$i]);
			// Override double written properties
			$storage[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
		}

		foreach ($storage as $tag => $arr){
			if (count($arr) == 4 && $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['right']){
				// String to replace each instance with
				$replace = "$tag:".$arr['top'];
				// Replace every instance, as multiple declarations removal will correct it
				foreach ($arr as $a=>$b)
					$val = str_ireplace("$tag-$a:$b", $replace, $val);
			}
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple font-definitions into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function combineFontDefinitions($val){
		$storage = array();
		$pattern = "/(font|line)-(style|variant|weight|size|height|family):(.*?);/i";
		preg_match_all($pattern, $val, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			// Store each property in it's full state
			$storage[$matches[1][$i].'-'.$matches[2][$i]] = $matches[3][$i];
		}

		// Combine font-size & line-height if possible
		if (isset($storage['font-size']) && isset($storage['line-height'])){
			$storage['size/height'] = $storage['font-size'].'/'.$storage['line-height'];
			unset($storage['font-size'], $storage['line-height']);
		}

		// Run font checks and get replacement str
		$replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-variant', 'font-weight', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-variant', 'font-weight', 'font-size', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-variant', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-variant', 'font-size', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-weight', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-weight', 'font-size', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-variant', 'font-weight', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-variant', 'font-weight', 'font-size', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-weight', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-weight', 'font-size', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-variant', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-variant', 'font-size', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-style', 'font-size', 'font-family'));

		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('size/height', 'font-family'));
		if (!$replace) $replace = $this->searchDefinitions('font', $storage, array('font-size', 'font-family'));

		// If replacement string found, run it on all options
		if ($replace){
			for ($i=0; $i<count($matches[1]); $i++)
				if (! isset($storage['line-height']) || 
					(isset($storage['line-height']) && stripos($matches[0][$i], 'line-height') !== 0))
						$val = str_ireplace($matches[0][$i], $replace, $val);
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple background props into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function combineBackgroundDefinitions($val){
		$storage = array();
		$pattern = "/background-(color|image|repeat|attachment|position):(.*?);/i";
		preg_match_all($pattern, $val, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			// Store each property in it's full state
			$storage[$matches[1][$i]] = $matches[2][$i];
		}

		// Run background checks and get replacement str
		// With color
		$replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'repeat', 'attachment', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'attachment', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'repeat', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'repeat', 'attachment'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'repeat'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'attachment'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color', 'image'));
		// Without Color
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image', 'attachment', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image', 'repeat', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image', 'repeat', 'attachment'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image', 'repeat'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image', 'attachment'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('image'));
		// Just Color
		if (!$replace) $replace = $this->searchDefinitions('background', $storage, array('color'));

		// If replacement string found, run it on all options
		if ($replace){
			for ($i=0; $i<count($matches[1]); $i++)
				$val = str_ireplace($matches[0][$i], $replace, $val);
		}

		// Return converted val
		return $val;
	}

	/**
	 * Combines multiple list style props into single definition
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function combineListProperties($val){
		$storage = array();
		$pattern = "/list-style-(type|position|image):(.*?);/i";
		preg_match_all($pattern, $val, $matches);
		// Store secondhand prop
		for ($i=0; $i<count($matches[1]); $i++)
			$storage[$matches[1][$i]] = $matches[2][$i];

		// Run search patterns for replacement string
		$replace = $this->searchDefinitions('list-style', $storage, array('type', 'position', 'image'));
		if (!$replace) $replace = $this->searchDefinitions('list-style', $storage, array('type', 'position'));
		if (!$replace) $replace = $this->searchDefinitions('list-style', $storage, array('type', 'image'));
		if (!$replace) $replace = $this->searchDefinitions('list-style', $storage, array('position', 'image'));
		if (!$replace) $replace = $this->searchDefinitions('list-style', $storage, array('type'));
		if (!$replace) $replace = $this->searchDefinitions('list-style', $storage, array('position'));
		if (!$replace) $replace = $this->searchDefinitions('list-style', $storage, array('image'));

		// If replacement string found, run it on all options
		if ($replace){
			for ($i=0; $i<count($matches[1]); $i++)
				$val = str_ireplace($matches[0][$i], $replace, $val);
		}

		// Return converted val
		return $val;
	}

	/**
	 * Helper function to ensure flagged words don't get
	 * overridden
	 *
	 * @param (array/string) obj: Array/String of definitions to be checked
	 */ 
	function checkUncombinables($obj){
		if (is_array($obj)){
			foreach ($obj as $item)
				if (preg_match("/inherit|\!important|\s/i", $item))
					return true;
			return false;
		}else{
			return preg_match("/inherit|\!important|\s/i", $obj);
		}
	}

	/**
	 * Helper function to ensure all values of search array
	 * exist within the storage array
	 *
	 * @param (string) prop: CSS Property
	 * @param (array) storage: Array of definitions found
	 * @param (array) search: Array of definitions requred
	 */ 
	function searchDefinitions($prop, $storage, $search){
		// Return storage & search don't match
		if (count($storage) != count($search))
			return false;
		$str = "$prop:";
		foreach ($search as $value){
			if (!isset($storage[$value]) || $this->checkUncombinables($storage[$value]))
				return false;
			$str .= $storage[$value].' ';
		}
		return trim($str).';';
	}

	/**
	 * Removes multiple definitions that were created during compression
	 *
	 * @param (string) val: CSS Selector Properties
	 */ 
	function removeMultipleDefinitions($val){
		$storage = array();
		$arr = explode(';', $val);
		foreach($arr as $x){
			if ($x){
				list($a, $b) = explode(':', $x, 2);
				$storage[$a] = $b;
			}
		}
		if ($storage){
			unset($val);
			foreach($storage as $x=>$y)
				$val .= "$x:$y;";
		}

		// Return converted val
		return $val;
	}

	/**
	 * Runs final counts on selectors and props
	 *
	 * @params none
	 */ 
	function runFinalStatistics(){
		// Selectors and props
		$this->stats['after']['selectors'] = count($this->selectors);
		foreach ($this->details as $item){
			$props = explode(';', $item);
			// Make sure count is true
			foreach ($props as $k=>$v)
				if (!isset($v) || $v == '') 
					unset($props[$k]);
			$this->stats['after']['props'] += count($props);
		}
	}

	/**
	 * Reformats compressed CSS into specified format
	 *
	 * @param (string) import: CSS Import property removed at beginning
	 */ 
	function readability($import){
		if ($this->options['readability'] == 3){
			$css = str_replace(';', ";\n", $import);
			if ($import) $css .= "\n";
			foreach ($this->selectors as $k=>$v){
				if (! $this->details[$k] || trim($this->details[$k]) == '')
					continue;
				$v = str_replace('>', ' > ', $v);
				$v = str_replace('+', ' + ', $v);
				$v = str_replace(',', ', ', $v);
				$css .= "$v {\n";
				$arr = explode(";", $this->details[$k]);
				foreach ($arr as $item){
					if (!$item) continue;
					list ($prop, $val) = explode(':', $item, 2);
					$css .= "\t$prop: $val;\n";
				}
				$css .= "}\n\n";
			}
		}
		else if ($this->options['readability'] == 2){
			$css = str_replace(';', ";\n", $import);
			foreach ($this->selectors as $k=>$v)
				if ($this->details[$k] && $this->details[$k] != '')
					$css .= "$v {\n\t".$this->details[$k]."\n}\n";
		}
		else if ($this->options['readability'] == 1){
			$css = str_replace(';', ";\n", $import);
			foreach ($this->selectors as $k=>$v)
				if ($this->details[$k] && $this->details[$k] != '')
					$css .= "$v{".$this->details[$k]."}\n";
		}
		else{
			$css = $import;
			foreach ($this->selectors as $k=>$v)
				if ($this->details[$k] && $this->details[$k] != '')
					$css .= trim("$v{".$this->details[$k]."}");
		}

		// Return formatted script
		return trim($css);
	}

	/**
	 * Display's a table containing the result statistics of the compression
	 *
	 * @params none
	 */ 
	function displayStats(){
		// Set before/after arrays
		$before = $this->stats['before'];
		$after = $this->stats['after'];

		// Calc final size
		$size = $before['size']-$after['size'];

		// Display the table
		echo "<table cellspacing='1' cellpadding='2' style='width:400px;margin-bottom:20px;'>
			<tr bgcolor='#d1d1d1' align='center'>
				<th bgcolor='#f1f1f1' style='color:#8B0000;'>Results &raquo;</th>
				<th width='100'>Before</th>
				<th width='100'>After</th>
				<th width='100'>Compresssion</th>
			</tr>
			<tr bgcolor='#f1f1f1' align='center'>
				<th bgcolor='#d1d1d1'>Time</th>
				<td>-</td>
				<td>-</td>
				<td>".round($after['time']-$before['time'],2)." seconds</td>
			</tr>
			<tr bgcolor='#f1f1f1' align='center'>
				<th bgcolor='#d1d1d1'>Selectors</th>
				<td>".$before['selectors']."</td>
				<td>".$after['selectors']."</td>
				<td>".($before['selectors']-$after['selectors'])."</td>
			</tr>
			<tr bgcolor='#f1f1f1' align='center'>
				<th bgcolor='#d1d1d1'>Properties</th>
				<td>".$before['props']."</td>
				<td>".$after['props']."</td>
				<td>".($before['props']-$after['props'])."</td>
			</tr>
			<tr bgcolor='#f1f1f1' align='center'>
				<th bgcolor='#d1d1d1'>Size</th>
				<td>".$this->displaySizes($before['size'])."</td>
				<td>".$this->displaySizes($after['size'])."</td>
				<td>".$this->displaySizes($before['size']-$after['size'])."</td>
			</tr>
			</table>";
	}

	/**
	 * Byte format return of file sizes
	 *
	 * @param (int) size: File size in Bytes
	 */ 
	function displaySizes($size){
		$ext = array('B', 'K', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		for($c=0; $size>1024; $c++) $size /= 1024;
		return round($size,2).$ext[$c];
	}
};

/* Create the object */
$CSSC = new CSSCompression;
?>
