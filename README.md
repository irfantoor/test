# IrfanTOOR\Test

Test without a twist.

Require it using composer:
```sh
$ composer require --dev irfantoor/test
```

## Usage with an example

file: TestMyClass.php
[note: name must match with the test class name]

```php
<?php

use IrfanTOOR\Test;

interface MyInterface
{

}

class MyClass implements MyInterface
{

}

class TestMyClass extends Test
{
    function setup()
    {
        # anything you want to do before any testFunction is called
    }

    function testExamples()
    {
        $another = $class = new MyClass();
        $class2 = new MyClass();
        
        $this->assertNull(null);
        $this->assertNotNull($class);

        $this->assertTrue(0 === 0);
        $this->assertFalse(0 === 1);
        $this->assertZero(0);
        $this->assertString('hello');
        $this->assertNotString($class);

        $this->assertEquals('1234', 1234);
        $this->assertNotEquals('12345', 1234);
        $this->assertSame($another, $class);
        $this->assertNotSame($class, $class2);

        $this->assertObject($class);
        $this->assertNotObject([]);

        $this->assertInstanceOf(MyClass::class, $class);
        $this->assertImplements(MyInterface::class, $class);

        $this->assertException(function(){
            throw new Exception("Error Processing Request", 1);
        });

        $this->assertExceptionMessage("Error Message", function(){
            throw new Exception("Error Message", 1);
        });

        # this will fail
        $this->assertNotObject(new Exception);
    }
}
```

```sh
$ ./test TestMyClass.php
test 0.1
TestMyClass.php
  [17] testExamples .................F  [1]
   /Users/irfantoor/test/TestMyClass.php -- [line: 56]
expected: non_object returned: Exception Object
   17 passed  1 failed 
```
