<?php

/**
 * IrfanTOOR\Test
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR;

use Throwable;
use Exception;

/**
 * Test -- base class to be used for TestClasses, to implement the unit testing
 * e.g. HelloTest.php
 * <?php
 * use Your\Class\Hello;
 * use IrfanTOOR\Test;
 * class HelloTest extends Test
 * {
 *      // used to init the unit tests - called only once in the beginning
 *      function __construct() {}
 *
 *      // used to setup an env before calling every unit test method
 *      function setup() {}
 *
 *      // example unit test
 *      function testHelloInstance()
 *      {
 *           $h = new Hello();
 *           $this->assertInstanceOf(Hello::class, $h);
 *      }
 * }
 */
class Test
{
    const NAME        = "Unit Test";
    const DESCRIPTION = "and I test ....";
    const VERSION     = "0.7.1";

    /**
     * Constants describing different assertion statuses
     * For example:
     *  ASSERTION_UNKNOWN:  $this->assertHello(); # Hello not found in assertions
     *  ASSERTION_MISMATCH: $this->asserWorld();  # does not conatin assert
     */
    const ASSERTION_PASSED        =  1; # Assertion passed
    const ASSERTION_FAILED        =  0; # Assertion failed
    const ASSERTION_EXCEPTION     = -1; # Threw an exception
    const ARGUMENTS_COUNT_ERROR   = -2; # Called with bad count of arguments
    const ASSERTION_UNKNOWN       = -3; # Not defined in the list of assertions
    const ASSERTION_MISMATCH      = -4; # Not even an assertion

    /**
     * Any class capable of receiving notification message through a function :
     * notify(array $message)
     */
    protected static $server;

    /**
     * Definition oof assertions, which are used for creating the definition
     * of assertion function and its arguments. Keys help in creating a virtual
     * method, and the values help in creating the expression to evaluate and
     * the arguments required.
     * 
     * For example with the definition: 'Equals' => '$expeced == $returned',
     *    key 'Equals' can create two assertions:
     *    1. assertEquals
     *    2. assertNotEquals
     * 
     *    value contains two variables, so the final assertions, must be called
     *    with two arguments:
     *    1. $this->assertEquals($expected, $returned);
     *    2. $this->assertNotEquals($expected, $returned);
     */
    protected static $assertions = [
        # Equality
        'Equals' => '$expected == $returned',
        'Same'   => '$expected === $returned',

        # Exceptions
        'Throwable'       => 'IrfanTOOR\Test::is_throwable($callable)',
        'Exception'       => 'IrfanTOOR\Test::is_throwable($callable, "Exception")',
        'Error'           => 'IrfanTOOR\Test::is_throwable($callable, "Error")',

        'Thrown'          => 'IrfanTOOR\Test::is_thrown($callable)',
        'ThrownException' => 'IrfanTOOR\Test::is_thrown($callable, "Exception")',
        'ThrownError'     => 'IrfanTOOR\Test::is_thrown($callable, "Error")',

        # objects / callable
        'Callable'     => 'is_callable($object)',
        'InstanceOf'   => [
            'args' => ['class', 'object'],    # so that class is the first arg
            'exp' => 'is_a($object, $class)',
        ],
        'Implements'   => [
            'args' => ['class', 'object'],
            'exp'  => 'interface_exists($class) && is_subclass_of($object, $class)',
        ],
        'Object'       => 'is_object($value)',
        'Resource'     => 'is_resource($value)',
        'SubclassOf'   => [
            'args' => ['class', 'object'],
            'exp'  => 'is_subclass_of($object, $class)',
        ],
        'Method'    => 'is_object($object) && method_exists($object, $method_name)',

        # boolean
        'Bool'         => 'is_bool($value)',
        'False'        => '$value === false',
        'True'         => '$value === true',

        # numeric variables
        'Zero'         => '$value === 0 || $value === 0.0',
        'Double'       => 'is_double($value)',
        'Finite'       => 'is_finite($value)',
        'Float'        => 'is_float($value)',
        'Infinite'     => 'is_infinite($value)',
        'Int'          => 'is_int($value)',
        'Integer'      => 'is_int($value)',
        'Long'         => 'is_long($value)',
        'Nan'          => 'is_nan($value)',
        'Null'         => 'is_null($value)',
        'Numeric'      => 'is_numeric($value)',
        'Real'         => 'is_real($value)',
        'Scalar'       => 'is_scalar($value)',

        # other variable types
        'Array'        => 'is_array($value)',
        'ArrayHasKey'  => 'is_array($array) && array_key_exists($key, $array)',
        'String'       => 'is_string($value)',
        'Empty'        => '$value === "" || $value === [] || $value === null',

        # filesystem
        'Dir'          => 'is_dir($filename)',
        'Executable'   => 'is_executable($filename)',
        'File'         => 'is_file($filename)',
        'Link'         => 'is_link($filename)',
        'Readable'     => 'is_readable($filename)',
        'UploadedFile' => 'is_uploaded_file($filename)',
        'Writable'     => 'is_writable($filename)',
        'Writeable'    => 'is_writable($filename)',
        
        # ... other expressions can be added here

        # exception case - i.e. to catch if a defined expression is not valid
        'RaiseException'   => '$callable()',
    ];

