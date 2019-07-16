<?php

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\{
    Console,
    Debug
};

class Test
{
    const NAME        = "test";
    const DESCRIPTION = "and I Test ...";
    const VERSION     = "0.6.2"; # @@VERSION

    # types which can be tested with is_ prefix.
    protected static $types = [

        # simple ...
        'array', 'bool', 'null', 'string', # 'empty',

        # numeric ...
        'double', 'float', 'int', 'integer', 'long',  'numeric', 'real', 'scalar', # 'zero'

        # mixed
        'callable', 'object', 'resource',

        # filesystem ...
        'dir', 'executable', 'file', 'link', 'readable', 'writable', 'writeable', 'uploaded_file'
    ];

    // protected $options;

    protected $count  = 0;
    protected $passed = 0;
    protected $result = [];

    function __construct() {}

    function setup() {}

    function getResult()
    {
        $result = [
            'count'   => $this->count,
            'passed'  => $this->passed,
            'result'  => $this->result,
        ];

        $this->count  = 0;
        $this->passed = 0;
        $this->result = [];

        return $result;
    }

    function assert($expected, $exp, $var, $message = '')
    {
        $this->count++;

        switch ($exp) {
            case '==':
                $r = ($expected == $var);
                break;

            case '!=':
                $r = ($expected != $var);
                break;

            case '===':
                $r = ($expected === $var);
                break;

            case '!==':
                $r = ($expected !== $var);
                break;

            default:
                $r = false;
        }

        if ($r) {
            $this->passed++;
            $this->result[] .= '.';
        } else {
            $trace = debug_backtrace();

            $this->result[] = [
                'expected' => $expected,
                'var'      => $var,
                'message'  => $message,
                'trace'     => $trace,
            ];
        }

        return $r;
    }

    /**
     * Process the assert for following functions:
     *
     *  function assertArray($var)
     *  function assertNotArray($var)
     *  function assertBool($var)
     *  function assertNotBool($var)
     *  function assertNull($var)
     *  function assertNotNull($var)
     *  function assertString($var)
     *  function assertNotString($var)
     *  function assertDouble($var)
     *  function assertNotDouble($var)
     *  function assertFloat($var)
     *  function assertNotFloat($var)
     *  function assertInt($var)
     *  function assertNotInt($var)
     *  function assertInteger($var)
     *  function assertNotInteger($var)
     *  function assertLong($var)
     *  function assertNotLong($var)
     *  function assertNumeric($var)
     *  function assertNotNumeric($var)
     *  function assertReal($var)
     *  function assertNotReal($var)
     *  function assertScalar($var)
     *  function assertNotScalar($var)
     *  function assertCallable($var)
     *  function assertNotCallable($var)
     *  function assertObject($var)
     *  function assertNotObject($var)
     *  function assertResource($var)
     *  function assertNotResource($var)
     *  function assertDir($var)
     *  function assertNotDir($var)
     *  function assertExecutable($var)
     *  function assertNotExecutable($var)
     *  function assertFile($var)
     *  function assertNotFile($var)
     *  function assertLink($var)
     *  function assertNotLink($var)
     *  function assertReadable($var)
     *  function assertNotReadable($var)
     *  function assertWritable($var)
     *  function assertNotWritable($var)
     *  function assertWriteable($var)
     *  function assertNotWriteable($var)
     *  function assertUploadedFile($var)
     *  function assertNotUploadedFile($var)
     */
    function __call($method, $args)
    {
        $not  = strpos($method, 'Not') !== false ? "not " : "";
        $type = strtolower(str_replace(['assert', 'Not'], '', $method));
        $type = ($type === "uploadedfile") ? "uploaded_file" : $type;
        $f    = 'is_' . $type;
        $var  = isset($args[0]) ? $args[0] : null;

        if (in_array($type, self::$types)) {
            $r = ($not ? !$f($var) : $f($var)) ? $var : ($var === null ? 'NULL' : null);
            return $this->assert($r, '===', $var, $not . $type, false);
        } else {
            throw new Exception("unknown-type ($type)", 1);
        }
    }

    function assertEquals($exp, $var)
    {
        return $this->assert($exp, '==', $var, 'equal to %s');
    }

