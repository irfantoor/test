# IrfanTOOR\Test

Test with a twist.

Require it using composer:
```sh
$ composer require --dev irfantoor/test
```

## Usage with an example

file: examples/TestMyClass.php
[note: name must match with the test class name]

```php
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

class TestMyClass extends Test
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
        $this->assertArrayHasKey('a', ['zero', 'a' => 'apple']);

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
        $this->assertNotCallable([$this, 'hello']);
        $this->assertObject($class);
        $this->assertNotObject([]);
        $this->assertResource(STDIN);
        $this->assertNotResource($class);

        # class instance and implementation
        $this->assertInstanceOf(SomeClass::class, $class);
        $this->assertImplements(SomeInterface::class, $class);

        # Assert Exception only
        $this->assertException(
            function(){
                throw new Exception("Exception Message", 1);
            }
        );

        # Assert Exception Message Only
        $this->assertExceptionMessage("Error Message", function(){
            throw new Exception("Error Message", 1);
        });

        # Assert Exception and the Message
        $this->assertException(
            function(){
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
    }

    function testSkip()
    {
        $this->assertEquals('1');
    }
}

```

```sh
$ ./test examples/TestMyClass.php
                     
  test 0.1           
  test with a twist  
                     

examples/TestMyClass.php
  [57] testExamples .................................F........................  [1]
         expected: not zero             
         returned: float(0) -> line: 84 

  [  ] testSkip (skipped) line: 140 >>  Exception: Too few arguments to function IrfanTOOR\Test::assertEquals() 

   57 passed     1 failed  
```
