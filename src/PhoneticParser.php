<?php

namespace Eru\AvroPhonetic;

/**
 * Avro Phonetic Parser for PHP
 * 
 * Ported from pyAvroPhonetic Python library
 * Original Copyright (C) 2013 Kaustav Das Modak
 */
class PhoneticParser
{
    /** @var array */
    private $patterns;
    
    /** @var array */
    private $nonRulePatterns;
    
    /** @var array */
    private $rulePatterns;
    
    /** @var string */
    private $vowel;
    
    /** @var string */
    private $consonant;
    
    /** @var string */
    private $numbers;
    
    /** @var string */
    private $caseSensitive;

    /**
     * @param array $rule
     */
    public function __construct(array $rule)
    {
        $this->patterns = $rule['patterns'];
        $this->vowel = $rule['vowel'];
        $this->consonant = $rule['consonant'];
        $this->numbers = $rule['number'];
        $this->caseSensitive = $rule['casesensitive'];
        
        // Separate patterns into rule and non-rule patterns
        $this->nonRulePatterns = array_filter($this->patterns, function($p) {
            return empty($p['rules']);
        });
        $this->rulePatterns = array_filter($this->patterns, function($p) {
            return !empty($p['rules']);
        });
    }

    /**
     * Parses input text, matches and replaces using avro dictionary
     * 
     * @param string $input The text to parse
     * @return string The converted Bengali text
     */
    public function parse($input)
    {
        // Sanitize text case to meet phonetic comparison standards
        $fixedText = $this->fixStringCase($input);
        $output = [];
        $curEnd = 0;
        $len = mb_strlen($fixedText);

        // Iterate through input text
        for ($cur = 0; $cur < $len; $cur++) {
            $char = mb_substr($fixedText, $cur, 1);
            
            // Check if cursor is at a position that has already been processed
            if ($cur >= $curEnd) {
                // Try looking in non-rule patterns first
                $match = $this->matchNonRulePatterns($fixedText, $cur);
                
                if ($match['matched']) {
                    $output[] = $match['replaced'];
                    $curEnd = $cur + mb_strlen($match['found']);
                } else {
                    // Try rule patterns
                    $match = $this->matchRulePatterns($fixedText, $cur);
                    
                    if ($match['matched']) {
                        $curEnd = $cur + mb_strlen($match['found']);
                        
                        // Process rules
                        $replaced = $this->processRules(
                            $match['rules'],
                            $fixedText,
                            $cur,
                            $curEnd
                        );
                        
                        // If any rules match, use rule replacement, else use default
                        if ($replaced !== null) {
                            $output[] = $replaced;
                        } else {
                            $output[] = $match['replaced'];
                        }
                    }
                }
                
                // If none matched, append current character
                if (!$match['matched']) {
                    $curEnd = $cur + 1;
                    $output[] = $char;
                }
            }
        }

        return implode('', $output);
    }

    /**
     * Alias for parse() to maintain backward compatibility
     * 
     * @param string $input
     * @return string
     */
    public function convert($input)
    {
        return $this->parse($input);
    }

    /**
     * Matches given text at cursor position with non-rule patterns
     * 
     * @param string $fixedText
     * @param int $cur
     * @return array
     */
    private function matchNonRulePatterns($fixedText, $cur)
    {
        $patterns = $this->exactFindInPattern($fixedText, $cur, $this->nonRulePatterns);
        
        if (count($patterns) > 0) {
            return [
                'matched' => true,
                'found' => $patterns[0]['find'],
                'replaced' => $patterns[0]['replace']
            ];
        }
        
        return [
            'matched' => false,
            'found' => null,
            'replaced' => mb_substr($fixedText, $cur, 1)
        ];
    }

    /**
     * Matches given text at cursor position with rule patterns
     * 
     * @param string $fixedText
     * @param int $cur
     * @return array
     */
    private function matchRulePatterns($fixedText, $cur)
    {
        $patterns = $this->exactFindInPattern($fixedText, $cur, $this->rulePatterns);
        
        if (count($patterns) > 0) {
            return [
                'matched' => true,
                'found' => $patterns[0]['find'],
                'replaced' => $patterns[0]['replace'],
                'rules' => $patterns[0]['rules']
            ];
        }
        
        return [
            'matched' => false,
            'found' => null,
            'replaced' => mb_substr($fixedText, $cur, 1),
            'rules' => null
        ];
    }

    /**
     * Returns pattern items that match given text at cursor position
     * 
     * @param string $fixedText
     * @param int $cur
     * @param array $patterns
     * @return array
     */
    private function exactFindInPattern($fixedText, $cur, array $patterns)
    {
        $len = mb_strlen($fixedText);
        $matched = [];
        
        foreach ($patterns as $pattern) {
            $findLen = mb_strlen($pattern['find']);
            if ($cur + $findLen <= $len) {
                $chunk = mb_substr($fixedText, $cur, $findLen);
                if ($pattern['find'] === $chunk) {
                    $matched[] = $pattern;
                }
            }
        }
        
        return $matched;
    }

