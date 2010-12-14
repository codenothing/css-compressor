#
# CSS Compressor [VERSION]
# [DATE]
# Corey Hart @ http://www.codenothing.com
#
.PHONY: benchmark test

all:
	@echo "\n\x1B[1;31mPC_LOAD_LETTER\x1B[0m\n"

test:
	@php unit/Start.php

test-all:
	@php unit/Start.php all

test-focus:
	@php unit/Focus.php

test-file:
	@php unit/File.php

benchmark:
	@php unit/benchmark/benchmark.php
