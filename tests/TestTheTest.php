<?php

use IrfanTOOR\Test;

interface SomeInterface {
    function f();
    function g();
}

class SomeClass implements SomeInterface
{
    function f()
    {
        return "f";
    }

    function g()
    {
        return "g";
    }
}

class ExtendedClass extends SomeClass
{
    function fg()
    {
        return $this->f() . $this->g();
    }

    function gf()
    {
        throw new Exception("Error Processing GF", 1);
    }
}

class TestTheTest extends Test
{
    protected $t = null;
    protected $c = 0;

    function __construct($options = [])
    {
        parent::__construct($options);
        $this->t = new Test;
    }

    function setup()
    {
        $this->c++;
    }

    function testConstruct()
    {
        $this->assertNotNull($this->t);
        $this->assertInstanceOf(IrfanTOOR\Test::class, $this->t);
        $this->t = null;
    }

    function testSetup()
    {
        $this->assertNull($this->t);
        $this->assertEquals(2, $this->c);
    }

    function testAssertTrue()
    {
        $this->assertTrue(true);
        $this->assertTrue(!false);
        $this->assertTrue(1 <= 1);
        $this->assertTrue(1 < 2);
        $this->assertTrue(1 >= 1);
        $this->assertTrue(1 > 0);
        $this->assertTrue(0 !== 1);
    }

    function testAssertFalse()
    {
        $this->assertFalse(false);
        $this->assertFalse(!true);
        $this->assertFalse(1 > 1);
        $this->assertFalse(1 >= 2);
        $this->assertFalse(1 < 1);
        $this->assertFalse(1 <= 0);
        $this->assertFalse(0 === 1);
    }

    function testAssertZero()
    {
        $this->assertZero(0);
        $this->assertZero(10 - 10);
    }

    function testAssertString()
    {
        $this->assertString('');
        $this->assertString(' ');
        $this->assertString('hello');

        $a = 'hello';
        $this->assertString($a);

        $e = new ExtendedClass();
        $this->assertString($e->f());
        $this->assertString($e->g());
        $this->assertString($e->fg());
    }

    function testAssertNotString()
    {
        $this->assertNotString(null);
        $this->assertNotString(0);
        $this->assertNotString(true);
        $this->assertNotString(false);
        $this->assertNotString([]);

        $c = new SomeClass();
        $this->assertNotString($c);
    }

    function testAssertEquals()
    {
        $a = 'hello';
        $this->assertEquals('hello', $a);

        $a = 6;
        $b = 6;
        $this->assertEquals($a, $b);
        $this->assertEquals('1234', 1234);

        $c = new SomeClass;
        $d = new SomeClass;
        $this->assertEquals($c, $d);

        $e = new ExtendedClass();
        $this->assertEquals("f", $e->f());
        $this->assertEquals("g", $e->g());
        $this->assertEquals("fg", $e->fg());
    }

    function testAssertNotEquals()
    {
        $a = 6;
        $b = 6;
        $b++;
        $this->assertNotEquals($a, $b);
        $this->assertNotEquals('12345', 1234);
    }

    function testAssertSame()
    {
        $a = 6;
        $b = 6;
        $this->assertSame($a, $b);

        $c = new SomeClass;
        $d = $c;
        $this->assertSame($c, $d);
    }

    function testAssertNotSame()
    {
        $b = 6;
        $this->assertNotSame('6', $b);
        $this->assertNotSame('1234', 1234);

        $c = new SomeClass;
        $d = new SomeClass;
        $this->assertNotSame($c, $d);
    }

    function testAssertObject()
    {
        $d = new ExtendedClass;
        $this->assertObject($d);
        $this->assertObject(new Exception());
        $this->assertObject($this);
    }

    function testAssertNotObject()
    {
        $this->assertNotObject(null);
        $this->assertNotObject(0);
        $this->assertNotObject(1);
        $this->assertNotObject('');
        $this->assertNotObject(' ');
        $this->assertNotObject(true);
        $this->assertNotObject(false);
        $this->assertNotObject([]);
    }

    function testAsserInstanceOf()
    {
        $d = new ExtendedClass;
        $this->assertInstanceOf(ExtendedClass::class, $d);
        $this->assertInstanceOf(SomeClass::class, $d);
    }

    function testAssertImplements()
    {
        $c = new ExtendedClass;
        $this->assertImplements(SomeInterface::class, $c);
    }

    function testException()
    {
        $f = function(){
            throw new Exception("Error Processing Request", 1);
        };

        $this->assertException($f);
        $this->assertException(new Exception);
        $this->assertException(function(){
            $e = new ExtendedClass;
            $e->gf();
        });
    }

    function testExceptionMessage()
    {
        $f = function(){
            throw new Exception("Error Processing Request", 1);
        };
        $this->assertExceptionMessage("Error Processing Request", $f);
        $this->assertExceptionMessage("Error Processing GF", function(){
            $e = new ExtendedClass;
            $e->gf();
        });
    }

    # this test must be skipped!
    function testMethodRaisesException()
    {
        $this->assertException();
    }
}
