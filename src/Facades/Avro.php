<?php

namespace Eru\AvroPhonetic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string parse(string $text)
 * @method static string convert(string $text)
 * @method static string to(string $text)
 * @method static \Eru\AvroPhonetic\PhoneticParser getParser()
 * 
 * @see \Eru\AvroPhonetic\Avro
 */
class Avro extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Eru\AvroPhonetic\Avro::class;
    }
}
