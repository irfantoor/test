<?php

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\Console;

class Test
{
    # types which can be tested with is_ prefix.
    protected static $types = [

        # simple ...
        'array', 'bool', 'null', 'string', # 'empty',

        # numeric ...
        'double', 'float', 'int', 'integer', 'long',  'numeric', 'real', 'scalar', # 'zero'

        # mixed
        'callable', 'object', 'resource', 

        # filesystem ...
        'dir', 'executable', 'file', 'link', 'readable', 'writable', 'writeable', 'uploaded_file', 
    ];

    protected $options;

    protected $count = 0, $passed = 0;
    // protected $tcount = 0, $tpassed = 0;
    protected $assertions;
    protected $traces = [];

    function __construct($options = []) {
        $defaults = [
            'testdox' => false,
            'quite'   => false,
            'failed'  => false, 
        ];

        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $options))
                $options[$k] = $v;
        }

        $this->options = $options;
    }

    function setup() {}

    private function _limitVar($var)
    {
        ob_start();
        var_dump($var);
        $result = ob_get_clean();
        $lines = explode("\n", $result);
        $line = $lines[0];
        if (count($lines) > 2)
            $line .= ' ...';

        return $line;
    }

    function getTrace()
    {
        $t = debug_backtrace();
        $trace = $t[1];
        foreach($t as $tt) {
            if (isset($tt['file']) && strpos($tt['file'], '/src/Test.php') === false) {
                $trace = $tt;
                break;
            }
        }

        return $trace;        
    }

    function run()
    {
        $c = new Console();

        # comvert options into variables: e.g. $option_failed, $option_testdox etc.
        foreach ($this->options as $option) {
            ${'option_' . $option['long']} = $option['value'];
        }

        $methods = get_class_methods($this);
        foreach($methods as $method) {
            preg_match_all('|test.*|uS', $method, $m);
            if (!isset($m[0][0]))
                continue;

            $this->setup();
            
            try {
                $e = null;
                ob_start();
                $this->$method();
            } catch (\Exception $e) {
            } catch (\Error $e) {
            }

            if ($e) {
                ob_get_clean();

                # $trace = $this->getTrace();
                $c->write("  [  ] $method (skipped) ", 'red');

                if (!$option_testdox) {
                    $c->write('line: ' . $e->getTrace()[0]['line'] . ' >> ', 'light_red');
                    $short_message = explode(',', $e->getMessage())[0];
                    $c->writeln(' Exception: ' . $short_message . ' ', 'dark');
                } else {
                    $c->writeln('');
                }

                $this->assertions[$method] = [
                    'passed' => 0,
                    'failed' => 0,
                    'skipped' => 1,
                ];

                continue;
            }

            $result = ' ' . ob_get_clean() . ' ';

            if ($this->passed == $this->count) {
                if (!$option_failed) {
                    $c->write(sprintf('  [%2d] %s', $this->passed, $method), 'green');
                    $c->writeln($result);
                }
            } else {
                $c->write(sprintf('  [%2d] ', $this->passed), 'green');
                $c->write(sprintf('%s', $method), 'red');
                $c->write($result);
                $c->writeln(' [' . ($this->count - $this->passed) . ']', 'red');
            }

            $this->assertions[$method] = [
                'passed' => $this->passed,
                'failed' => $this->count - $this->passed,
            ];

            $this->count = $this->passed = 0;

            if (!$option_testdox) {
                foreach ($this->traces as $t) {
                    extract($t);
                    extract($trace);

                    $l = [];
                    $l[0] = 'expected: ';
                    $l[0] .= $message ? $message . ' ' : '';

                    if ($message === '')
                        $l[0] .= $this->_limitVar($expected);

                    $l[1] = 'returned: ';
                    $l[1] .= $this->_limitVar($result);
                    $l[1] .= " -> line: $line";

                    $max = 0;
                    foreach ($l as $v) {
                        $max = max($max, strlen($v));
                    }

                    foreach ($l as $k => $v) {
                        $pad = str_repeat(' ', $max - strlen($v));
                        $c->write('       ');
                        $c->write(' ', 'bg_red');
                        $c->writeln(' ' . $v . $pad . ' ', ['bg_yellow', 'black']);
                    }
                    $c->writeln('');
                }
            }

            $this->traces = [];
        }

        return $this->assertions;
    }

    function assert($expected, $exp, $result, $message = '')
    {

        # comvert options into variables: e.g. $option_failed, $option_testdox etc.
        foreach ($this->options as $option) {
            ${'option_' . $option['long']} = $option['value'];
        }

        $this->count++;
        if ($this->count % 64 == 0)
            if (!$option_testdox)
                echo PHP_EOL . "       ";

        switch ($exp) {
            case '==':
                $r = ($expected == $result);
                break;

            case '!=':
                $r = ($expected != $result);
                break;

            case '===':
                $r = ($expected === $result);
                break;

            case '!==':
                $r = ($expected !== $result);
                break;

            default:
                $r = false;
        }

        if ($r) {
            $this->passed++;
            if (!$option_testdox)
                echo ".";
            return true;
        } else {
            $this->traces[] = [
                'trace' => $this->getTrace(),
                'expected' => $expected,
                'result' => $result,
                'message' => $message,
            ];
            if (!$option_testdox)
                echo "F";
            return false;
        }
    }

    function assertEquals($exp, $var)
    {
        return $this->assert($exp, '==', $var, 'equal to ' . $this->_limitVar($exp));
    }    

    function assertNotEquals($exp, $var)
    {
        return $this->assert($exp, '!=', $var, 'not equal to ' . $this->_limitVar($exp));
    }

    function assertSame($exp, $res)
    {
        return $this->assert($exp, '===', $res, 'same as ' . $this->_limitVar($exp));
    }

    function assertNotSame($exp, $res)
    {
        return $this->assert($exp, '!==', $res, 'not same as ' . $this->_limitVar($exp));
    }
    
    function assertArray($var)
    {
        return $this->assertInternalType('array', $var);
    }

    function assertNotArray($var)
    {
        return $this->assertNotInternalType('array', $var);
    }

    function assertArrayHasKey($key, $array)
    {
        $this->assert(true, '===', is_array($array) && array_key_exists($key, $array), 
            'array has key: ' . $key);
    }

    function assertBool($var)
    {
        return $this->assertInternalType('bool', $var);
    }

    function assertNotBool($var)
    {
        return $this->assertNotInternalType('bool', $var);
    }

    function assertFalse($var)
    {
        return $this->assert(false, '===', $var);
    }    

    function assertTrue($var)
    {
        return $this->assert(true, '===', $var);
    }

    function assertNull($var)
    {
        return $this->assertInternalType('null', $var);
    }

    function assertNotNull($var)
    {
        return $this->assertNotInternalType('null', $var);
    }    

    function assertString($var)
    {
        return $this->assertInternalType('string', $var);
    }

    function assertNotString($var)
    {
        return $this->assertNotInternalType('string', $var);
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

   function assertDouble($var)
    {
        return $this->assertInternalType('double', $var);
    }

    function assertNotDouble($var)
    {
        return $this->assertNotInternalType('double', $var);
    }

    function assertFloat($var)
    {
        return $this->assertInternalType('float', $var);
    }

    function assertNotFloat($var)
    {
        return $this->assertNotInternalType('float', $var);
    }

    function assertInt($var)
    {
        return $this->assertInternalType('int', $var);
    }

    function assertNotInt($var)
    {
        return $this->assertNotInternalType('int', $var);
    }

    function assertInteger($var)
    {
        return $this->assertInternalType('int', $var);
    }

    function assertNotInteger($var)
    {
        return $this->assertNotInternalType('int', $var);
    }

    function assertLong($var)
    {
        return $this->assertInternalType('long', $var);
    }

    function assertNotLong($var)
    {
        return $this->assertNotInternalType('long', $var);
    }

    function assertNumeric($var)
    {
        return $this->assertInternalType('numeric', $var);
    }

    function assertNotNumeric($var)
    {
        return $this->assertNotInternalType('numeric', $var);
    }

    function assertReal($var)
    {
        return $this->assertInternalType('real', $var);
    }

    function assertNotReal($var)
    {
        return $this->assertNotInternalType('real', $var);
    }

    function assertScalar($var)
    {
        return $this->assertInternalType('scalar', $var);
    }

    function assertNotScalar($var)
    {
        return $this->assertNotInternalType('scalar', $var);
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

    function assertCallable($var)
    {
        return $this->assertInternalType('callable', $var);
    }

    function assertNotCallable($var)
    {
        return $this->assertNotInternalType('callable', $var);
    }

    function assertObject($var)
    {
        return $this->assertInternalType('object', $var);
    }

    function assertNotObject($var)
    {
        return $this->assertNotInternalType('object', $var);
    }

    function assertResource($var)
    {
        return $this->assertInternalType('resource', $var);
    }

    function assertNotResource($var)
    {
        return $this->assertNotInternalType('resource', $var);
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
        }
        
        if ($message !== null) {
            if ($e) {
                return $this->assertEquals($message, $e->getMessage());
            } else {
                return $this->assertInstanceOf($class, $e);
            }
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
        }
    }   






    function assertDir($var)
    {
        return $this->assertInternalType('dir', $var);
    }

    function assertNotDir($var)
    {
        return $this->assertNotInternalType('dir', $var);
    }


    function assertExecutable($var)
    {
        return $this->assertInternalType('executable', $var);
    }

    function assertNotExecutable($var)
    {
        return $this->assertNotInternalType('executable', $var);
    }

    function assertFile($var)
    {
        return $this->assertInternalType('file', $var);
    }

    function assertNotFile($var)
    {
        return $this->assertNotInternalType('file', $var);
    }

    function assertLink($var)
    {
        return $this->assertInternalType('link', $var);
    }

    function assertNotLink($var)
    {
        return $this->assertNotInternalType('link', $var);
    }

    function assertReadable($var)
    {
        return $this->assertInternalType('readable', $var);
    }

    function assertNotReadable($var)
    {
        return $this->assertNotInternalType('readable', $var);
    }

    function assertWritable($var)
    {
        return $this->assertInternalType('writable', $var);
    }

    function assertNotWritable($var)
    {
        return $this->assertNotInternalType('writable', $var);
    }

    function assertWriteable($var)
    {
        return $this->assertInternalType('writeable', $var);
    }

    function assertNotWriteable($var)
    {
        return $this->assertNotInternalType('writeable', $var);
    }

    function assertUploadedFile($var)
    {
        return $this->assertInternalType('uploaded_file', $var);
    }

    function assertNotUploadedFile($var)
    {
        return $this->assertNotInternalType('uploaded_file', $var);
    }

    function assertInternalType($type, $var)
    {
        if (in_array($type, self::$types)) {
            $f = 'is_' . $type;
            $r = $f($var) ? $var : ($var === null ? 'NULL' : null);
            $this->assert($r, '===', $var, $type, false);
        } else {
            throw new Exception("unknown-type ($type)", 1);
        }
    }

    function assertNotInternalType($type, $var)
    {
        if (in_array($type, self::$types)) {
            $f = 'is_' . $type;
            $r = !$f($var) ? $var : ($var === null ? 'NULL' : null);
            $this->assert($r, '===', $var, 'not ' . $type, false);
        } else {
            throw new Exception("unknown-type ($type)", 1);
        }
    }
}
