Options
=======

Here's a few different ways to set options.

	// Set an array of options
	$options = array( 'color-long2hex' => false, 'readability' => CSSCompression::READ_MAX );

	// Pass directly into express compression
	$compressed = CSSCompression::express( $css, $options );

	// Create an instance based on the predefined set of options
	$CSSC = new CSSCompression( $options );

	// Set a batch of options on an instance
	$CSSC->option( $options );

	// Set a single option on an instance
	$CSSC->option( 'readability', CSSCompression::READ_MAX );

	// Or, if you just want to read an option
	$readability = $CSSC->option( 'readability' );

	// Also, you can look at the current options
	$options = $CSSC->option();


color-long2hex
--------------

Converts long color names to short hex names

 - *aliceblue -> #f0f8ff*


color-rgb2hex
-------------

Converts rgb colors to hex

 - *rgb(159,80,98) -> #9F5062, rgb(100%) -> #FFFFFF*


color-hex2shortcolor
--------------------

Converts long hex codes to short color names, Only works on latest browsers, careful when using.

 - *#f5f5dc -> beige*


color-hex2shorthex
------------------

Converts long hex codes to short hex codes

 - *#44ff11 -> #4f1*


color-hex2safe
--------------------

Converts long hex codes to safe CSS Level 1 color names.

 - *#f00 -> red*


fontweight2num
--------------

Converts font-weight names to numbers

 - *bold -> 700*


format-units
------------

Removes zero decimals and 0 units

 - *15.0px -> 15px || 0px -> 0*


lowercase-selectors
-------------------

Lowercases html tags from list

 - *BODY -> body*


attr2selector
-------------

Converts class and id attributes to their shorthand counterparts

 - *div[id=blah][class=blah] -> div#blah.blah*


strict-id
---------

Promotes nested id's to the front of the selector

 - *body > div#elem p -> #elem p*


pseudo-space
------------

Add space after :first-letter and :first-line pseudo selectors, for ie6

 - *a:first-line{ -> a:first-line {*


directional-compress
--------------------

Compresses single defined multi-directional properties

 - *margin: 15px 25px 15px 25px -> margin:15px 25px*


organize
--------

Combines multiply defined selectors and details

 - *p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;}*
 - *p{color:blue;} a{color:blue;} -> p,a{color:blue;}*



csw-combine
-----------

Combines color/style/width properties

 - *border-style:dashed;border-color:black;border-width:4px; -> border:4px dashed black*


auralcp-combine
---------------

Combines cue/pause properties

 - *cue-before: url(before.au); cue-after: url(after.au) -> cue:url(before.au) url(after.au)*


mp-combine
----------

Combines margin/padding directionals

 - *margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -> margin:10px 5px 4px 1px;*


border-combine
--------------

Combines border directionals

 - *border-top|right|bottom|left:1px solid black -> border:1px solid black*


font-combine
------------

Combines font properties

 - *font-size:12pt; font-family: arial; -> font:12pt arial*


background-combine
------------------

Combines background properties

 - *background-color: black; background-image: url(bgimg.jpeg); -> background:black url(bgimg.jpeg)*


list-combine
------------

Combines list-style properties

 - *list-style-type: round; list-style-position: outside -> list-style:round outside*


border-radius-combine
---------------------

Combines border-radius properties

	{
	 border-top-left-radius: 10px;
	 border-top-right-radius: 10px;
	 border-bottom-right-radius: 10px;
	 border-bottom-left-radius: 10px;
	}
	-> { border-radius: 10px; }


unnecessary-semicolons
---------------------- 

Removes the last semicolon of a rule set

 - *{margin: 2px; color: blue;} -> {margin: 2px; color: blue}*


rm-multi-define
---------------

Removes multiple declarations within the same rule set

 - *{color:black;font-size:12pt;color:red;} -> {color:red;font-size:12pt;}*


add-unknown
-----------

Adds unknown artifacts to a comment block at the top of output.


readability
-----------

Readability of Compressed Output.

	CSSCompression::READ_MAX; // Maximum readability
	CSSCompression::READ_MED; // Medium readability
	CSSCompression::READ_MIN; // Minimum readability
	CSSCompression::READ_NONE; // No readability
