<?php

require 'vendor/autoload.php';

use Eru\AvroPhonetic\Avro;

// Convert input to Bangla
$input = "ami bangla gan gai";
$output = Avro::to($input);

echo "Input: $input\n";
echo "Output: $output\n";