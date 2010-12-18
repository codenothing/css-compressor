Contribute
==========

This project is always in dire need of help in any form, I only have 2 rules:

- All sandboxed tests must pass.

For nix users: From the root directory, run 'make test'  
For windows users: Run 'php unit/start.php'

- Tab character indentations

I don't care if you like 2,3,4,8,16 spaced indentation, just make sure it's a tab character and not spaces.


make test
---------

This command will run all sandboxed tests to check that most functions run the way they should.


make test-focus
---------------

This command runs a focused test on a single function for development. Open up unit/focus.php for configuration.


make test-file
--------------

This command runs a focused test on a single file. Make sure the original resides in unit/sheets/original/ and the expected
output resides in unit/sheets/expected/. Open up unit/file.php for configuration


make test-all
-------------

This command runs all sandboxed tests, as well as double compressions on any stylesheets in benchmark src. Doesn't always pass,
but is a helpful check to see that most compression is done the first time around.


make benchmark
--------------

This command runs the benchmark process. This will create a new temp directory for regression testing and comparison.


make benchmark VERSION=3.0
--------------------------

The benchmark command takes an optional assignment which will strictly do regression testing against the given version.


make clean
----------

This command removes all generated files used for comparisons and benchmarks.


unit/src/sandbox.json
---------------------

The sandbox json spec, is exactly that, a sandbox of tests to run against as many methods possible in each of the classes.
Take a look at some of the other examples to get an idea of how to add/modify to the list.
