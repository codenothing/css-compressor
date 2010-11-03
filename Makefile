#
# CSS Compressor [VERSION]
# [DATE]
# Corey Hart @ http://www.codenothing.com
#
.PHONY: benchmark test

test:
	@php unit/start.php

benchmark:
	@php benchmark/run.php
