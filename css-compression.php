<?
/**
 * CSS Compressor
 * r:5 - May 5, 2009
 * Corey Hart @ http://www.codenothing.com
 */ 

// Include miscellaneous variables
include("vars/collect.php");


Class CSSCompression
{
	var $selectors = array();
	var $details = array();
	var $options = array();
	var $stats = array();

	function setOptions(){
		// Converts long color names to short hex names (aliceblue -> #f0f8ff)
		$this->options['color-long2hex'] = $_POST['color-long2hex'];

		// Converts rgb colors to hex (rgb(159,80,98) -> #9F5062)
		$this->options['color-rgb2hex'] = $_POST['color-rgb2hex'];

		// Converts long hex codes to short color names (#f5f5dc -> beige)
		$this->options['color-hex2shortcolor'] = $_POST['color-hex2shortcolor'];

		// Converts long hex codes to short hex codes (#44ff11 -> #4f1)
		$this->options['color-hex2shorthex'] = $_POST['color-hex2shorthex'];

		// Converts font-weight names to numbers (bold -> 700)
		$this->options['fontweight2num'] = $_POST['fontweight2num'];

		// Removes zero decimals and 0 units (15.0px -> 15px || 0px -> 0)
		$this->options['format-units'] = $_POST['format-units'];

		// Lowercases html tags from list (BODY -> body)
		$this->options['lowercase-selectors'] = $_POST['lowercase-selectors'];

		// Compresses single defined multi-directional properties (margin: 15px 25px 15px 25px -> margin:15px 25px)
		$this->options['directional-compress'] = $_POST['directional-compress'];

		// Combines multiply defined selectors (p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;})
		$this->options['multiple-selectors'] = $_POST['multiple-selectors'];

		// Combines selectors with same details (p{color:blue;} a{color:blue;} -> p,a{color:blue;})
		$this->options['multiple-details'] = $_POST['multiple-details'];

		// Combines color/style/width properties (border-style:dashed;border-color:black;border-width:4px; -> border:4px dashed black)
		$this->options['csw-combine'] = $_POST['csw-combine'];

		// Combines margin/padding directionals (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -> margin:10px 5px 4px 1px;)
		$this->options['mp-combine'] = $_POST['mp-combine'];

		// Combines border directionals (border-top|right|bottom|left:1px solid black -> border:1px solid black)
		$this->options['border-combine'] = $_POST['border-combine'];

		// Combines font properties (font-size:12pt; font-family: arial; -> font:12pt arial)
		$this->options['font-combine'] = $_POST['font-combine'];

		// Combines background properties (background-color: black; background-image: url(bgimg.jpeg); -> background:black url(bgimg.jpeg))
		$this->options['background-combine'] = $_POST['background-combine'];

		// Combines list-style properties (list-style-type: round; list-style-position: outside -> list-style:round outside)
		$this->options['list-combine'] = $_POST['list-combine'];

		// Removes multiply defined properties
		// STRONGLY SUGGESTED TO KEEP THIS TRUE
		$this->options['rm-multi-define'] = $_POST['rm-multi-define'];
	}

	function compress($css){
		// Start the timer
		$time = explode(' ', microtime());
		$this->stats['before']['time'] = $time[1] + $time[0];

		// Initial count for stats
		$this->stats['before']['size'] = strlen($css);

		// Use defined options
		$this->setOptions();

		// Send body through initial trimings
		$css = $this->initialTrim($css);

		// Seperate the element from the elements details
		$css = explode("\n", $css);
		foreach ($css as $details){
			// Determine whether your looking at the details or element
			if (ereg("^{", $details)){
				unset($storage);
				$details = substr($details, 1, strlen($details)-2);
				$details = explode(";", $details);
				foreach($details as $line){
					if (eregi("^(url|@import)", $line)){
						$storage .= $line.";";
						continue;
					}
					list ($property, $value) = explode(":", $line);
					// Fail safe, remove unknown tag/elements
					if (!isset($property) || !isset($value)) continue;

					// Run the tag/element through each compression
					list ($property, $value) = $this->runSpecialCompressions($property, $value);

					// Add counter to before stats
					$this->stats['before']['props']++;

					// Store the compressed element
					$storage .= "$property:$value;";
				}
				$this->details[count($this->details)] = $storage;
			}
			else if (eregi("^@import", trim($details))){
				list ($import, $details) = explode(";", $details);
				$IMPORT_STR .= trim($import).";";
				$this->selectors[count($this->selectors)] = trim($details);
				// Add counter to before stats
				$this->stats['before']['selectors']++;
			}
			else if ($details){
				$this->selectors[count($this->selectors)] = trim($details);
				// Add counter to before stats
				$this->stats['before']['selectors']++;
			}

		}
		// Compression Functions
		if ($this->options['lowercase-selectors']) 	$this->lowercaseSelectors();
		if ($this->options['multiple-selectors']) 	$this->combineMultiplyDefinedSelectors();
		if ($this->options['multiple-details']) 	$this->combineMuliplyDefinedDetails();
		if ($this->options['csw-combine'])		$this->combineCSWproperties();
		if ($this->options['mp-combine']) 		$this->combineMPproperties();
		if ($this->options['border-combine']) 		$this->combineBorderDefinitions();
		if ($this->options['font-combine']) 		$this->combineFontDefinitions();
		if ($this->options['background-combine']) 	$this->combineBackgroundDefinitions();
		if ($this->options['list-combine']) 		$this->combineListProperties();
		if ($this->options['rm-multi-define']) 		$this->removeMultipleDefinitions();

		// Run final stats
		$this->runFinalStatistics();

		// Format css to users preference
		$css =  $this->readability($IMPORT_STR, intval($_POST['readability']));

		// Final count for stats
		$this->stats['after']['size'] = strlen($css);

		// Compression time
		$time = explode(' ', microtime());
		$this->stats['after']['time'] = $time[1] + $time[0];

		// Return compressed css
		return $css;
	}

	function initialTrim($css){
		// Regex
		$search = array(
			1 => "(\r|\n|\t)is", // Move extraneous spaces
			2 => "(\s{2,})is", // Remove multiple spaces
			3 => "((\/\*|\<\!\-\-)(.*?)(\*\/|\-\-\>))is", // Remove all comments
			4 => "(\s{0,}([,{};:>])\s{0,})is", // Remove un-needed spaces around special characters
			5 => "(url\(['\"](.*?)['\"]\))is", // Remove quotes from urls
			6 => "(;{2,})is", // Remove unecessary semi-colons
			// Leave section open for additional entries

			// Break apart elements for setup of further compression
			20 => "(\{)is",
			21 => "(\})is",
		);

		// Replacements
		$replace = array(
			1 => " ",
			2 => " ",
			3 => " ",
			4 => "$1",
			5 => "url($1)",
			6 => ";",
			// Leave section open for additional entries

			// Add new line for setup of further compression
			20 => "\n{",
			21 => "}\n",
		);

		// Run replacements
		return preg_replace($search, $replace, $css);
	}

	function runSpecialCompressions($prop, $val){
		global $fontweight2num;
		// Properties should always be lowercase
		$prop = strtolower($prop);

		// Remove uneeded side definitions if possible
		if ($this->options['directional-compress'] && eregi("^(margin|padding|border-width)", $prop)){
			$val = $this->sidesDirectional($val);
		}

		// Font-weight converter
		if ($this->options['fontweight2num'] && eregi("^font-weight$", $prop)){
			if (isset($fontweight2num[strtolower($val)]))
				$val = $fontweight2num[strtolower($val)];
		}

		// Remove uneeded decimals/units
		if ($this->options['format-units']){
			$val = $this->removeDecimal($val);
			$val = $this->removeUnits($val);
		}

		// Seperate out multi-definitions if possible
		$arr = explode(" ", $val);
		foreach ($arr as $k=>$v) $arr[$k] = $this->runColorChanges($v);
		$val = trim(implode(" ", $arr));

		// Return for list retrival
		return array($prop, $val);
	}

	function sidesDirectional($val){
		// Side definitions already reduced down to a single definition
		if (!ereg(" ", $val)){
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
			foreach ($direction as $k=>$v) $direction[$k] = $this->removeDecimal($v);
			foreach ($direction as $k=>$v) $direction[$k] = $this->removeUnits($v);
		}

		// 4 Direction reduction
		if (count($direction) == 4){
			if ($direction[0] == $direction[1] && $direction[2] == $direction[3] && $direction[0] == $direction[3]){
				// All 4 sides are the same, combine into 1 definition
				$val = $direction[0];
			}
			else if ($direction[0] == $direction[2] && $direction[1] == $direction[3]){
				// top-bottom/left-right are the same, reduce definition
				$val = $direction[0]." ".$direction[1];
			}
			else{
				// No reduction found, return in initial form
				$val = implode(" ", $direction);
			}
		}
		// 2 Direction reduction
		else if (count($direction) == 2){
			if ($direction[0] == $direction[1]){
				// Both directions are the same, combine into single definition
				$val = $direction[0];
			}else{
				// No reduction found, return in initial form
				$val = implode(" ", $direction);
			}
		}
		// No reduction found, return in initial form
		else{
			$val = implode(" ", $direction);
		}

		// Return the value of the property
		return $val;
	}

	function removeDecimal($str){
		// Find all instances of .0 and remove them
		$pattern = "/^(\d+\.0)(\w{0,2})/i";
		preg_match_all($pattern, $str, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			$search = "(".$matches[0][$i].")is";
			$replace = intval($matches[1][$i]).$matches[2][$i];
			$str = preg_replace($search, $replace, $str);
		}
		return $str;
	}

	function removeUnits($str){
		// Find all instants of 0 size and remove suffix
		$pattern = "/^(\d)(\w{0,2})/i";
		preg_match_all($pattern, $str, $matches);
		for ($i=0; $i<count($matches[1]); $i++){
			if (intval($matches[1][$i]) == 0){
				$search = "(".$matches[0][$i].")is";
				$replace = "0";
				$str = preg_replace($search, $replace, $str);
			}
		}
		return $str;
	}

	function runColorChanges($val){
		global $long2hex, $hex2short;
		// Transfer rgb colors to hex codes
		if ($this->options['color-rgb2hex']){
			$pattern = "/rgb\((\d{1,3}(,\s?\d{1,3},\s?\d{1,3})?)\)/i";
			preg_match_all($pattern, $val, $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				unset($new, $str);
				$hex = "0123456789ABCDEF";
				$str = explode(",", $matches[1][$i]);
				// Incase rgb was defined with single val
				if (!$str) $str = array($matches[1][$i]);
				foreach($str as $x){
					$x = intval($x);
					if ($x > 255) $x = 255;
					if ($x < 0) $x = 0;
					$new .= $hex[($x-$x%16)/16];
					$new .= $hex[$x%16];
				}
				// Repeat hex code to complete 6 digit hex requirement for single definitions
				if (count($str) == 1) $new .= $new.$new;
				// Escape out brackets
				$matches[0][$i] = str_replace("(", "\(", $matches[0][$i]);
				$matches[0][$i] = str_replace(")", "\)", $matches[0][$i]);
				// Replace within string
				$val = preg_replace("(".$matches[0][$i].")is", "#$new", $val);
			}
		}

		// Push long2hex vals for first conversion
		if ($this->options['color-long2hex']){
			foreach ($long2hex as $x=>$y){
				if (strtolower($val) == $x) $val = $y;
			}
		}

		// Now add hex2short vals
		if ($this->options['color-hex2shortcolor']){
			foreach($hex2short as $x=>$y){
				if (strtolower($val) == $x) $val = $y;
			}
		}

		// Convert large hex codes to small codes
		if ($this->options['color-hex2shorthex']){
			$pattern = "/#([0-9a-fA-F]{6})/i";
			preg_match_all($pattern, $val, $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				$hex = str_split($matches[1][$i], 1);
				if ($hex[0] == $hex[1] && $hex[2] == $hex[3] && $hex[4] == $hex[5]){
					$search = "(".$matches[0][$i].")is";
					$replace = "#".$hex[0].$hex[2].$hex[4];
					$val = preg_replace($search, $replace, $val);
				}
			}
		}

		// Return transformed value
		return $val;
	}

	function lowercaseSelectors(){
		global $standard_selectors;
		$search = array();
		$replace = array();
		foreach ($standard_selectors as $k=>$v){
			// Ensure its a tag thats being selected
			array_push($search, "(([^a-zA-Z0-9#.])$v)is");
			array_push($replace, "$1$v");
			// For when the tag is the first defined
			array_push($search, "(^$v)is");
			array_push($replace, "$v");
		}

		foreach ($this->selectors as $k=>$v){
			$this->selectors[$k] = preg_replace($search, $replace, $v);
		}
	}

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

	function combineMuliplyDefinedDetails(){
		$max = array_pop(array_keys($this->selectors))+1;
		for ($i=0; $i<$max; $i++){
			if (!$this->selectors[$i]) continue;
			$arr = explode(";", $this->details[$i]);
			for ($k=$i+1; $k<$max; $k++){
				if (!$this->selectors[$k]) continue;
				$match = explode(";", $this->details[$k]);
				$x = array_diff($arr, $match);
				$y = array_diff($match, $arr);
				if (count($x) < 1 && count($y) < 1){
					$this->selectors[$i] .= ",".$this->selectors[$k];
					unset($this->details[$k], $this->selectors[$k]);
				}
			}
		}
	}

	function combineCSWproperties(){
		foreach ($this->details as $k=>$v){
			$storage = array();
			$pattern = "/(border|outline)-(color|style|width):(.*?);/i";
			preg_match_all($pattern, $this->details[$k], $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				if (!isset($storage[$matches[1][$i]])) $storage[$matches[1][$i]] = array($matches[2][$i] => $matches[3][$i]);
				// Override double written properties
				$storage[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
			}

			// Go through each tag for possible combination
			foreach($storage as $tag => $arr){
				if (count($arr) == 3){
					// String to replace each instance with
					$replace = "$tag:".$arr['width']." ".$arr['style']." ".$arr['color'];
					foreach ($arr as $x=>$y){
						// Replace every instance, as multiple declarations removal will correct it
						$search = "($tag-$x:$y)is";
						$this->details[$k] = preg_replace($search, $replace, $this->details[$k]);
					}
				}
			}
		}
	}

	function combineMPproperties(){
		foreach ($this->details as $k=>$v){
			$storage = array();
			$pattern = "/(margin|padding)-(top|right|bottom|left):(.*?);/i";
			preg_match_all($pattern, $this->details[$k], $matches);
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
				if (count($arr) == 4){
					// If all definitions are the same, combine into single definition
					if ($arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['left']){
						// String to replace each instance with
						$replace = "$tag:".$arr['top'];
						foreach ($arr as $a=>$b){
							// Replace every instance, as multiple declarations removal will correct it
							$search = "($tag-$a:$b)is";
							$this->details[$k] = preg_replace($search, $replace, $this->details[$k]);
						}
					}
					// If opposites are the same, combine into single definition
					else if ($arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right']){
						// String to replace each instance with
						$replace = "$tag:".$arr['top']." ".$arr['left'];
						foreach ($arr as $a=>$b){
							// Replace every instance, as multiple declarations removal will correct it
							$search = "($tag-$a:$b)is";
							$this->details[$k] = preg_replace($search, $replace, $this->details[$k]);
						}
					}
					else{
						// String to replace each instance with
						$replace = "$tag:".$arr['top']." ".$arr['right']." ".$arr['bottom']." ".$arr['left'];
						foreach ($arr as $a=>$b){
							// Replace every instance, as multiple declarations removal will correct it
							$search = "($tag-$a:$b)is";
							$this->details[$k] = preg_replace($search, $replace, $this->details[$k]);
						}
					}
				}
			}
		}
	}

	function combineBorderDefinitions(){
		foreach ($this->details as $k=>$v){
			$storage = array();
			$pattern = "/(border)-(top|right|bottom|left):(.*?);/i";
			preg_match_all($pattern, $this->details[$k], $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				if (!isset($storage[$matches[1][$i]])) $storage[$matches[1][$i]] = array($matches[2][$i] => $matches[3][$i]);
				// Override double written properties
				$storage[$matches[1][$i]][$matches[2][$i]] = $matches[3][$i];
			}

			foreach ($storage as $tag => $arr){
				if (count($arr) == 4 && $arr['top'] == $arr['bottom'] && $arr['left'] == $arr['right'] && $arr['top'] == $arr['right']){
					// String to replace each instance with
					$replace = "$tag:".$arr['top'];
					foreach ($arr as $a=>$b){
						// Replace every instance, as multiple declarations removal will correct it
						$search = "($tag-$a:$b)is";
						$this->details[$k] = preg_replace($search, $replace, $this->details[$k]);
					}
				}
			}
		}
	}

	function combineFontDefinitions(){
		foreach ($this->details as $k=>$v){
			unset($replace, $storage);
			$pattern = "/(font|line)-(style|variant|weight|size|height|family):(.*?);/i";
			preg_match_all($pattern, $this->details[$k], $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				// Store each property in it's full state
				$storage[$matches[1][$i]."-".$matches[2][$i]] = $matches[3][$i];
			}

			// Combine font-size & line-height if possible
			if (isset($storage['font-size']) && isset($storage['line-height'])){
				$storage['size/height'] = $storage['font-size']."/".$storage['line-height'];
				unset($storage['font-size'], $storage['line-height']);
			}

			// Run font checks and get replacement str
			$replace = $this->searchDefinitions("font", $storage, array("font-style", "font-variant", "font-weight", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "font-variant", "font-weight", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "font-variant", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "font-variant", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "font-weight", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "font-weight", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-variant", "font-weight", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-variant", "font-weight", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-weight", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-weight", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-variant", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-variant", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-style", "font-size", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("size/height", "font-family"));
			if (!$replace) $replace = $this->searchDefinitions("font", $storage, array("font-size", "font-family"));

			// If replacement string found, run it on all options
			if ($replace){
				for ($i=0; $i<count($matches[1]); $i++){
					if (!isset($storage['line-height']) || (isset($storage['line-height']) && !eregi("^line-height", $matches[0][$i]))){
						$v = preg_replace("(".$matches[0][$i].")is", $replace, $v);
					}
				}
			}

			// Replace details
			$this->details[$k] = $v;
		}
	}

	function combineBackgroundDefinitions(){
		foreach ($this->details as $k=>$v){
			unset($replace, $storage);
			$pattern = "/background-(color|image|repeat|attachment|position):(.*?);/i";
			preg_match_all($pattern, $this->details[$k], $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				// Store each property in it's full state
				$storage[$matches[1][$i]] = $matches[2][$i];
			}

			// Run background checks and get replacement str
			// With color
			$replace = $this->searchDefinitions("background", $storage, array("color", "image", "repeat", "attachment", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image", "attachment", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image", "repeat", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image", "repeat", "attachment"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image", "repeat"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image", "attachment"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color", "image"));
			// Without Color
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image", "attachment", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image", "repeat", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image", "repeat", "attachment"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image", "repeat"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image", "attachment"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image", "position"));
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("image"));
			// Just Color
			if (!$replace) $replace = $this->searchDefinitions("background", $storage, array("color"));

			// If replacement string found, run it on all options
			if ($replace){
				for ($i=0; $i<count($matches[1]); $i++){
					// Escape out url braces
					$matches[0][$i] = str_replace("(", "\(", $matches[0][$i]);
					$matches[0][$i] = str_replace(")", "\)", $matches[0][$i]);
					$v = preg_replace("(".$matches[0][$i].")is", $replace, $v);
				}
			}

			// Replace details
			$this->details[$k] = $v;
		}
	}

	function combineListProperties(){
		foreach ($this->details as $k=>$v){
			$storage = array();
			$pattern = "/list-style-(type|position|image):(.*?);/i";
			preg_match_all($pattern, $this->details[$k], $matches);
			for ($i=0; $i<count($matches[1]); $i++){
				// Store secondhand prop
				$storage[$matches[1][$i]] = $matches[2][$i];
			}

			// Run search patterns for replacement string
			$replace = $this->searchDefinitions("list-style", $storage, array("type", "position", "image"));
			if (!$replace) $replace = $this->searchDefinitions("list-style", $storage, array("type", "position"));
			if (!$replace) $replace = $this->searchDefinitions("list-style", $storage, array("type", "image"));
			if (!$replace) $replace = $this->searchDefinitions("list-style", $storage, array("position", "image"));
			if (!$replace) $replace = $this->searchDefinitions("list-style", $storage, array("type"));
			if (!$replace) $replace = $this->searchDefinitions("list-style", $storage, array("position"));
			if (!$replace) $replace = $this->searchDefinitions("list-style", $storage, array("image"));

			// If replacement string found, run it on all options
			if ($replace){
				for ($i=0; $i<count($matches[1]); $i++){
					// Escape out url braces
					$matches[0][$i] = str_replace("(", "\(", $matches[0][$i]);
					$matches[0][$i] = str_replace(")", "\)", $matches[0][$i]);
					$v = preg_replace("(".$matches[0][$i].")is", $replace, $v);
				}
			}

			// Replace details
			$this->details[$k] = $v;
		}
	}

	function searchDefinitions($prop, $storage, $search){
		$str = "$prop:";
		foreach ($search as $value){
			if (!isset($storage[$value])) return false;
			$str .= $storage[$value]." ";
		}
		return trim($str).";";
	}

	function removeMultipleDefinitions(){
		foreach($this->details as $k=>$v){
			$storage = array();
			$arr = explode(";", $v);
			foreach($arr as $x){
				if ($x){
					list($a, $b) = explode(":", $x);
					$storage[$a] = $b;
				}
			}
			if ($storage){
				unset($this->details[$k]);
				foreach($storage as $x=>$y){
					$this->details[$k] .= "$x:$y;";
				}
			}
		}
	}

	function runFinalStatistics(){
		// Selectors and props
		$this->stats['after']['selectors'] = count($this->selectors);
		foreach ($this->details as $item){
			$props = explode(";", $item);
			// Make sure count is true
			foreach ($props as $k=>$v){
				if (!isset($v) || $v == "") unset($props[$k]);
			}
			$this->stats['after']['props'] += count($props);
		}
	}

	function readability($import, $read){
		if ($read == 3){
			$css = str_replace(";", ";\n", $import);
			foreach ($this->selectors as $k=>$v){
				$v = str_replace(">", " > ", $v);
				$v = str_replace(",", ", ", $v);
				$css .= "$v {\n";
				$arr = explode(";", $this->details[$k]);
				foreach ($arr as $item){
					if (!$item) continue;
					list ($prop, $val) = explode(":", $item);
					$css .= "\t$prop: $val;\n";
				}
				$css .= "}\n\n";
			}
		}
		else if ($read == 2){
			$css = str_replace(";", ";\n", $import);
			foreach ($this->selectors as $k=>$v){
				$css .= "$v {\n\t".$this->details[$k]."\n}\n";
			}
		}
		else if ($read == 1){
			$css = str_replace(";", ";\n", $import);
			foreach ($this->selectors as $k=>$v){
				$css .= "$v{".$this->details[$k]."}\n";
			}
		}
		else{
			$css = $import;
			foreach ($this->selectors as $k=>$v){
				$css .= trim("$v{".$this->details[$k]."}");
			}
		}

		// Return formatted script
		return trim($css);
	}

	function displayStats(){
		// Set before/after arrays
		$before = $this->stats['before'];
		$after = $this->stats['after'];

		// Calc final size
		$size = $before['size']-$after['size'];

		// Display the table
		echo "<table cellspacing='1' cellpadding='2' style='width:400px;margin-bottom:20px;'>
			<tr bgcolor='#d1d1d1' align='center'>
				<th bgcolor='white' style='color:#8B0000;'>Results &raquo;</th>
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
				<td>".($before['size'] > 1024 ? round($before['size']/1024,2)."K" : $before['size']."B")."</td>
				<td>".($after['size'] > 1024 ? round($after['size']/1024,2)."K" : $after['size']."B")."</td>
				<td>".($size > 1024 ? round($size/1024,2)."K" : $size."B")."</td>
			</tr>
			</table>";
	}
};

/* Create the object */
$CSSC = new CSSCompression;
?>
