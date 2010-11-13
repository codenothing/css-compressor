#
# CSS Compressor [VERSION]
# [DATE]
# Corey Hart @ http://www.codenothing.com
#
.PHONY: benchmark test

test:
	@php unit/start.php

test-all:
	@php unit/start.php all

benchmark:
	@php unit/benchmark/benchmark.php
