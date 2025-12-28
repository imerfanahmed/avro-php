<?php

namespace Eru\AvroPhonetic;

/**
 * Main Avro Phonetic class
 * 
 * This is the main entry point for using Avro Phonetic.
 * It can be used standalone or with Laravel.
 */
class Avro
{
    /** @var PhoneticParser */
    private $parser;
    
    /** @var Avro|null */
    private static $instance = null;
    
    /** @var array|null */
    private static $grammar = null;

    /**
     * Create a new Avro instance
     *
     * @param array|null $grammar Custom grammar rules (optional)
     */
    public function __construct($grammar = null)
    {
        $grammar = $grammar !== null ? $grammar : self::loadDefaultGrammar();
        $this->parser = new PhoneticParser($grammar);
    }

    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Load the default grammar file
     *
     * @return array
     * @throws \RuntimeException
     */
    private static function loadDefaultGrammar()
    {
        if (self::$grammar !== null) {
            return self::$grammar;
        }

        $grammarPath = self::getGrammarPath();
        
        if (!file_exists($grammarPath)) {
            throw new \RuntimeException("Grammar file not found: {$grammarPath}");
        }
        
        $content = file_get_contents($grammarPath);
        $grammar = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid grammar JSON: " . json_last_error_msg());
        }
        
        self::$grammar = $grammar;
        
        return $grammar;
    }

    /**
     * Get the path to the grammar file
     *
     * @return string
     */
    public static function getGrammarPath()
    {
        // Check if Laravel and config exists
        if (function_exists('config') && config('avro-phonetic.grammar_path')) {
            return config('avro-phonetic.grammar_path');
        }
        
        // Default path
        return __DIR__ . '/../resources/grammar.json';
    }

    /**
     * Parse/convert text to Bengali
     *
     * @param string $text The text to convert
     * @return string The converted Bengali text
     */
    public function parse($text)
    {
        return $this->parser->parse($text);
    }

    /**
     * Alias for parse()
     *
     * @param string $text The text to convert
     * @return string The converted Bengali text
     */
    public function convert($text)
    {
        return $this->parse($text);
    }

    /**
     * Static method to quickly convert text
     *
     * @param string $text The text to convert
     * @return string The converted Bengali text
     */
    public static function to($text)
    {
        return self::getInstance()->parse($text);
    }

    /**
     * Get the underlying parser instance
     *
     * @return PhoneticParser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Create a new instance with custom grammar
     *
     * @param array $grammar Custom grammar rules
     * @return self
     */
    public static function withGrammar(array $grammar)
    {
        return new self($grammar);
    }

    /**
     * Create a new instance from a grammar file path
     *
     * @param string $path Path to the grammar JSON file
     * @return self
     * @throws \RuntimeException
     */
    public static function fromGrammarFile($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Grammar file not found: {$path}");
        }
        
        $content = file_get_contents($path);
        $grammar = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid grammar JSON: " . json_last_error_msg());
        }
        
        return new self($grammar);
    }
}
