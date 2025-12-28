<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\PhoneticParser;

class PhoneticParserTest extends TestCase
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

    // ==================== Basic Conversion Tests ====================

    public function testBasicConversion()
    {
        $this->assertEquals('আমি', $this->parser->parse('ami'));
    }

    public function testConvertAlias()
    {
        $this->assertEquals('আমি', $this->parser->convert('ami'));
    }

    public function testSentenceConversion()
    {
        $result = $this->parser->parse('ami banglay gan gai');
        $this->assertStringContainsString('আমি', $result);
        $this->assertStringContainsString('গান', $result);
        $this->assertStringContainsString('গাই', $result);
    }

    public function testEmptyString()
    {
        $this->assertEquals('', $this->parser->parse(''));
    }

    public function testSingleCharacter()
    {
        $this->assertEquals('আ', $this->parser->parse('a'));
        $this->assertEquals('ক', $this->parser->parse('k'));
    }

    // ==================== Vowel Tests ====================

    public function testVowels()
    {
        $vowelTests = array(
            'a' => 'আ',
            'i' => 'ই',
            'u' => 'উ',
            'e' => 'এ',
            'o' => 'অ',
        );

        foreach ($vowelTests as $input => $expected) {
            $this->assertEquals($expected, $this->parser->parse($input), "Failed for vowel: $input");
        }
    }

    public function testVowelCombinations()
    {
        $this->assertEquals('ঐ', $this->parser->parse('OI'));
        $this->assertEquals('ঔ', $this->parser->parse('OU'));
    }

    // ==================== Consonant Tests ====================

    public function testBasicConsonants()
    {
        $consonantTests = array(
            'k' => 'ক',
            'kh' => 'খ',
            'g' => 'গ',
            'gh' => 'ঘ',
            'ng' => 'ং',
        );

        foreach ($consonantTests as $input => $expected) {
            $this->assertEquals($expected, $this->parser->parse($input), "Failed for consonant: $input");
        }
    }

    // ==================== Rule-Based Conversion Tests ====================

    /**
     * Test context-sensitive rules: 'rri' at start vs after consonant
     */
    public function testRriRules()
    {
        // 'rri' at the beginning should become ঋ (non-consonant prefix)
        $this->assertEquals('ঋ', $this->parser->parse('rri'));
        
        // 'rri' after consonant should become ৃ
        $this->assertEquals('কৃ', $this->parser->parse('krri'));
    }

    /**
     * Test OI rules: at start vs after consonant
     */
    public function testOIRules()
    {
        // 'OI' at beginning -> ঐ
        $this->assertEquals('ঐ', $this->parser->parse('OI'));
        
        // 'OI' after consonant -> ৈ
        $this->assertEquals('কৈ', $this->parser->parse('kOI'));
    }

    /**
     * Test OU rules: at start vs after consonant
     */
    public function testOURules()
    {
        // 'OU' at beginning -> ঔ
        $this->assertEquals('ঔ', $this->parser->parse('OU'));
        
        // 'OU' after consonant -> ৌ
        $this->assertEquals('কৌ', $this->parser->parse('kOU'));
    }

    // ==================== Complex Word Tests ====================

    public function testCommonWords()
    {
        $wordTests = array(
            'bangladesh' => 'বাংলাদেশ',
            'bangla' => 'বাংলা',
            'amar' => 'আমার',
            'sonar' => 'সনার',
        );

        foreach ($wordTests as $input => $expected) {
            $this->assertEquals($expected, $this->parser->parse($input), "Failed for word: $input");
        }
    }

    public function testConjuncts()
    {
        // Test some conjunct characters
        $this->assertNotEmpty($this->parser->parse('ndr')); // ন্দ্র
        $this->assertNotEmpty($this->parser->parse('str')); // স্ত্র
    }

    // ==================== Case Sensitivity Tests ====================

    public function testCaseSensitivity()
    {
        // These should produce different results
        $this->assertNotEquals(
            $this->parser->parse('o'),
            $this->parser->parse('O')
        );
        
        // 'o' -> অ, 'O' -> ও (different Bengali characters)
    }

    public function testMixedCase()
    {
        // Test that case-sensitive characters are preserved
        $result = $this->parser->parse('OItihasik');
        $this->assertNotEmpty($result);
    }

    // ==================== Punctuation Tests ====================

    public function testPunctuation()
    {
        // Period and other punctuation pass through as-is
        $this->assertEquals('.', $this->parser->parse('.'));
        $this->assertEquals('...', $this->parser->parse('...'));
    }

    public function testPunctuationInSentence()
    {
        $result = $this->parser->parse('ami banglay gan gai.');
        $this->assertStringEndsWith('.', $result);
    }

    // ==================== Special Characters Tests ====================

    public function testNumbers()
    {
        // Numbers should pass through
        $this->assertEquals('১২৩', $this->parser->parse('123'));
    }

    public function testSpaces()
    {
        $this->assertEquals('আমি তুমি', $this->parser->parse('ami tumi'));
    }

    public function testSpecialCharacters()
    {
        // Characters not in patterns should pass through
        $result = $this->parser->parse('ami@tumi');
        $this->assertStringContainsString('@', $result);
    }

    // ==================== Edge Cases ====================

    public function testRepeatedCharacters()
    {
        $result = $this->parser->parse('aaaa');
        $this->assertNotEmpty($result);
    }

    public function testLongText()
    {
        $longText = str_repeat('ami bangla ', 100);
        $result = $this->parser->parse($longText);
        $this->assertNotEmpty($result);
    }

    public function testUnicodeInput()
    {
        // If Bengali is passed, it should remain unchanged (not in patterns)
        $bengaliText = 'আমি';
        $result = $this->parser->parse($bengaliText);
        $this->assertEquals($bengaliText, $result);
    }

    // ==================== Pattern Priority Tests ====================

    public function testLongestMatchPriority()
    {
        // 'ng' vs 'ngg' - should match longer pattern first
        $result = $this->parser->parse('Ngg');
        $this->assertEquals('ঙ্গ', $result);
    }

    public function testMultiCharacterPatterns()
    {
        // Test patterns with multiple characters
        $this->assertEquals('ক্ষ', $this->parser->parse('kSh'));
        $this->assertEquals('ক্ষ', $this->parser->parse('kkh'));
    }

    // ==================== Sentence Tests ====================

    /**
     * @dataProvider sentenceProvider
     */
    public function testSentences($input, $expectedContains)
    {
        $result = $this->parser->parse($input);
        $this->assertStringContainsString($expectedContains, $result);
    }

    public function sentenceProvider()
    {
        return array(
            array('ami banglay gan gai', 'আমি'),
            array('amar sonar bangla', 'আমার'),
            array('tumi kemon acho', 'তুমি'),
        );
    }
}
