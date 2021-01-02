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

class MyClassTest extends Test
{
    function setup()
    {
        # anything you want to do before any testFunction is called
    }

    function testExamples()
    {
        $some = $class = new SomeClass();
        $somemore = new SomeClass();
        $int = 0;
        $float = 1.0;
        symlink(__FILE__, 'link');

        # equql
        $this->assertEquals($some, $somemore);
        $this->assertNotEquals($some, 'Some');

        # same
        $this->assertSame($some, $class);
        $this->assertNotSame($some, $somemore);
        
        # array
        $this->assertArray([]);
        $this->assertNotArray("I'm not an array");
        $this->assertArrayHasKey(['zero', 'a' => 'apple'], 'a');

        # bool
        $this->assertBool(false);
        $this->assertFalse(1 > 1);
        $this->assertTrue(0 == 0);

        # null
        $this->assertNull(null);
        $this->assertNotNull($class);

        # string
        $this->assertString('hello');
        $this->assertNotString($class);
        $this->assertEmpty([]);
        $this->assertNotEmpty(false);

        # numerics
        $this->assertDouble($float);
        $this->assertNotDouble($int);
        $this->assertFloat($float);
        $this->assertNotFloat($int);
        $this->assertInt($int);
        $this->assertNotInt($float);
        $this->assertInteger($int);
        $this->assertNotInteger($float);
        $this->assertLong($int);
        $this->assertNotLong($float);
        $this->assertNumeric($int);
        $this->assertNotNumeric('');
        $this->assertReal($float);
        $this->assertNotReal($int);
        $this->assertScalar('');
        $this->assertNotScalar($class);
        $this->assertZero(0);
        $this->assertNotZero(0.0); # fails

        # callable, objects, resources
        $this->assertCallable(function(){});
        $this->assertCallable([$this, 'setup']);
        $this->assertNotCallable([$this, 'functionNotDefined']);
        $this->assertObject($class);
        $this->assertNotObject([]);
        $this->assertResource(STDIN);
        $this->assertNotResource($class);

        # class instance and implementation
        $this->assertInstanceOf(SomeClass::class, $class);
        $this->assertImplements(SomeInterface::class, $class);

        # Assert Exception only
        $this->assertException(
            function () {
                throw new Exception("Exception Message", 1);
            }
        );

        # test will fail here, function is not defined any more ...
        $this->assertExceptionMessage("Error Message", function() {
            throw new Exception("Error Message", 1);
        });

        # Assert Exception and the Message
        $this->assertException(
            function () {
                throw new Exception("Exception Message", 1);
            },
            Exception::class,
            'Exception Message'
        );

        # filesystem
        $this->assertDir(__DIR__);
        $this->assertNotDir(__FILE__);
        $this->assertExecutable('/bin/ls');
        $this->assertNotExecutable(__FILE__);
        $this->assertFile(__FILE__);
        $this->assertNotFile(__DIR__);
        $this->assertLink('link'); # this will fail
        $this->assertNotLink('..');
        $this->assertReadable(__FILE__);
        $this->assertNotReadable('unknown');
        $this->assertWritable(__FILE__);
        $this->assertNotWritable('/etc/passwd');
        $this->assertWriteable(__FILE__);
        $this->assertNotWriteable('unknown');

        unlink('link');

        $this->assertEquals(['hello'], []);
        $this->assertEquals(
            'Too few arguments to function IrfanTOOR\Test::assertEquals()',
            'Too few arguments to function IrfanTOOR\Test::assertEquals() -');
    }

    function testSkip()
    {
        $this->assertEquals('1');
    }

    public function testMethodSkip()
    {
        throw new Exception("method throws an exception, it must be skipped");
    }

    /**
     * throws: Exception::class
     */
    public function testExceptionThrown()
    {
        throw new Exception("method throws an exception, it must be skipped");
    }

    /**
     * throws: Exception::class
     * message: method throws an exception, it must be skipped
     */
    public function testExceptionThrownWithMessage()
    {
        throw new Exception("method throws an exception, it must be skipped");
    }

    /**
     * Single parameter
     * a: $this->getArgs()
     */
    public function testSource($a)
    {
        $this->assertNotNull($a);
    }

    /**
     * throws: Exception::class
     * a: $this->getArgs()
     */
    public function testExceptionWithSource($a)
    {
        throw new Exception($a);
    }

    /**
     * throws: Exception::class
     * message: {$a}
     * a: $this->getArgs()
     */
    public function testExceptionWithSourceAndMessage($a)
    {
        throw new Exception($a);
    }

    function getArgs()
    {
        return [
            'a',
            'b',
            'c',
        ];
    }
}
