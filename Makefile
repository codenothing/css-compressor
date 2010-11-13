#
# CSS Compressor [VERSION]
# [DATE]
# Corey Hart @ http://www.codenothing.com
#
.PHONY: benchmark test

test:
	@php unit/start.php

test-full:
	@php unit/start.php full

benchmark:
	@php unit/benchmark/benchmark.php
