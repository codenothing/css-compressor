cli.php
=======

CSSCompression comes with a basic cli script to run the compressor from the command line.


Usage
-----

	php cli.php [options] [files]


options
-------

The cli script itself only has a single option "imports", which tells the class to get relative stylesheets defined in import statements

	php cli.php -i styles.css
	php cli.php --imports styles.css

It can also take a mode, but only in long-hand notation.

	php cli.php --mode=sane styles.css

And lastly, all CSSCompression options can be passed in longhand notation

	php cli.php --organize=true styles.css
