<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Grammar File Path
    |--------------------------------------------------------------------------
    |
    | This is the path to the Avro Phonetic grammar JSON file. By default,
    | it uses the bundled grammar file. You can customize this by publishing
    | the grammar file and modifying it according to your needs.
    |
    | To publish the grammar file, run:
    | php artisan vendor:publish --tag=avro-phonetic-grammar
    |
    */

    'grammar_path' => env('AVRO_GRAMMAR_PATH', null),

];
