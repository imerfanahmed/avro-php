<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\Avro;
use Eru\AvroPhonetic\PhoneticParser;

/**
 * Performance and stress tests
 */
class PerformanceTest extends TestCase
{
    /** @var PhoneticParser */
    private $parser;

    /** @var array */
    private static $grammar;

    public static function setUpBeforeClass(): void
    {
        $grammarPath = __DIR__ . '/../resources/grammar.json';
        self::$grammar = json_decode(file_get_contents($grammarPath), true);
    }

    protected function setUp(): void
    {
        $this->parser = new PhoneticParser(self::$grammar);
    }

    // ==================== Performance Tests ====================

    /**
     * Test that parsing completes in reasonable time
     */
    public function testParsingPerformance()
    {
        $text = 'ami banglay gan gai amar sonar bangla';
        $iterations = 100;
        
        $start = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $this->parser->parse($text);
        }
        
        $elapsed = microtime(true) - $start;
        $avgTime = ($elapsed / $iterations) * 1000; // ms
        
        // Should complete in less than 10ms per parse on average
        $this->assertLessThan(10, $avgTime, "Average parse time {$avgTime}ms exceeds 10ms");
    }

    /**
     * Test Trie initialization performance
     */
    public function testTrieInitializationPerformance()
    {
        $start = microtime(true);
        
        $parser = new PhoneticParser(self::$grammar);
        
        $elapsed = (microtime(true) - $start) * 1000;
        
        // Trie initialization should complete in less than 100ms
        $this->assertLessThan(100, $elapsed, "Trie initialization took {$elapsed}ms");
    }

    // ==================== Stress Tests ====================

    /**
     * Test with long text
     */
    public function testLongTextParsing()
    {
        $baseText = 'ami banglay gan gai amar sonar bangla ami tomake bhalobashi ';
        $longText = str_repeat($baseText, 100);
        
        $start = microtime(true);
        $result = $this->parser->parse($longText);
        $elapsed = (microtime(true) - $start) * 1000;
        
        $this->assertNotEmpty($result);
        $this->assertLessThan(1000, $elapsed, "Long text parsing took {$elapsed}ms");
    }

    /**
     * Test with many short strings
     */
    public function testManyShortStrings()
    {
        $words = array('ami', 'tumi', 'se', 'amra', 'tomra', 'tara', 'bangla', 'desh');
        $iterations = 500;
        
        $start = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($words as $word) {
                $this->parser->parse($word);
            }
        }
        
        $elapsed = (microtime(true) - $start) * 1000;
        $totalParses = $iterations * count($words);
        
        // Should complete all parses in reasonable time
        $this->assertLessThan(5000, $elapsed, "Many short strings took {$elapsed}ms for {$totalParses} parses");
    }

    /**
     * Test with special characters and edge cases
     */
    public function testEdgeCasePerformance()
    {
        $edgeCases = array(
            '',
            'a',
            str_repeat('a', 100),
            str_repeat('k', 100),
            '123456789',
            '@#$%^&*()',
            "ami\ntumi\nse",
            "ami\ttumi\tse",
        );
        
        $start = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            foreach ($edgeCases as $case) {
                $this->parser->parse($case);
            }
        }
        
        $elapsed = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(1000, $elapsed, "Edge case tests took {$elapsed}ms");
    }

    // ==================== Memory Tests ====================

    /**
     * Test memory usage stays reasonable
     */
    public function testMemoryUsage()
    {
        $initialMemory = memory_get_usage();
        
        // Create multiple parser instances
        $parsers = array();
        for ($i = 0; $i < 10; $i++) {
            $parsers[] = new PhoneticParser(self::$grammar);
        }
        
        $afterCreation = memory_get_usage();
        $memoryPerParser = ($afterCreation - $initialMemory) / 10;
        
        // Each parser should use less than 5MB
        $this->assertLessThan(5 * 1024 * 1024, $memoryPerParser);
    }

    /**
     * Test that repeated parsing doesn't cause memory leaks
     */
    public function testNoMemoryLeak()
    {
        $text = 'ami banglay gan gai';
        
        // Parse many times and check memory doesn't grow unbounded
        $initialMemory = memory_get_usage();
        
        for ($i = 0; $i < 1000; $i++) {
            $this->parser->parse($text);
        }
        
        $finalMemory = memory_get_usage();
        $memoryGrowth = $finalMemory - $initialMemory;
        
        // Memory growth should be minimal (less than 1MB)
        $this->assertLessThan(1024 * 1024, $memoryGrowth);
    }

    // ==================== Singleton Performance ====================

    /**
     * Test singleton access performance
     */
    public function testSingletonPerformance()
    {
        $iterations = 1000;
        
        $start = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            Avro::to('ami');
        }
        
        $elapsed = (microtime(true) - $start) * 1000;
        
        // Should be fast due to singleton caching
        $this->assertLessThan(500, $elapsed, "Singleton access took {$elapsed}ms for {$iterations} calls");
    }
}
