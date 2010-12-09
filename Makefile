#
# CSS Compressor [VERSION]
# [DATE]
# Corey Hart @ http://www.codenothing.com
#
.PHONY: benchmark test

all:
	@echo "\n\x1B[1;31mPC_LOAD_LETTER\x1B[0m\n"
test:
	@php unit/start.php

test-all:
	@php unit/start.php all

single:
	@php unit/single.php

benchmark:
	@php unit/benchmark/benchmark.php