    /**
     * Process rules matched in pattern and returns suitable replacement
     * 
     * @param array $rules
     * @param string $fixedText
     * @param int $cur
     * @param int $curEnd
     * @return string|null
     */
    private function processRules(array $rules, $fixedText, $cur, $curEnd)
    {
        $replaced = '';
        $ruleMatched = false;
        
        foreach ($rules as $rule) {
            $matched = false;
            
            // Iterate through matches
            foreach ($rule['matches'] as $match) {
                $matched = $this->processMatch($match, $fixedText, $cur, $curEnd);
                
                // Break if we don't have a match
                if (!$matched) {
                    break;
                }
            }
            
            // If all matches in this rule matched, use this rule's replacement
            if ($matched) {
                $replaced = $rule['replace'];
                $ruleMatched = true;
                break;
            }
        }
        
        return $ruleMatched ? $replaced : null;
    }

    /**
     * Processes a single match in rules
     * 
     * @param array $match
     * @param string $fixedText
     * @param int $cur
     * @param int $curEnd
     * @return bool
     */
    private function processMatch(array $match, $fixedText, $cur, $curEnd)
    {
        $replace = true;
        $len = mb_strlen($fixedText);
        
        // Set check cursor depending on match type
        if ($match['type'] === 'prefix') {
            $chk = $cur - 1;
        } else {
            // suffix
            $chk = $curEnd;
        }
        
        // Set scope based on whether scope is negative
        $scope = $match['scope'];
        $negative = false;
        
        if (strpos($scope, '!') === 0) {
            $scope = substr($scope, 1);
            $negative = true;
        }
        
        // Matching logic
        switch ($scope) {
            case 'punctuation':
                $condition = ($chk < 0 && $match['type'] === 'prefix') ||
                            ($chk >= $len && $match['type'] === 'suffix') ||
                            ($chk >= 0 && $chk < $len && $this->isPunctuation(mb_substr($fixedText, $chk, 1)));
                
                if (!($condition xor $negative)) {
                    $replace = false;
                }
                break;
                
            case 'vowel':
                $condition = (($chk >= 0 && $match['type'] === 'prefix') ||
                             ($chk < $len && $match['type'] === 'suffix')) &&
                            ($chk >= 0 && $chk < $len && $this->isVowel(mb_substr($fixedText, $chk, 1)));
                
                if (!($condition xor $negative)) {
                    $replace = false;
                }
                break;
                
            case 'consonant':
                $condition = (($chk >= 0 && $match['type'] === 'prefix') ||
                             ($chk < $len && $match['type'] === 'suffix')) &&
                            ($chk >= 0 && $chk < $len && $this->isConsonant(mb_substr($fixedText, $chk, 1)));
                
                if (!($condition xor $negative)) {
                    $replace = false;
                }
                break;
                
            case 'exact':
                $value = $match['value'] ?? '';
                $valueLen = mb_strlen($value);
                
                if ($match['type'] === 'prefix') {
                    $exactStart = $cur - $valueLen;
                    $exactEnd = $cur;
                } else {
                    // suffix
                    $exactStart = $curEnd;
                    $exactEnd = $curEnd + $valueLen;
                }
                
                if (!$this->isExact($value, $fixedText, $exactStart, $exactEnd, $negative)) {
                    $replace = false;
                }
                break;
        }
        
        return $replace;
    }

    /**
     * Fix string case - preserve case for case-sensitive characters, lowercase others
     * 
     * @param string $string
     * @return string
     */
    private function fixStringCase($string)
    {
        $result = '';
        $len = mb_strlen($string);
        
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($string, $i, 1);
            // If character is in case-sensitive list, keep it as is
            // Otherwise convert to lowercase
            if (strpos($this->caseSensitive, $char) !== false || 
                strpos($this->caseSensitive, strtolower($char)) !== false) {
                $result .= $char;
            } else {
                $result .= mb_strtolower($char);
            }
        }
        
        return $result;
    }

    /**
     * Check if character is a punctuation (not vowel, not consonant, not number)
     * 
     * @param string $char
     * @return bool
     */
    private function isPunctuation($char)
    {
        return !$this->isVowel($char) && 
               !$this->isConsonant($char) && 
               strpos($this->numbers, $char) === false;
    }

    /**
     * Check if character is a vowel
     * 
     * @param string $char
     * @return bool
     */
    private function isVowel($char)
    {
        $lower = strtolower($char);
        return strpos('aeiou', $lower) !== false;
    }

    /**
     * Check if character is a consonant
     * 
     * @param string $char
     * @return bool
     */
    private function isConsonant($char)
    {
        $lower = strtolower($char);
        return strpos($this->consonant, $lower) !== false;
    }

    /**
     * Check for exact match
     * 
     * @param string $value
     * @param string $fixedText
     * @param int $start
     * @param int $end
     * @param bool $negative
     * @return bool
     */
    private function isExact($value, $fixedText, $start, $end, $negative)
    {
        $len = mb_strlen($fixedText);
        
        // Boundary checks
        if ($start < 0 || $end > $len) {
            return $negative;
        }
        
        $chunk = mb_substr($fixedText, $start, $end - $start);
        $matched = ($chunk === $value);
        
        return $negative ? !$matched : $matched;
    }
}