    /**
     * Called once in the beginning by the class extending from Test 
     */
    public function __construct() {}

    /** 
     * Called before every method of the class extending from test
     */
    public function setup() {}

    /**
     * Process the assertion, notify and return result
     * Note: All the assertions not only returns the result, but can also
     * notify to a server, so that the results can be processed or printed etc.
     *
     * @return array Result of all the assertions passed, failed or skipped etc.
     */
    public function __call($method, $args): array
    {
        $result = $this->processAssertion($method, $args);

        if (self::$server) {
            self::$server->notify($result);
        }

        return $result;
    }

    /**
     * Register a notification server
     *
     * @param object $server Object containing a method notify(array $message)
     */
    public function register($server)
    {
        self::$server = $server;
    }

    /**
     * Process an assertions, it is called by the __call function
     * e.g. $this->assertEquals(1, $a); is translated into:
     *     $method = 'assertEquals',
     *     $args   = [1, $a]
     *
     * @param string $method Assertion method
     * @param array  $args   Arguments of assertion
     * @return array 
     */ 
    protected function processAssertion($method, $args): array
    {
        # backtrace
        $bt = debug_backtrace();
        $line = $bt[1]['line'];

        # only processes the assert... calls
        # assertion must start with the assert
        if (strpos($method, 'assert') !== 0) {
            return [
                'status' => self::ASSERTION_MISMATCH,
                'line'   => $line,
                'method' => $method,
            ];
        }

        # keep track if its an assertNot
        $not = (strpos($method, 'assertNot') === 0);

        # remove the assertNot and assert from the method
        $key = str_replace(['assertNot', 'assert'], '', $method);

        # do we have a definition ?
        $assert = self::$assertions[$key] ?? null;

        # if assertion is not defined in our list of assertions
        if (!$assert) {
            return [
                'status' => self::ASSERTION_UNKNOWN,
                'line'   => $line,
                'method' => $method
            ];
        }

        # extract the expected arguments from the expression or explicitly
        # defined array
        if (is_string($assert)) {
            # provided string is the expression
            $exp = $assert;

            # e.g. '$expected === $var', will create an array ['expected', 'var']
            preg_match_all('|\$(\w*)|', $exp, $m);
            $args_exp = $m[1];

            # extract unique args and reset their index
            $args_exp = array_values(array_unique($args_exp));
        } else {
            # expression is provided with a key name exp
            $exp = $assert['exp'];

            # if args are provided with key args use these else use ['value']
            $args_exp = $assert['args'] ?? ['value'];
        }

        # if provided arguments are not equal to the expected arguments
        if (count($args_exp) != count($args)) {
            return [
                'status' => self::ARGUMENTS_COUNT_ERROR,
                'line'   => $line,
                'method' => $method,
                'arguments' => $args_exp,
            ];
        }

        # assign the arguments to their respective variables
        foreach ($args_exp as $k => $v) {
            $$v = $args[$k];
        }

        # evaluate the associated expression
        # and process results
        try {
            eval('$result = ' . ($not ? '!(' : '(') . $exp . ");");

            if ($result) {
                return [
                    'status' => self::ASSERTION_PASSED,
                    'line'   => $line,
                    'method' => $method,
                ];
            } else {
                return [
                    'status' => self::ASSERTION_FAILED,
                    'line'   => $line,
                    'method' => $method,
                    'expected' => $args_exp,
                    'args' => $args,
                ];
            }
        } catch (Throwable $e) {
            return [
                'status' => self::ASSERTION_EXCEPTION,
                'line'   => $line,
                'method' => $method,
                'message'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifies if a callback object is throwable (thorwn or not)
     *
     * @param closure|object $callback Closure function, or an object
     * @param object          $class    Class type expcted
     * @return bool True if the $callback is throwable, and of type $class
     */
    protected static function is_throwable($callback, $class = Throwable::class)
    {
        try {
            $e = is_callable($callback) ? $callback() : $callback;
        } catch (Throwable $e) {
        }

        return is_a($e, $class);
    }

    /**
     * Verifies if a callable throws a Throwable - an Exception or an Error
     * Note: The callable or object must throw an object to be tested
     *
     * @param closure|object $callable Closure function, or an invokable object
     * @param object   $class    Class type expcted to be thrown
     * @return bool True if the callable throws the $class
     */
    protected static function is_thrown($callback, $class = Throwable::class)
    {
        try {
            $e = is_callable($callback) ? $callback() : $callback;
            return false;
        } catch (Throwable $e) {
            return is_a($e, $class);
        }
    } 
}
