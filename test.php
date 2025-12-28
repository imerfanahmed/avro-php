<?php

// For composer-based autoloading
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    // Manual loading for testing without composer
    require 'src/TrieNode.php';
    require 'src/Trie.php';
    require 'src/PhoneticParser.php';
    require 'src/Avro.php';
    require 'src/helpers.php';
}

use Eru\AvroPhonetic\Avro;

// Convert input to Bangla
$input = "ami bangla gan gai";
$output = Avro::to($input);

echo "Input: $input\n";
echo "Output: $output\n";