<?php

namespace Eru\AvroPhonetic;

/**
 * TrieNode represents a single node in the Trie structure
 */
class TrieNode
{
    /** @var array Associative array of child nodes */
    private $children;
    
    /** @var bool Whether this node marks the end of a pattern */
    private $isEndOfPattern;
    
    /** @var array|null Pattern data stored at this node */
    private $patternData;

    public function __construct()
    {
        $this->children = array();
        $this->isEndOfPattern = false;
        $this->patternData = null;
    }

    /**
     * Check if this node has a child for the given character
     *
     * @param string $char
     * @return bool
     */
    public function hasChild($char)
    {
        return isset($this->children[$char]);
    }

    /**
     * Get the child node for the given character
     *
     * @param string $char
     * @return TrieNode|null
     */
    public function getChild($char)
    {
        return isset($this->children[$char]) ? $this->children[$char] : null;
    }

    /**
     * Add a child node for the given character
     *
     * @param string $char
     * @param TrieNode $node
     * @return void
     */
    public function addChild($char, TrieNode $node)
    {
        $this->children[$char] = $node;
    }

    /**
     * Get all children
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Check if this node marks the end of a pattern
     *
     * @return bool
     */
    public function isEndOfPattern()
    {
        return $this->isEndOfPattern;
    }

    /**
     * Set whether this node marks the end of a pattern
     *
     * @param bool $value
     * @return void
     */
    public function setEndOfPattern($value)
    {
        $this->isEndOfPattern = (bool) $value;
    }

    /**
     * Get the pattern data stored at this node
     *
     * @return array|null
     */
    public function getPatternData()
    {
        return $this->patternData;
    }

    /**
     * Set the pattern data for this node
     *
     * @param array $data
     * @return void
     */
    public function setPatternData(array $data)
    {
        $this->patternData = $data;
    }
}
