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
    const HELLO = 'hello';

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
        $this->assertObject(new Test);
        $this->assertInstanceOf(IrfanTOOR\Test::class, $this->t);
        $this->t = null;
    }

    function testSetup()
    {
        $this->assertNull($this->t);
        $this->assertEquals(2, $this->c);
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

    function testAssertArray()
    {
        $this->assertArray([]);
        $this->assertArray(['hello', 'world']);
        $this->assertArray(['h' => 'hello', 'w' => 'world']);
        $this->assertArray([0, null]);
        $this->assertArray(explode(',', 'a,b,c'));
        $this->assertArray($_SERVER);
    }

    function testAssertNotArray()
    {
        $this->assertNotarray(true);
        $this->assertNotarray(false);
        $this->assertNotarray(!false);
        $this->assertNotarray(!true);
        $this->assertNotArray(null);
        $this->assertNotArray(0);
        $this->assertNotArray(1.1);
        $this->assertNotArray('hello');
        $this->assertNotArray(new Exception);    
    }

    function testAssertArrayHasKey()
    {
        $this->assertArrayHasKey('0', ['zero']);
        $this->assertArrayHasKey('0', [1]);
        $this->assertArrayHasKey('10', [9=>'a', 'b']);
        $this->assertArrayHasKey('a', ['zero', 'a' => 'apple']);
        $this->assertArrayHasKey('argv', $_SERVER);
    }  

    function testAssertBool()
    {
        $this->assertBool(true);
        $this->assertBool(false);
        $this->assertBool(!false);
        $this->assertBool(!true);
    }

    function testAssertNotBool()
    {
        $this->assertNotBool(null);
        $this->assertNotBool(0);
        $this->assertNotBool(1.1);
        $this->assertNotBool('hello');
        $this->assertNotBool([]);
        $this->assertNotBool(new Exception);
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

    function testAssertNull()
    {
        $this->assertNull(null);
        $this->assertNull(NULL);

        $a = null;
        $this->assertNull($a);
        $this->assertNull($b); # undefined variable
    }

    function testAssertNotNull()
    {
        $this->assertNotNull(0);
        $this->assertNotNull('');
        $this->assertNotNull(false);
        $this->assertNotNull([]);
        $this->assertNotNull([null]);
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

    function testAssertEmpty()
    {
        $this->assertEmpty('');
        $this->assertEmpty([]);
        $this->assertEmpty(null);
    }

    function testAssertNotEmpty()
    {
        $this->assertNotEmpty(' ');
        $this->assertNotEmpty([0]);
        $this->assertNotEmpty(true);
        $this->assertNotEmpty(false);
        $this->assertNotEmpty(1);

        $c = new SomeClass();
        $this->assertNotEmpty($c);
    }



    function testAssertDouble()
    {
        $this->assertDouble(0.);
        $this->assertDouble(0.0);
        $this->assertDouble(.1);
        $this->assertDouble(1.);
        $this->assertDouble(0 * 1e1);
        $this->assertDouble(0.1 * 1);
        $this->assertDouble(-0.1);
        $this->assertDouble(1-1.);
    }

    function testAssertNotDouble()
    {
        $this->assertNotDouble(0);
        $this->assertNotDouble(1);
        $this->assertNotDouble(100000000);
        $this->assertNotDouble(1-1);
    }

    function testAssertFloat()
    {
        $this->assertFloat(0.);
        $this->assertFloat(0.0);
        $this->assertFloat(.1);
        $this->assertFloat(1.);
        $this->assertFloat(0 * 1e1);
        $this->assertFloat(0.1 * 1);
        $this->assertFloat(-0.1);
        $this->assertFloat(1-1.);
    }

    function testAssertNotFloat()
    {
        $this->assertNotFloat(0);
        $this->assertNotFloat(1);
        $this->assertNotFloat(100000000);
        $this->assertNotFloat(1-1);
    }

    function testAssertInt()
    {
        $this->assertInt(0);
        $this->assertInt(1);
        $this->assertInt(10 * 10);
        $this->assertInt(-12345);
        $this->assertInt(-0);
        $this->assertInt((int) 123.456);
        $this->assertInt((int) '1234');
    }

    function testAssertNotInt()
    {
        $this->assertNotInt(0.);
        $this->assertNotInt(1.);
        $this->assertNotInt(1e2);
        $this->assertNotInt(1-0.);
        $this->assertNotInt((float) 1234);
        $this->assertNotInt((float) '1234');
        $this->assertNotInt('1234');
        $this->assertNotInt(null);
        $this->assertNotInt('');
    }

    function testAssertInteger()
    {
        $this->assertInteger(0);
        $this->assertInteger(1);
        $this->assertInteger(10 * 10);
        $this->assertInteger(-12345);
        $this->assertInteger(-0);
        $this->assertInteger((integer) 123.456);
        $this->assertInteger((integer) '1234');
    }

    function testAssertNotInteger()
    {
        $this->assertNotInteger(0.);
        $this->assertNotInteger(1.);
        $this->assertNotInteger(1e2);
        $this->assertNotInteger(1-0.);
        $this->assertNotInteger((float) 1234);
        $this->assertNotInteger((float) '1234');
        $this->assertNotInteger('1234');
        $this->assertNotInteger(null);
        $this->assertNotInteger('');
    }

    function testAssertLong()
    {
        $this->assertLong(0);
        $this->assertLong(1);
        $this->assertLong(10 * 10);
        $this->assertLong(-12345);
        $this->assertLong(-0);
        $this->assertLong((int) 123.456);
        $this->assertLong((int) '1234');
    }

    function testAssertNotLong()
    {
        $this->assertNotLong(0.);
        $this->assertNotLong(1.);
        $this->assertNotLong(1e2);
        $this->assertNotLong(1-0.);
        $this->assertNotLong((float) 1234);
        $this->assertNotLong((float) '1234');
        $this->assertNotLong('1234');
        $this->assertNotLong(null);
        $this->assertNotLong('');
    }

    function testAssertNumeric()
    {
        $this->assertNumeric(0);
        $this->assertNumeric(0.);
        $this->assertNumeric(1);
        $this->assertNumeric(1.2);
        $this->assertNumeric(10 * 10);
        $this->assertNumeric(10.0 * 10.0);
        $this->assertNumeric(1e2);
        $this->assertNumeric(-12345);
        $this->assertNumeric(-0);
        $this->assertNumeric((int) 123.456);
        $this->assertNumeric((int) '1234');
        $this->assertNumeric((float) 123.456);
    }

    function testAssertNotNumeric()
    {
        $this->assertNotNumeric(null);
        $this->assertNotNumeric('');
        $this->assertNotNumeric(true);
        $this->assertNotNumeric(false);
        $this->assertNotNumeric([]);
        $this->assertNotNumeric(new Exception);
    }

    function testAssertReal()
    {
        $this->assertReal(0.);
        $this->assertReal(0.0);
        $this->assertReal(.1);
        $this->assertReal(1.);
        $this->assertReal(0 * 1e1);
        $this->assertReal(0.1 * 1);
        $this->assertReal(-0.1);
        $this->assertReal(1-1.);
    }

    function testAssertNotReal()
    {
        $this->assertNotReal(0);
        $this->assertNotReal(1);
        $this->assertNotReal(100000000);
        $this->assertNotReal(1-1);
        $this->assertNotReal('');
        $this->assertNotReal(null);
        $this->assertNotReal(true);
        $this->assertNotReal(false);
        $this->assertNotReal([]);
    }

    function testAssertZero()
    {
        $this->assertZero(0);
        $this->assertZero(0.);
        $this->assertZero(0.0);
        $this->assertZero(10 - 10);
        $this->assertZero('12' - '12');
        $this->assertZero('' - '');
        $this->assertZero(0 + '');
        $this->assertZero(0 + 0);
        $a = 0;
        $this->assertZero($a);
    }

    function testAssertNotZero()
    {
        $this->assertNotZero(null);
        $this->assertNotZero(1);
        $this->assertNotZero(false);
        $this->assertNotZero('0');
        $this->assertNotZero('');
        $this->assertNotZero([]);
        
        $a = 1/1e100;
        $this->assertNotZero($a);
    }

    function testAssertCallable()
    {
        $this->assertCallable(function(){});

        $s = new SomeClass;
        $this->assertCallable([$s, 'f']);
        $this->assertCallable([$s, 'g']);
        $this->assertCallable([$this, 'run']);
        $this->assertCallable([$this, 'testAssertCallable']);
    }

    function testAssertNotCallable()
    {
        $e = new ExtendedClass;
        $this->assertNotCallable([$e, 'ff']);
        $this->assertNotCallable(2);
        $this->assertNotCallable([$this, null]);
        $this->assertNotCallable('hello');
        $this->assertNotcallable(true);
        $this->assertNotcallable(false);
        $this->assertNotcallable(!false);
        $this->assertNotcallable(!true);
        $this->assertNotcallable(null);
        $this->assertNotcallable(0);
        $this->assertNotcallable(1.1);
        $this->assertNotcallable('hello');
        $this->assertNotcallable(new Exception);          
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

    function testAssertResource()
    {
        $fp = fopen(__FILE__, 'r');
        $this->assertResource($fp);
        fclose($fp);

        $this->assertResource(fopen('/dev/null', 'w'));
        $this->assertResource(STDIN);
        $this->assertResource(STDOUT);
    }

    function testAssertNotResource()
    {
        $fp = fopen(__FILE__, 'r');
        fclose($fp);
        $this->assertNotResource($fp);

        $this->assertNotResource(null);
        $this->assertNotResource(__FILE__);
        $this->assertNotResource(0);
        $this->assertNotResource('');
        $this->assertNotResource([]);
        $this->assertNotResource(true);
        $this->assertNotResource(false);
    }

    function testAssertScalar()
    {
        $this->assertScalar(self::HELLO);
        $this->assertScalar(WORLD);
        $this->assertScalar(0);
        $this->assertScalar(1.0);
        $this->assertScalar('');
        $this->assertScalar('hello');
        $this->assertScalar(true);
        $this->assertScalar(false);
    }

    function testAssertNotScalar()
    {
        $this->assertNotScalar(null);
        $this->assertNotScalar([]);
        $this->assertNotScalar(new Exception);
        $this->assertNotScalar(function(){});
        $this->assertNotScalar([$this, 'testAssertNotScalar']);
        $this->assertNotScalar(fopen(__FILE__, 'r'));
        $this->assertNotScalar(STDIN);
        $this->assertNotScalar(STDOUT);
    }

    function testAssertDir()
    {
        $this->assertDir('/');
        $this->assertDir(__DIR__);
        $this->assertDir(__DIR__ . '/');
        $this->assertDir(__DIR__ . '/.');
        $this->assertDir(__DIR__ . '/..');
        $this->assertDir(dirname(__FILE__));
    }

    function testAssertNotDir()
    {
        $this->assertNotDir([]);
        $this->assertNotDir(2);
        $this->assertNotdir(true);
        $this->assertNotdir(false);
        $this->assertNotdir(!false);
        $this->assertNotdir(!true);
        $this->assertNotdir(null);
        $this->assertNotdir(0);
        $this->assertNotdir(1.1);
        $this->assertNotdir('hello');
        $this->assertNotdir(new Exception);          
    }

    function testAssertExecutable()
    {
        $this->assertExecutable(dirname(__DIR__) . '/test');
    }

    function testAssertNotExecutable()
    {
        $this->assertNotExecutable(__FILE__);
    }

    function testAssertFile()
    {
        $this->assertFile(__FILE__);
    }

    function testAssertNotFile()
    {
        $this->assertNotFile(__DIR__);
        $this->assertNotFile('/');
    }

    function testAssertLink()
    {
        symlink(__FILE__, 'link');
        $this->assertLink('link');
        unlink('link');
    }

    function testAssertNotLink()
    {
        $this->assertNotLink(__FILE__);
        $this->assertNotLink(__DIR__);
        $this->assertNotLink(__DIR__ . '/.');
        $this->assertNotLink(__DIR__ . '/..');
    }

    function testAssertReadable()
    {
        symlink(__FILE__, 'link');
        file_put_contents('file', '');
        
        $this->assertReadable(__FILE__);
        $this->assertReadable(__DIR__);
        $this->assertReadable('link');
        $this->assertReadable('file');

        unlink('link');
        unlink('file');
    }

    function testAssertNotReadable()
    {
        file_put_contents('file', '');
        chmod('file', 0x000);
        
        $this->assertNotReadable('/unknowndir');
        $this->assertNotReadable('unknownfile');
        $this->assertNotReadable('file');

        unlink('file');
    }

    function testAssertWritble()
    {
        file_put_contents('file', '');

        $this->assertWritable(__FILE__);
        $this->assertWritable(__DIR__);
        $this->assertWritable('file');

        unlink('file');
    }

    function testAssertNotWritable()
    {
        file_put_contents('file', '');
        chmod('file', 0x000);

        $this->assertNotWritable('/unknowndir');
        $this->assertNotWritable('unknownfile');
        $this->assertNotWritable('file');

        unlink('file');
    }

    function testAssertWriteble()
    {
        file_put_contents('file', '');

        $this->assertWriteable(__FILE__);
        $this->assertWriteable(__DIR__);
        $this->assertWriteable('file');

        unlink('file');
    }

    function testAssertNotWriteable()
    {
        file_put_contents('file', '');
        chmod('file', 0x000);

        $this->assertNotWriteable('/unknowndir');
        $this->assertNotWritable('unknownfile');
        $this->assertNotWriteable('file');

        unlink('file');
    }

    function testAssertUploadedFile()
    {
        # $this->assertUploadedFile( ... );
    }

    function testAssertNotUploadedFile()
    {
        file_put_contents('file', '');

        $this->assertNotUploadedFile(__FILE__);
        $this->assertNotUploadedFile(__DIR__);
        $this->assertNotUploadedFile('file');

        unlink('file');
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
        $this->assertException(
            function(){
                $e = new ExtendedClass;
                $e->gf();
            },
            Exception::class,
            'Error Processing GF'
        );
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
}
