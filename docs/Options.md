Options
=========


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


pseduo-space
------------

Add space after pseduo selectors, for ie6

 - *a:first-child{ -> a:first-child {*


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


unnecessary-semicolons
----------------------

Removes the last semicolon of a property set

 - *{margin: 2px; color: blue;} -> {margin: 2px; color: blue}*


readability
-----------

Readibility of Compressed Output, Defaults to none
