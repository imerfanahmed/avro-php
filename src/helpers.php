<?php

use Eru\AvroPhonetic\Avro;

if (!function_exists('avro')) {
    /**
     * Get the Avro instance or convert text to Bengali
     *
     * @param string|null $text The text to convert (optional)
     * @return Avro|string Returns Avro instance if no text provided, otherwise returns converted text
     */
    function avro($text = null)
    {
        if ($text === null) {
            return Avro::getInstance();
        }
        
        return Avro::to($text);
    }
}

if (!function_exists('bangla')) {
    /**
     * Convert text to Bengali using Avro Phonetic
     *
     * @param string $text The text to convert
     * @return string The converted Bengali text
     */
    function bangla($text)
    {
        return Avro::to($text);
    }
}
