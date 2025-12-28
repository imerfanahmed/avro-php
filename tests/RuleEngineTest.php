<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\PhoneticParser;

/**
 * Tests specifically for the context-sensitive rule engine
 */
class RuleEngineTest extends TestCase
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

    // ==================== Prefix Rule Tests ====================

    /**
     * Test prefix scope: consonant
     * Pattern should behave differently when preceded by consonant
     */
    public function testPrefixConsonantRule()
    {
        // 'rri' not preceded by consonant -> ঋ
        $this->assertEquals('ঋ', $this->parser->parse('rri'));
        
        // 'rri' preceded by consonant -> ৃ
        $this->assertEquals('কৃ', $this->parser->parse('krri'));
        $this->assertEquals('গৃ', $this->parser->parse('grri'));
    }

    /**
     * Test prefix scope: vowel
     */
    public function testPrefixVowelRule()
    {
        // 'y' behaves differently based on prefix
        $resultAfterVowel = $this->parser->parse('ay');
        $resultAfterConsonant = $this->parser->parse('ky');
        
        // These should be different
        $this->assertNotEquals($resultAfterVowel, $resultAfterConsonant);
    }

    /**
     * Test prefix scope: punctuation (beginning of word/string)
     */
    public function testPrefixPunctuationRule()
    {
        // At the start of string (treated as after punctuation)
        $atStart = $this->parser->parse('OI');
        
        // After a consonant
        $afterConsonant = $this->parser->parse('kOI');
        
        $this->assertEquals('ঐ', $atStart);
        $this->assertStringEndsWith('ৈ', $afterConsonant);
    }

    // ==================== Suffix Rule Tests ====================

    /**
     * Test suffix scope rules
     */
    public function testSuffixRules()
    {
        // Test patterns that check what comes after
        $result1 = $this->parser->parse('aa');
        $result2 = $this->parser->parse('a');
        
        // These might be same or different depending on grammar rules
        $this->assertNotEmpty($result1);
        $this->assertNotEmpty($result2);
    }

    // ==================== Exact Match Rules ====================

    /**
     * Test exact prefix/suffix match rules
     */
    public function testExactMatchRules()
    {
        // 'y' after 'a' has specific handling
        $resultAY = $this->parser->parse('ay');
        
        // 'y' in other contexts
        $resultKY = $this->parser->parse('ky');
        
        $this->assertNotEmpty($resultAY);
        $this->assertNotEmpty($resultKY);
    }

    // ==================== Negative Scope Tests ====================

    /**
     * Test negative scope (!) rules
     * !consonant means "not a consonant" (vowel or punctuation)
     */
    public function testNegativeConsonantScope()
    {
        // rri: !consonant prefix should match at start (punctuation)
        $this->assertEquals('ঋ', $this->parser->parse('rri'));
        
        // After vowel (also !consonant)
        $result = $this->parser->parse('arri');
        $this->assertStringContainsString('ঋ', $result);
    }

    /**
     * Test !vowel (negative vowel) scope
     */
    public function testNegativeVowelScope()
    {
        // Patterns that check for !vowel prefix/suffix
        $result1 = $this->parser->parse('ka');
        $result2 = $this->parser->parse('a');
        
        $this->assertNotEmpty($result1);
        $this->assertNotEmpty($result2);
    }

    // ==================== Combined Rules Tests ====================

    /**
     * Test patterns with multiple match conditions
     */
    public function testMultipleMatchConditions()
    {
        // Some patterns have multiple conditions that must all match
        // 'y' pattern has complex rules with multiple conditions
        
        $results = array(
            $this->parser->parse('y'),
            $this->parser->parse('ay'),
            $this->parser->parse('ky'),
            $this->parser->parse('ayn'),
        );
        
        foreach ($results as $result) {
            $this->assertNotEmpty($result);
        }
    }

    // ==================== Rule Priority Tests ====================

    /**
     * Rules should be checked in order, first matching rule wins
     */
    public function testRulePriority()
    {
        // OI has rules: first !consonant prefix -> ঐ, then punctuation prefix -> ঐ
        // At start, both would match, but first rule should win
        $this->assertEquals('ঐ', $this->parser->parse('OI'));
    }

    // ==================== No Rule Match Tests ====================

    /**
     * When no rules match, default replacement should be used
     */
    public function testDefaultReplacementWhenNoRuleMatches()
    {
        // 'rri' after consonant - no rule matches, use default ৃ
        $result = $this->parser->parse('krri');
        $this->assertEquals('কৃ', $result);
    }

    // ==================== Boundary Condition Tests ====================

    /**
     * Test rules at string boundaries
     */
    public function testRulesAtStringStart()
    {
        // Patterns at the very start should treat prefix as punctuation
        $this->assertEquals('ঋ', $this->parser->parse('rri'));
        $this->assertEquals('ঐ', $this->parser->parse('OI'));
        $this->assertEquals('ঔ', $this->parser->parse('OU'));
    }

    /**
     * Test rules at string end
     */
    public function testRulesAtStringEnd()
    {
        // Patterns at the end should treat suffix as punctuation
        $result = $this->parser->parse('kar');
        $this->assertNotEmpty($result);
    }

    // ==================== Complex Sentence Tests ====================

    /**
     * Test rules work correctly in complex sentences
     */
    public function testRulesInSentences()
    {
        $sentence = 'ami rri te shuru kori OI dike takao';
        $result = $this->parser->parse($sentence);
        
        // Should contain ঋ (rri at word start after space)
        $this->assertStringContainsString('ঋ', $result);
        // Should contain ঐ (OI at word start after space)
        $this->assertStringContainsString('ঐ', $result);
    }

    /**
     * Test rule engine with repeated patterns
     */
    public function testRulesWithRepeatedPatterns()
    {
        $result = $this->parser->parse('rri krri rri');
        
        // First rri -> ঋ, krri -> কৃ, third rri -> ঋ
        $this->assertStringContainsString('ঋ', $result);
        $this->assertStringContainsString('ৃ', $result);
    }
}
