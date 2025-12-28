<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\Avro;
use Eru\AvroPhonetic\PhoneticParser;

class AvroTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset singleton for each test
        $reflection = new \ReflectionClass(Avro::class);
        
        $instanceProp = $reflection->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null, null);
        
        $grammarProp = $reflection->getProperty('grammar');
        $grammarProp->setAccessible(true);
        $grammarProp->setValue(null, null);
    }

    // ==================== Constructor Tests ====================

    public function testConstructorWithoutGrammar()
    {
        $avro = new Avro();
        $this->assertInstanceOf(Avro::class, $avro);
    }

    public function testConstructorWithCustomGrammar()
    {
        $grammar = $this->getMinimalGrammar();
        $avro = new Avro($grammar);
        
        $this->assertEquals('ক', $avro->parse('k'));
    }

    // ==================== Singleton Tests ====================

    public function testGetInstance()
    {
        $instance1 = Avro::getInstance();
        $instance2 = Avro::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    // ==================== Parse/Convert Tests ====================

    public function testParse()
    {
        $avro = new Avro();
        $result = $avro->parse('ami');
        
        $this->assertEquals('আমি', $result);
    }

    public function testConvert()
    {
        $avro = new Avro();
        $result = $avro->convert('ami');
        
        $this->assertEquals('আমি', $result);
    }

    public function testStaticTo()
    {
        $result = Avro::to('ami');
        
        $this->assertEquals('আমি', $result);
    }

    // ==================== Factory Methods ====================

    public function testWithGrammar()
    {
        $grammar = $this->getMinimalGrammar();
        $avro = Avro::withGrammar($grammar);
        
        $this->assertInstanceOf(Avro::class, $avro);
        $this->assertEquals('ক', $avro->parse('k'));
    }

    public function testFromGrammarFile()
    {
        $grammarPath = __DIR__ . '/../resources/grammar.json';
        $avro = Avro::fromGrammarFile($grammarPath);
        
        $this->assertInstanceOf(Avro::class, $avro);
        $this->assertEquals('আমি', $avro->parse('ami'));
    }

    public function testFromGrammarFileThrowsExceptionForMissingFile()
    {
        $this->expectException(\RuntimeException::class);
        
        Avro::fromGrammarFile('/non/existent/path.json');
    }

    // ==================== Parser Access ====================

    public function testGetParser()
    {
        $avro = new Avro();
        $parser = $avro->getParser();
        
        $this->assertInstanceOf(PhoneticParser::class, $parser);
    }

    // ==================== Grammar Path ====================

    public function testGetGrammarPath()
    {
        $path = Avro::getGrammarPath();
        
        $this->assertStringEndsWith('grammar.json', $path);
        $this->assertFileExists($path);
    }

    // ==================== Integration Tests ====================

    public function testMultipleConversions()
    {
        $avro = new Avro();
        
        $this->assertEquals('আমি', $avro->parse('ami'));
        $this->assertEquals('তুমি', $avro->parse('tumi'));
        $this->assertEquals('বাংলা', $avro->parse('bangla'));
    }

    public function testStaticAndInstanceConsistency()
    {
        $avro = new Avro();
        
        $this->assertEquals(
            $avro->parse('ami bangla'),
            Avro::to('ami bangla')
        );
    }

    // ==================== Helper ====================

    private function getMinimalGrammar()
    {
        return array(
            'patterns' => array(
                array('find' => 'k', 'replace' => 'ক', 'rules' => array()),
                array('find' => 'a', 'replace' => 'আ', 'rules' => array()),
            ),
            'vowel' => 'aeiou',
            'consonant' => 'bcdfghjklmnpqrstvwxyz',
            'number' => '1234567890',
            'casesensitive' => 'oiudgjnrstyz',
        );
    }
}
