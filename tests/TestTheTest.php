<?php

use IrfanTOOR\Test;
use IrfanTOOR\Test\TestCommand;

use Tests\SomeInterface;
use Tests\SomeClass;
use Tests\ExtendedClass;

class TestTheTest extends Test
{
    const HELLO = 'hello';

    protected $t = null;
    protected $c = 0;
    protected $throwables;

    protected $method_skipped = 0;

    function __construct()
    {
        parent::__construct();
        $this->t = new StdClass();

        $this->throwables = [
            'exception'       => new Exception,
            'error'           => new Error,
            'throw_exception' => function () { throw new Exception(""); },
            'throw_error'     => function () { throw new Error("");     },
        ];

        $this->not_throwables = [
            null,
            0,
            1,
            1.1,
            "",
            [],
            self::HELLO,
            $this->t,
        ];

        $this->thrown = [
            function () { throw new Exception(""); },
            function () { throw new Error("");     },
            function () { return new Hello;        },

            # todo -- error is not caught!
            // function () { include "unknown file";  },
        ];

        $this->not_thrown = [
            function() { return new Exception(""); },
            function() { return new Error("");     },
            function() { return new StdClass();    },
        ];
    }

    function setup()
    {
        $this->c++;
    }

    function testConstruct()
    {
        # setup was called in the beginning
        $this->assertEquals(1, $this->c);

        $this->assertObject($this);
        $this->assertInstanceOf(Test::class, $this);

        # construct is called
        $this->assertInstanceOf(StdClass::class, $this->t);

        # construct is only called once
        $this->t = null;
    }

    function testSetup()
    {
        # construct is only called once
        $this->assertNull($this->t);

        # setup was called in the beginning
        $this->assertEquals(2, $this->c);

        # setup is only called once
        $this->assertEquals(2, $this->c);
        $this->assertEquals(2, $this->c);
    }

    function testAssertEquals()
    {
        $a = 'hello';
        $this->assertEquals('hello', $a);

        $a = 6;
        $b = 6;
        $this->assertEquals($a, $b);

        $c = new SomeClass;
        $d = new SomeClass;
        $this->assertEquals($c, $d);

        $e = new ExtendedClass();
        $this->assertEquals("f", $e->f());
        $this->assertEquals("g", $e->g());
        $this->assertEquals("fg", $e->fg());

        # might seem confusing ...
        $this->assertEquals('1234', 1234);
        $this->assertEquals(null, 0);
        $this->assertEquals(null, "");
        $this->assertEquals(null, []);
        $this->assertEquals(false, 0);
        $this->assertEquals(true, 1);
    }

    function testAssertNotEquals()
    {
        $a = 6;
        $b = 6;
        $b++;

        $this->assertNotEquals($a, $b);
        $this->assertNotEquals('12345', 1234);
        $this->assertNotEquals(0, 1);
        $this->assertNotEquals(1, [1]);
        $this->assertNotEquals([null], []);

        # not associative ;-)
        $this->assertNotEquals(0, []);
        $this->assertNotEquals("", []);
    }

    function testAssertSame()
    {
        $a = 6;
        $b = 6;
        $this->assertSame($a, $b);

        $c = new SomeClass;
        $d = $c;
        $this->assertSame($c, $d);

        $e = &$c;
        $f = &$d;
        $this->assertSame($e, $f);
    }

    function testAssertNotSame()
    {
        $b = 6;
        $this->assertNotSame('6', $b);
        $this->assertNotSame('1234', 1234);

        $c = new SomeClass;
        $d = new SomeClass;

        $this->assertEquals($c, $d);
        $this->assertNotSame($c, $d);

        $this->assertEquals(null, '');
        $this->assertNotSame(null, '');
    }

    function testAssertArray()
    {
        $this->assertArray([]);
        $this->assertArray(['hello', 'world']);
        $this->assertArray(['h' => 'hello', 'w' => 'world']);
        $this->assertArray([0, null]);
        $this->assertArray(explode(',', 'a,b,c'));
        $this->assertArray($_SERVER);
        $this->assertArray([$this, 'testAssertArray']);
    }

