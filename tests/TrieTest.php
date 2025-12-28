<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\Trie;
use Eru\AvroPhonetic\TrieNode;

class TrieTest extends TestCase
{
    /** @var Trie */
    private $trie;

    protected function setUp(): void
    {
        $this->trie = new Trie();
    }

    public function testInsertAndExists()
    {
        $this->trie->insert('hello', 'হেলো');
        
        $this->assertTrue($this->trie->exists('hello'));
        $this->assertFalse($this->trie->exists('hell'));
        $this->assertFalse($this->trie->exists('helloo'));
        $this->assertFalse($this->trie->exists('world'));
    }

    public function testInsertMultiplePatterns()
    {
        $this->trie->insert('a', 'আ');
        $this->trie->insert('aa', 'আা');
        $this->trie->insert('aaa', 'আআআ');
        
        $this->assertTrue($this->trie->exists('a'));
        $this->assertTrue($this->trie->exists('aa'));
        $this->assertTrue($this->trie->exists('aaa'));
    }

    public function testSearchLongestMatch()
    {
        $this->trie->insert('k', 'ক');
        $this->trie->insert('kh', 'খ');
        $this->trie->insert('kha', 'খা');
        
        $result = $this->trie->searchLongest('khan', 0);
        
        $this->assertNotNull($result);
        $this->assertEquals('kha', $result['find']);
        $this->assertEquals('খা', $result['replace']);
    }

    public function testSearchLongestAtPosition()
    {
        $this->trie->insert('a', 'আ');
        $this->trie->insert('mi', 'মি');
        
        $result = $this->trie->searchLongest('ami', 1);
        
        $this->assertNotNull($result);
        $this->assertEquals('mi', $result['find']);
        $this->assertEquals('মি', $result['replace']);
    }

    public function testSearchLongestNoMatch()
    {
        $this->trie->insert('hello', 'হেলো');
        
        $result = $this->trie->searchLongest('world', 0);
        
        $this->assertNull($result);
    }

    public function testSearchAllMatches()
    {
        $this->trie->insert('b', 'ব');
        $this->trie->insert('ba', 'বা');
        $this->trie->insert('ban', 'বান');
        $this->trie->insert('bang', 'বাং');
        
        $results = $this->trie->searchAll('bangla', 0);
        
        $this->assertCount(4, $results);
        // Should be ordered longest first
        $this->assertEquals('bang', $results[0]['find']);
        $this->assertEquals('ban', $results[1]['find']);
        $this->assertEquals('ba', $results[2]['find']);
        $this->assertEquals('b', $results[3]['find']);
    }

    public function testInsertWithRules()
    {
        $rules = array(
            array(
                'matches' => array(
                    array('type' => 'prefix', 'scope' => '!consonant')
                ),
                'replace' => 'ঋ'
            )
        );
        
        $this->trie->insert('rri', 'ৃ', $rules);
        
        $result = $this->trie->searchLongest('rri', 0);
        
        $this->assertNotNull($result);
        $this->assertEquals('rri', $result['find']);
        $this->assertEquals('ৃ', $result['replace']);
        $this->assertCount(1, $result['rules']);
    }

    public function testUnicodePatterns()
    {
        // Test with Bengali characters as patterns
        $this->trie->insert('আমি', 'ami');
        
        $this->assertTrue($this->trie->exists('আমি'));
        
        $result = $this->trie->searchLongest('আমি বাংলায়', 0);
        $this->assertNotNull($result);
        $this->assertEquals('আমি', $result['find']);
    }

    public function testEmptyTrie()
    {
        $result = $this->trie->searchLongest('hello', 0);
        $this->assertNull($result);
        
        $results = $this->trie->searchAll('hello', 0);
        $this->assertEmpty($results);
    }

    public function testGetRoot()
    {
        $root = $this->trie->getRoot();
        
        $this->assertInstanceOf(TrieNode::class, $root);
    }

    public function testOverlappingPatterns()
    {
        $this->trie->insert('ng', 'ং');
        $this->trie->insert('ngg', 'ঙ্গ');
        $this->trie->insert('ngk', 'ঙ্ক');
        
        $result = $this->trie->searchLongest('nggla', 0);
        $this->assertEquals('ngg', $result['find']);
        
        $result = $this->trie->searchLongest('ngkla', 0);
        $this->assertEquals('ngk', $result['find']);
        
        $result = $this->trie->searchLongest('ngala', 0);
        $this->assertEquals('ng', $result['find']);
    }
}