    function assertNotEquals($exp, $var)
    {
        return $this->assert($exp, '!=', $var, 'not equal to %s');
    }

    function assertSame($exp, $res)
    {
        return $this->assert($exp, '===', $res, 'same as %s');
    }

    function assertNotSame($exp, $res)
    {
        return $this->assert($exp, '!==', $res, 'not same as %s');
    }

    function assertArrayHasKey($key, $array)
    {
        $this->assert(true, '===', is_array($array) && array_key_exists($key, $array),
            'array has key: ' . $key);
    }

    function assertFalse($var)
    {
        return $this->assert(false, '===', $var);
    }

    function assertTrue($var)
    {
        return $this->assert(true, '===', $var);
    }

    function assertEmpty($var)
    {
        if(is_string($var)) {
            $this->assert('', '===', $var, 'empty');
        } elseif(is_array($var)) {
            $this->assert([], '===', $var, 'empty');
        } else {
            $this->assert(null, '===', $var, 'empty');
        }
    }

    function assertNotEmpty($var)
    {
        if(is_string($var)) {
            $this->assert('', '!==', $var, 'not empty');
        } elseif(is_array($var)) {
            $this->assert([], '!==', $var, 'not empty');
        } else {
            $this->assert(null, '!==', $var, 'not empty');
        }
    }

    function assertZero($var)
    {
        if (is_real($var)) {
            return $this->assert(0.0, '===', $var, 'zero');
        } else {
            return $this->assert(0, '===', $var, 'zero');
        }
    }

    function assertNotZero($var)
    {
        if (is_real($var)) {
            return $this->assert(0.0, '!==', $var, 'not zero');
        } else {
            return $this->assert(0, '!==', $var, 'not zero');
        }
    }

    function assertInstanceOf($instance, $class)
    {
        # 'subclass_of', is_a(object, class_name)
        if (is_object($class)) {
            $parents = class_parents($class);
            if (in_array($instance, $parents))
            {
                return $this->assert($instance, '===', $parents[$instance]);
            } elseif ($instance === get_class($class)) {
                return $this->assert($instance, '===', get_class($class));
            } else {
                return $this->assert($instance, '===', $class, "instance of " . $instance, false);
            }
        } else {
            return $this->assert($class, '!==', $class, "instance of " . $instance, false);
        }
    }

    function assertNotInstanceOf($instance, $class)
    {
        if (is_object($class)) {
            $parents = class_parents($class);
            if (in_array($instance, $parents))
            {
                return $this->assert($instance, '===', $parents[$instance], 'not an instance of ' . $instance, false);
            } elseif ($instance === get_class($class)) {
                return $this->assert($instance, '!==', get_class($class), 'not an instance of ' . $instance, false);
            } else {
                return $this->assert($instance, '!==', $class, 'not an instance of' . $instance, false);
            }
        } else {
            return $this->assert($class, '===', $class);
        }
    }

    function assertImplements($instance, $class)
    {
        if (is_object($class)) {
            $interfaces = class_implements($class);
            if (in_array($instance, $interfaces))
            {
                return $this->assert($instance, '===', $interfaces[$instance]);
            } elseif ($instance === get_class($class)) {
                return $this->assert($instance, '===', get_class($class));
            } else {
                 return $this->assert($instance, '===', $class, "Implimentation of " . $instance, false);
            }
        } else {
            return $this->assert($class, '!==', $class, "Implimentation of " . $instance, false);
        }
    }

    function assertException($f, $class = 'Exception', $message = null)
    {
        $e = null;
        try {
            if (is_callable($f))
                $f();

            $e = $f;
        } catch (Exception $e) {
        } catch (Error $e) {
        }

        $this->assertInstanceOf($class, $e);

        if ($message !== null) {
            $this->assertEquals($message, $e->getMessage());
        }
    }

    function assertExceptionMessage($msg, $f)
    {
        $e = null;
        try {
            if (is_callable($f))
                $f();

            $this->assert($f, '!==', $f, 'Exception', false);
        } catch (Exception $e) {
            $this->assert($msg, '===', $e->getMessage());
        } catch(Error $e) {
            $this->assert($msg, '===', $e->getMessage());
        }
    }
}
