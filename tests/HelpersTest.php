<?php

namespace Eru\AvroPhonetic\Tests;

use PHPUnit\Framework\TestCase;
use Eru\AvroPhonetic\Avro;

class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset Avro singleton
        $reflection = new \ReflectionClass(Avro::class);
        
        $instanceProp = $reflection->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null, null);
    }

    // ==================== avro() Helper Tests ====================

    public function testAvroHelperWithText()
    {
        $result = avro('ami');
        
        $this->assertEquals('আমি', $result);
    }

    public function testAvroHelperWithoutText()
    {
        $result = avro();
        
        $this->assertInstanceOf(Avro::class, $result);
    }

    public function testAvroHelperWithNullText()
    {
        $result = avro(null);
        
        $this->assertInstanceOf(Avro::class, $result);
    }

    public function testAvroHelperInstanceCanParse()
    {
        $avro = avro();
        $result = $avro->parse('ami');
        
        $this->assertEquals('আমি', $result);
    }

    // ==================== bangla() Helper Tests ====================

    public function testBanglaHelper()
    {
        $result = bangla('ami');
        
        $this->assertEquals('আমি', $result);
    }

    public function testBanglaHelperWithSentence()
    {
        $result = bangla('ami banglay gan gai');
        
        $this->assertStringContainsString('আমি', $result);
        $this->assertStringContainsString('গান', $result);
    }

    public function testBanglaHelperWithEmptyString()
    {
        $result = bangla('');
        
        $this->assertEquals('', $result);
    }

    // ==================== Consistency Tests ====================

    public function testHelpersReturnSameResult()
    {
        $input = 'ami bangla';
        
        $this->assertEquals(
            avro($input),
            bangla($input)
        );
    }

    public function testHelperMatchesClassMethod()
    {
        $input = 'ami bangla';
        
        $this->assertEquals(
            Avro::to($input),
            avro($input)
        );
        
        $this->assertEquals(
            Avro::to($input),
            bangla($input)
        );
    }
}
