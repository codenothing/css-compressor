/*
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 
(function( global, undefined ) {


// Utilities

function extend(){
	var args = Array.prototype.slice.call( arguments ), deep = false, target = args.shift(), i = -1, l = args.length, name, copy;

	if ( typeof target == 'boolean' ) {
		deep = target;
		target = args.shift();
		l = args.length;
	}

	for ( ; ++i < l; ) {
		copy = args[ i ];
		for ( name in copy ) {
			target[ name ] = deep && typeof copy[ name ] == 'object' ?
				extend( deep, target[ name ], copy[ name ] ) :
				copy[ name ];
		}
	}

	return target;
}



// Constructor
function CSSCompressor( options ) {
	if ( ! ( this instanceof CSSCompressor ) ) {
		return new CSSCompressor( css, options );
	}

	// Defaults
	this.selectors = [];
	this.options = extend( true, CSSCompressor.defaults, options || {} );
	this.stats = {};
	this.importcss = '';
	this.media = {
		found: false,
		str: ''
	};
};

CSSCompressor.prototype = {

	// Option handling
	option: function( name, value ) {
		return arguments.length === 0 ? this.options :
			typeof name == 'object' ? extend( true, this.options, name ) :
			arguments.length === 1 ? this.options[ name ] :
			( this.options[ name ] = value );
	}
};



// Info
extend(CSSCompressor, {
	// Default Options
	defaults: {
		// Converts long color names to short hex names
		// (aliceblue -> #f0f8ff)
		'color-long2hex': true,

		// Converts rgb colors to hex
		// (rgb(159,80,98) -> #9F5062, rgb(100%) -> #FFFFFF)
		'color-rgb2hex': true,

		// Converts long hex codes to short color names (#f5f5dc -> beige)
		// Only works on latest browsers, careful when using
		'color-hex2shortcolor': false,

		// Converts long hex codes to short hex codes
		// (#44ff11 -> #4f1)
		'color-hex2shorthex': true,

		// Converts font-weight names to numbers
		// (bold -> 700)
		'fontweight2num': true,

		// Removes zero decimals and 0 units
		// (15.0px -> 15px || 0px -> 0)
		'format-units': true,

		// Lowercases html tags from list
		// (BODY -> body)
		'lowercase-selectors': true,

		// Compresses single defined multi-directional properties
		// (margin: 15px 25px 15px 25px -> margin:15px 25px)
		'directional-compress': true,

		// Combines multiply defined selectors
		// (p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;})
		'multiple-selectors': true,

		// Combines selectors with same details
		// (p{color:blue;} a{color:blue;} -> p,a{color:blue;})
		'multiple-details': true,

		// Combines color/style/width properties
		// (border-style:dashed;border-color:black;border-width:4px; -> border:4px dashed black)
		'csw-combine': true,

		// Combines cue/pause properties
		// (cue-before: url(before.au); cue-after: url(after.au) -> cue:url(before.au) url(after.au))
		'auralcp-combine': true,

		// Combines margin/padding directionals
		// (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -> margin:10px 5px 4px 1px;)
		'mp-combine': true,

		// Combines border directionals
		// (border-top|right|bottom|left:1px solid black -> border:1px solid black)
		'border-combine': true,

		// Combines font properties
		// (font-size:12pt; font-family: arial; -> font:12pt arial)
		'font-combine': true,

		// Combines background properties
		// (background-color: black; background-image: url(bgimg.jpeg); -> background:black url(bgimg.jpeg))
		'background-combine': true,

		// Combines list-style properties
		// (list-style-type: round; list-style-position: outside -> list-style:round outside)
		'list-combine': true,

		// Removes the last semicolon of a property set
		// ({margin: 2px; color: blue;} -> {margin: 2px; color: blue})
		'unnecessary-semicolons': true,

		// Removes multiply defined properties
		// STRONGLY SUGGESTED TO KEEP THIS TRUE
		'rm-multi-define': true,

		// Readibility of Compressed Output, Defaults to none
		'readability': 0
	}

	// Readability markers
	readability: {
		none: 0,
		min: 1,
		med: 2,
		max: 3
	},

	// Singleton pattern (why o why???)
	_instance: undefined,
	getInstance: function(){
		if ( ! CSSCompressor._instance ) {
			CSSCompressor._instance = new CSSCompressor();
		}

		return CSSCompressor._instance;
	},

	// Font Weight conversions
	fontweight: {
		lighter: 100,
		normal: 400,
		bold: 700,
		bolder: 900
	},

	// Hex to shortname conversion
	hex2short: {
		"#f0ffff": "azure",
		"#f5f5dc": "beige",
		"#ffe4c4": "bisque",
		"#a52a2a": "brown",
		"#ff7f50": "coral",
		"#ffd700": "gold",
		"#808080": "gray",
		"#008000": "green",
		"#4b0082": "indigo",
		"#fffff0": "ivory",
		"#f0e68c": "khaki",
		"#faf0e6": "linen",
		"#800000": "maroon",
		"#000080": "navy",
		"#808000": "olive",
		"#ffa500": "orange",
		"#da70d6": "orchid",
		"#cd853f": "peru",
		"#ffc0cb": "pink",
		"#dda0dd": "plum",
		"#800080": "purple",
		"#ff0000": "red",
		"#fa8072": "salmon",
		"#a0522d": "sienna",
		"#c0c0c0": "silver",
		"#fffafa": "snow",
		"#d2b48c": "tan",
		"#008080": "teal",
		"#ff6347": "tomato",
		"#ee82ee": "violet",
		"#f5deb3": "wheat",

		// Red is the only string value that is less than the 3# hex value
		"#f00": "red"
	},

	long2hex: {
		"aliceblue": "#f0f8ff",
		"antiquewhite": "#faebd7",
		"aquamarine": "#7fffd4",
		"bisque": "#ffe4c4",
		"black": "#000000",
		"blanchedalmond": "#ffebcd",
		"blueviolet": "#8a2be2",
		"burlywood": "#deb887",
		"cadetblue": "#5f9ea0",
		"chartreuse": "#7fff00",
		"chocolate": "#d2691e",
		"coral": "#ff7f50",
		"cornflowerblue": "#6495ed",
		"cornsilk": "#fff8dc",
		"crimson": "#dc143c",
		"cyan": "#00ffff",
		"darkblue": "#00008b",
		"darkcyan": "#008b8b",
		"darkgoldenrod": "#b8860b",
		"darkgray": "#a9a9a9",
		"darkgreen": "#006400",
		"darkkhaki": "#bdb76b",
		"darkmagenta": "#8b008b",
		"darkolivegreen": "#556b2f",
		"darkorange": "#ff8c00",
		"darkorchid": "#9932cc",
		"darkred": "#8b0000",
		"darksalmon": "#e9967a",
		"darkseagreen": "#8fbc8f",
		"darkslateblue": "#483d8b",
		"darkslategray": "#2f4f4f",
		"darkturquoise": "#00ced1",
		"darkviolet": "#9400d3",
		"deeppink": "#ff1493",
		"deepskyblue": "#00bfff",
		"dimgray": "#696969",
		"dodgerblue": "#1e90ff",
		"firebrick": "#b22222",
		"floralwhite": "#fffaf0",
		"forestgreen": "#228b22",
		"fuchsia": "#ff00ff",
		"gainsboro": "#dcdcdc",
		"ghostwhite": "#f8f8ff",
		"goldenrod": "#daa520",
		"green": "#008800",
		"greenyellow": "#adff2f",
		"honeydew": "#f0fff0",
		"hotpink": "#ff69b4",
		"indianred ": "#cd5c5c",
		"indigo  ": "#4b0082",
		"lavender": "#e6e6fa",
		"lavenderblush": "#fff0f5",
		"lawngreen": "#7cfc00",
		"lemonchiffon": "#fffacd",
		"lightblue": "#add8e6",
		"lightcoral": "#f08080",
		"lightcyan": "#e0ffff",
		"lightgoldenrodyellow": "#fafad2",
		"lightgrey": "#d3d3d3",
		"lightgreen": "#90ee90",
		"lightpink": "#ffb6c1",
		"lightsalmon": "#ffa07a",
		"lightseagreen": "#20b2aa",
		"lightskyblue": "#87cefa",
		"lightslategray": "#778899",
		"lightsteelblue": "#b0c4de",
		"lightyellow": "#ffffe0",
		"lime": "#00ff00",
		"limegreen": "#32cd32",
		"magenta": "#ff00ff",
		"maroon": "#800000",
		"mediumaquamarine": "#66cdaa",
		"mediumblue": "#0000cd",
		"mediumorchid": "#ba55d3",
		"mediumpurple": "#9370d8",
		"mediumseagreen": "#3cb371",
		"mediumslateblue": "#7b68ee",
		"mediumspringgreen": "#00fa9a",
		"mediumturquoise": "#48d1cc",
		"mediumvioletred": "#c71585",
		"midnightblue": "#191970",
		"mintcream": "#f5fffa",
		"mistyrose": "#ffe4e1",
		"moccasin": "#ffe4b5",
		"navajowhite": "#ffdead",
		"oldlace": "#fdf5e6",
		"olivedrab": "#6b8e23",
		"orange": "#ffa500",
		"orangered": "#ff4500",
		"orchid": "#da70d6",
		"palegoldenrod": "#eee8aa",
		"palegreen": "#98fb98",
		"paleturquoise": "#afeeee",
		"palevioletred": "#d87093",
		"papayawhip": "#ffefd5",
		"peachpuff": "#ffdab9",
		"powderblue": "#b0e0e6",
		"purple": "#800080",
		"rosybrown": "#bc8f8f",
		"royalblue": "#4169e1",
		"saddlebrown": "#8b4513",
		"salmon": "#fa8072",
		"sandybrown": "#f4a460",
		"seagreen": "#2e8b57",
		"seashell": "#fff5ee",
		"sienna": "#a0522d",
		"silver": "#c0c0c0",
		"skyblue": "#87ceeb",
		"slateblue": "#6a5acd",
		"slategray": "#708090",
		"springgreen": "#00ff7f",
		"steelblue": "#4682b4",
		"thistle": "#d8bfd8",
		"tomato": "#ff6347",
		"turquoise": "#40e0d0",
		"violet": "#ee82ee",
		"white": "#ffffff",
		"whitesmoke": "#f5f5f5",
		"yellow": "#ffff00",
		"yellowgreen": "#9acd32"
	},

	template: [
		"<table cellspacing='1' cellpadding='2' style='width:400px;margin-bottom:20px;'>",
			"<tr bgcolor='#d1d1d1' align='center'>",
				"<th bgcolor='#f1f1f1' style='color:#8B0000;'>Results &raquo;</th>",
				"<th width='100'>Before</th>",
				"<th width='100'>After</th>",
				"<th width='100'>Compresssion</th>",
			"</tr>",
			"<tr bgcolor='#f1f1f1' align='center'>",
				"<th bgcolor='#d1d1d1'>Time</th>",
				"<td>-</td>",
				"<td>-</td>",
				"<td>#{time} seconds</td>",
			"</tr>",
			"<tr bgcolor='#f1f1f1' align='center'>",
				"<th bgcolor='#d1d1d1'>Selectors</th>",
				"<td>#{selectors:before}</td>",
				"<td>#{selectors:after}</td>",
				"<td>#{selectors:final}</td>",
			"</tr>",
			"<tr bgcolor='#f1f1f1' align='center'>",
				"<th bgcolor='#d1d1d1'>Properties</th>",
				"<td>#{props:before}</td>",
				"<td>#{props:after}</td>",
				"<td>#{props:final}</td>",
			"</tr>",
			"<tr bgcolor='#f1f1f1' align='center'>",
				"<th bgcolor='#d1d1d1'>Size</th>",
				"<td>#{size:before}</td>",
				"<td>#{size:after}</td>",
				"<td>#{size:final}</td>",
			"</tr>",
		"</table>"
	].join('')
});


// Expose CSSCompressor
global.CSSCompressor = CSSCompressor;

})( exports || this );
