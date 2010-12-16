Contribute
==========

This project is always in dire need of help in any form, I only have 2 rules:

- All unit tests must pass.

For nix users: From the root directory, run 'make test'  
For windows users: Run 'php unit/start.php'

- Tab character indentations

I don't care if you like 2,3,4,8,16 spaced indentation, just make sure it's a tab character and not spaces.


unit/src/sandbox.json
---------------------

	// Run sandbox tests
	$ make test

The sandbox json spec, is exactly that, a sandbox of tests to run against as many methods possible in each of the classes.
Take a look at some of the other examples to get an idea of how to add/modify to the list.


unit/focus.php
--------------

	// Run focused test
	$ make test-focus

Runs a focused test on a single function within a class. Read comments to place right configuration


unit/file.php
-------------

	// Run single file test
	$ make test-file

Runs a focused test on a single file inside unit/sheets. Read comment to place right configuration
