[CSS Compressor](http://www.codenothing.com/css-compressor/)
========================

Javascript Based CSS Compressor that is enviorment independent(works on both the server and browser).


Usage
-----

	var compressor = new CSSCompressor( [ options ] );
	compressor.compress( css ); // Returns compressed css


Option Handling
---------------

	// Combines defaults with options passed in
	var compressor = new CSSCompressor( options );

	// Returns entire options array
	var options = compressor.option();

	// Returns the readability value
	var readability = compressor.option( 'readability' );

	// Sets the readability to non-readable (fully compressed)
	compressor.option( 'readability', CSSCompression.read.none );

	// Can also pass an object to be merged with the current options
	compressor.option( newoptions );


Singleton Instance
------------------

Yes the compressor provides a singleton access method, but use it wisely.

	var compressor = CSSCompressor.getInstance();


Readability
-----------

The compressor class provides static integers that map to the internal readability values

	CSSCompressor.read.max // Maximum Readability
	CSSCompressor.read.med // Medium readability of output
	CSSCompressor.read.min // Minimal readability of output
	CSSCompressor.read.none // No readability of output (full compression into single line)


Credits
--------
[Corey Hart](http://www.codenothing.com) - Creator

[Martin Zvarík](http://www.teplaky.net/) - Pointed out the url and empty definition bug(in the php version).

[Phil DeJarnett](http://www.overzealous.com/) - Pointed out splitting(and numerous other) problems(in the php version)