    function testAssertNotArray()
    {
        $this->assertNotArray(true);
        $this->assertNotArray(false);
        $this->assertNotArray(!false);
        $this->assertNotArray(!true);
        $this->assertNotArray(null);
        $this->assertNotArray(0);
        $this->assertNotArray(1.1);
        $this->assertNotArray('hello');
        $this->assertNotArray($this);
        $this->assertNotArray(new Exception);
    }

    function testAssertArrayHasKey()
    {
        $this->assertArrayHasKey(['zero'], '0');
        $this->assertArrayHasKey(['zero'], '0');
        $this->assertArrayHasKey([1], '0');
        $this->assertArrayHasKey([9=>'a', 'b'], '10');
        $this->assertArrayHasKey(['zero', 'a' => 'apple'], 'a');
        $this->assertArrayHasKey($_SERVER, 'argv');
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
        $this->assertNotBool(1);
        $this->assertNotBool(-1);
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
        $this->assertNull(NULL);

        $a = null;
        $this->assertNull($a);
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

    function testAssertZero()
    {
        $this->assertZero(0);
        $this->assertZero(0.);
        $this->assertZero(0.0);
        $this->assertZero(10 - 10);
        $this->assertZero('12' - '12');
        $this->assertZero(1 - '1');
        $this->assertZero('-1' + 1);
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
        $this->assertNotCallable(true);
        $this->assertNotCallable(false);
        $this->assertNotCallable(!false);
        $this->assertNotCallable(!true);
        $this->assertNotCallable(null);
        $this->assertNotCallable(0);
        $this->assertNotCallable(1.1);
        $this->assertNotCallable('hello');
        $this->assertNotCallable(new Exception);
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

    function testAssertMethod()
    {
        $object = new ExtendedClass();

        $this->assertMethod($object, 'fg');
        $this->assertMethod($object, 'gf');
    }

    function testAssertNotMethod()
    {
        $object = new ExtendedClass();

        $this->assertNotMethod($object, 'gh');
        $this->assertNotMethod($object, 'hg');
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
        $a = ['HELLO', 'WORLD'];
        $this->assertScalar(self::HELLO);
        $this->assertScalar($a[1]);
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
        $this->assertDir(dirname(__FILE__) . '/');
        $this->assertDir(__DIR__);
        $this->assertDir(__DIR__ . '/');
        $this->assertDir(__DIR__ . '/.');
        $this->assertDir(__DIR__ . '/..');
        $this->assertDir(dirname(__FILE__));
    }

    function testAssertNotDir()
    {
        $d = [__DIR__, '--'];
        $this->assertNotDir($d[0] . $d[1]);
        $this->assertNotDir(2);
        $this->assertNotDir(true);
        $this->assertNotDir(false);
        $this->assertNotDir(!false);
        $this->assertNotDir(!true);
        $this->assertNotDir(null);
        $this->assertNotDir(0);
        $this->assertNotDir(1.1);
        $this->assertNotDir('hello');
        $this->assertNotDir(new Exception);
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
        $this->assertNotFile(dirname(__FILE__) . '/');
        $this->assertNotFile(__DIR__);
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
        $this->assertTodo("write tests to check the proper functioning of file uploading");
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

        # even the interface
        $this->assertImplements(SomeInterface::class, $d);
    }

    function testAssertImplements()
    {
        # implements
        $c = new ExtendedClass;
        $this->assertImplements(SomeInterface::class, $c);

        $this->assertNotImplements(SomeInterface::class, $this);

        # these are the extentions and not implementations en stricto sensu
        $this->assertNotImplements(SomeClass::class, $c);
        $this->assertNotImplements(Test::class, $this);
    }

    function testAssertThrowable()
    {
        foreach ($this->throwables as $t) {
            $this->assertThrowable($t);
        }

        $this->assertThrowable(new Error());
        $this->assertThrowable(new Exception());

        foreach ($this->not_throwables as $nt) {
            $this->assertNotThrowable($nt);
        }
    }

    function testAssertException()
    {
        $this->assertException($this->throwables['exception']);
        $this->assertException($this->throwables['throw_exception']);
        $this->assertNotException($this->throwables['error']);
        $this->assertNotException($this->throwables['throw_error']);
        $this->assertException(new Exception());
        $this->assertNotException(new Error());

        foreach ($this->not_throwables as $nt) {
            $this->assertNotException($nt);
        }
    }

    function testAssertError()
    {
        $this->assertError($this->throwables['error']);
        $this->assertError($this->throwables['throw_error']);
        $this->assertError(new Error());

        $this->assertNotError($this->throwables['exception']);
        $this->assertNotError($this->throwables['throw_exception']);
        $this->assertNotError(new Exception());

        foreach ($this->not_throwables as $nt) {
            $this->assertNotError($nt);
        }
    }

    function testAssertThrown()
    {
        foreach ($this->thrown as $th) {
            $this->assertThrown($th);
        }

        $this->assertNotThrown(new Error());
        $this->assertNotThrown(new Exception());

        foreach ($this->not_thrown as $nth) {
            $this->assertNotThrown($nth);
        }
    }

    function testAssertThrownException()
    {
        $this->assertThrownException($this->throwables['throw_exception']);
        $this->assertThrownException(function () { throw new Exception(""); });

        $this->assertNotThrownException(function () { throw new Error(""); });
        $this->assertNotThrownException(new Error());
        $this->assertNotThrownException(new Exception());

        foreach ($this->not_thrown as $nth) {
            $this->assertNotThrownException($nth);
        }
    }

    function testAssertThrownError()
    {
        $this->assertThrownError($this->throwables['throw_error']);
        $this->assertThrownError(function () { throw new Error(""); });

        $this->assertNotThrownError(function () { throw new Exception(""); });
        $this->assertNotThrownError(new Error());
        $this->assertNotThrownError(new Exception());

        foreach ($this->not_thrown as $nth) {
            $this->assertNotThrownError($nth);
        }
    }

    function testProcessAssertion()
    {
        // const ASSERTION_PASSED        =  1; # assertion passed
        $result  = $this->processAssertion('assertEquals', [1, 1]);
        $this->assertEquals(self::ASSERTION_PASSED, $result['status']);

        // const ASSERTION_FAILED        =  0; # assertion failed
        $result  = $this->processAssertion('assertEquals', [1, 0]);
        $this->assertEquals(self::ASSERTION_FAILED, $result['status']);

        // const ASSERTION_EXCEPTION     = -1; # assertion threw an exception
        $result  = $this->processAssertion('assertRaiseException', [
            function () {
                throw new Exception("ASSERTION_EXCEPTION");
            }
        ]);
        $this->assertEquals(self::ASSERTION_EXCEPTION, $result['status']);
        $this->assertEquals("ASSERTION_EXCEPTION", $result['message']);


        // const ARGUMENTS_COUNT_ERROR   = -2; # assertion called with bad count of arguments
        $result  = $this->processAssertion('assertEquals', [1]);
        $this->assertEquals(self::ARGUMENTS_COUNT_ERROR, $result['status']);

        // const ASSERTION_UNKNOWN       = -3; # assertion is not defined in the list of assertions
        $result  = $this->processAssertion('assertCallback', [function() {}]);
        $this->assertEquals(self::ASSERTION_UNKNOWN, $result['status']);

        // const ASSERTION_MISMATCH      = -4; # not even an assertion e.g. $this->asserEquals(1,1);
        $result  = $this->processAssertion('asserEquals', [1, 1]);
        $this->assertEquals(self::ASSERTION_MISMATCH, $result['status']);
    }

    /**
     * throws: Exception::class
     */
    public function testExceptionThrown()
    {
        throw new Exception("method throws an exception, it must not be skipped");
    }

    /**
     * throws: Exception::class
     * message: method throws an exception, it must not be skipped
     */
    public function testExceptionThrownWithMessage()
    {
        throw new Exception("method throws an exception, it must not be skipped");
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

    function testGetProperty()
    {
        $s = self::$server;
        try {
            $root = $s->root;
        } catch (\Throwable $th) {
        }
        $this->assertEquals('Cannot access protected property IrfanTOOR\\Test\\TestCommand::$root', $th->getMessage());
        $root = $this->getProperty($s, 'root');
        $this->assertString($root);
        $this->assertNotZero(strlen($root));

        $passed = $this->getProperty($s, 'passed');
        $this->assertTrue(is_integer($passed));
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
