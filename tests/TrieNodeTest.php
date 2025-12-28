<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\TrieNode;

class TrieNodeTest extends TestCase
{
    /** @var TrieNode */
    private $node;

    protected function setUp(): void
    {
        $this->node = new TrieNode();
    }

    public function testInitialState()
    {
        $this->assertFalse($this->node->isEndOfPattern());
        $this->assertNull($this->node->getPatternData());
        $this->assertEmpty($this->node->getChildren());
    }

    public function testAddAndGetChild()
    {
        $childNode = new TrieNode();
        $this->node->addChild('a', $childNode);
        
        $this->assertTrue($this->node->hasChild('a'));
        $this->assertSame($childNode, $this->node->getChild('a'));
    }

    public function testHasChildReturnsFalseForMissing()
    {
        $this->assertFalse($this->node->hasChild('x'));
        $this->assertNull($this->node->getChild('x'));
    }

    public function testSetEndOfPattern()
    {
        $this->node->setEndOfPattern(true);
        $this->assertTrue($this->node->isEndOfPattern());
        
        $this->node->setEndOfPattern(false);
        $this->assertFalse($this->node->isEndOfPattern());
    }

    public function testSetPatternData()
    {
        $data = array(
            'find' => 'test',
            'replace' => 'টেস্ট',
            'rules' => array()
        );
        
        $this->node->setPatternData($data);
        
        $this->assertEquals($data, $this->node->getPatternData());
    }

    public function testGetChildren()
    {
        $child1 = new TrieNode();
        $child2 = new TrieNode();
        
        $this->node->addChild('a', $child1);
        $this->node->addChild('b', $child2);
        
        $children = $this->node->getChildren();
        
        $this->assertCount(2, $children);
        $this->assertArrayHasKey('a', $children);
        $this->assertArrayHasKey('b', $children);
    }

    public function testUnicodeChildKey()
    {
        $childNode = new TrieNode();
        $this->node->addChild('আ', $childNode);
        
        $this->assertTrue($this->node->hasChild('আ'));
        $this->assertSame($childNode, $this->node->getChild('আ'));
    }
}
