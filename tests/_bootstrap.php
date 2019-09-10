<?php

// This is global bootstrap for autoloading

// VCR cassette path
use VCR\VCR;

VCR::configure()->enableLibraryHooks(['curl'])->setCassettePath(__DIR__ . '/_data/vcr');
