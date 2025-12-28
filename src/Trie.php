<?php

namespace Eru\AvroPhonetic;

/**
 * Trie (Prefix Tree) implementation for fast pattern lookup
 * 
 * This provides O(m) lookup time where m is the length of the search string,
 * compared to O(n*m) for linear search through all patterns.
 */
class Trie
{
    /** @var TrieNode */
    private $root;

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    /**
     * Insert a pattern into the Trie
     *
     * @param string $find The pattern string to find
     * @param string $replace The replacement string
     * @param array $rules The rules for context-sensitive matching
     * @return void
     */
    public function insert($find, $replace, array $rules = array())
    {
        $node = $this->root;
        $len = mb_strlen($find);
        
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($find, $i, 1);
            
            if (!$node->hasChild($char)) {
                $node->addChild($char, new TrieNode());
            }
            
            $node = $node->getChild($char);
        }
        
        // Mark end of pattern and store pattern data
        $node->setEndOfPattern(true);
        $node->setPatternData(array(
            'find' => $find,
            'replace' => $replace,
            'rules' => $rules
        ));
    }

    /**
     * Search for the longest matching pattern at the given position
     *
     * @param string $text The text to search in
     * @param int $position The starting position
     * @return array|null Returns pattern data if found, null otherwise
     */
    public function searchLongest($text, $position = 0)
    {
        $node = $this->root;
        $len = mb_strlen($text);
        $lastMatch = null;
        
        for ($i = $position; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1);
            
            if (!$node->hasChild($char)) {
                break;
            }
            
            $node = $node->getChild($char);
            
            // If this node marks end of a pattern, remember it
            if ($node->isEndOfPattern()) {
                $lastMatch = $node->getPatternData();
            }
        }
        
        return $lastMatch;
    }

    /**
     * Search for all matching patterns at the given position
     * Returns matches from longest to shortest
     *
     * @param string $text The text to search in
     * @param int $position The starting position
     * @return array Array of pattern data, longest first
     */
    public function searchAll($text, $position = 0)
    {
        $node = $this->root;
        $len = mb_strlen($text);
        $matches = array();
        
        for ($i = $position; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1);
            
            if (!$node->hasChild($char)) {
                break;
            }
            
            $node = $node->getChild($char);
            
            if ($node->isEndOfPattern()) {
                $matches[] = $node->getPatternData();
            }
        }
        
        // Return longest matches first
        return array_reverse($matches);
    }

    /**
     * Check if a pattern exists in the Trie
     *
     * @param string $find The pattern to check
     * @return bool
     */
    public function exists($find)
    {
        $node = $this->root;
        $len = mb_strlen($find);
        
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($find, $i, 1);
            
            if (!$node->hasChild($char)) {
                return false;
            }
            
            $node = $node->getChild($char);
        }
        
        return $node->isEndOfPattern();
    }

    /**
     * Get the root node (for advanced traversal)
     *
     * @return TrieNode
     */
    public function getRoot()
    {
        return $this->root;
    }
}
