<?php

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\Console;

class Test
{
    protected $options;

    protected $count = 0, $passed = 0;
    protected $tcount = 0, $tpassed = 0;
    protected $traces = [];

    function __construct($options = []) {
        $defaults = [
            'testdox' => false,
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

        $methods = get_class_methods($this);
        foreach($methods as $method) {
            preg_match_all('|test.*|uS', $method, $m);
            if (!isset($m[0][0]))
                continue;

            $this->setup();
            ob_start();

            try {
                $this->$method();
            } catch (\Exception $e) {
                $trace = $this->getTrace();
                $c->writeln("  [  ] $method (skipped)", 'red');

                if (!$this->options['testdox']) {
                    $c->write('       ');
                    $c->writeln('Exception: ' . $e->getMessage(), 'dark');
                    $c->writeln('');
                }
                continue;
            } catch (\Error $e) {
                $trace = $this->getTrace();
                $c->writeln("  [  ] $method (skipped)", 'red');

                if (!$this->options['testdox']) {
                    $c->write('       ');
                    $c->writeln('Error: ' . $e->getMessage(), 'dark');
                    $c->writeln('');
                }
                continue;
            }

            $result = ' ' . ob_get_clean() . ' ';

            if ($this->passed == $this->count) {
                if (!$this->options['failed']) {
                    $c->write(sprintf('  [%2d] %s', $this->passed, $method), 'green');
                    $c->writeln($result);
                }
            } else {
                $c->write(sprintf('  [%2d] ', $this->passed), 'green');
                $c->write(sprintf('%s', $method), 'red');
                $c->write($result);
                $c->writeln(' [' . ($this->count - $this->passed) . ']', 'red');
            }

            $this->tcount += $this->count;
            $this->tpassed += $this->passed;
            $this->count = $this->passed = 0;

            if (!$this->options['testdox']) {
                foreach ($this->traces as $t) {
                    extract($t);
                    extract($trace);

                    $l = [];
                    $l[0] = 'expected: ';
                    $l[0] .= $message ? $message . ' ' : '';

                    if ($show)
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

        return [
            $this->tcount,
            $this->tpassed
        ];
    }

    function assert($expected, $exp, $result, $message = '', $show = true)
    {
        $this->count++;
        if ($this->count % 64 == 0)
            if (!$this->options['testdox'])
                echo "    ";

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
            if (!$this->options['testdox'])
                echo ".";
            return true;
        } else {
            $this->traces[] = [
                'trace' => $this->getTrace(),
                'expected' => $expected,
                'result' => $result,
                'message' => $message,
                'show' => $show
            ];
            if (!$this->options['testdox'])
                echo "F";
            return false;
        }
    }

    function assertEquals($exp, $res)
    {
        $this->assert($exp, '==', $res, 'equal to');
    }    

    function assertNotEquals($exp, $res)
    {
        $this->assert($exp, '!=', $res, 'not equal to');
    }

    function assertSame($exp, $res)
    {
        $this->assert($exp, '===', $res, 'same as');
    }

    function assertNotSame($exp, $res)
    {
        $this->assert($exp, '!==', $res, 'not same as');
    }

    function assertTrue($res)
    {
        return $this->assert(true, '===', $res);
    }

    function assertFalse($res)
    {
        return $this->assert(false, '===', $res);
    }

    function assertNull($var)
    {
        return $this->assert(null, '===', $var);
    }

    function assertNotNull($var)
    {
        return $this->assert(null, '!==', $var);
    }

    function assertZero($var)
    {
        return $this->assert(0, '===', $var);
    }

    function assertString($var)
    {
        if (is_string($var)) {
            $this->assert(true, '===', is_string($var));
        } else {
            $this->assert($var, '!==', $var, 'a string', false);
        }
    }

    function assertNotString($var)
    {
        if (!is_string($var)) {
            $this->assert(false, '===', is_string($var));
        } else {
            $this->assert(null, '===', $var, 'a non-string', false);
        }
    }

    function assertObject($var)
    {
        if (is_object($var)) {
            $this->assert(true, '===', is_object($var));
        } else {
            $this->assert($var, '!==', $var, 'an object', false);
        }
    }

    function assertNotObject($var)
    {
        if (!is_object($var)) {
            $this->assert(false, '===', is_object($var));
        } else {
            $this->assert($var, '!==', $var, 'a non-object', false);
        }
    }

    function assertInstanceOf($instance, $class)
    {
        if (is_object($class)) {
            $parents = class_parents($class);
            if (in_array($instance, $parents))
            {
                $this->assert($instance, '===', $parents[$instance]);
            } elseif ($instance === get_class($class)) {
                $this->assert($instance, '===', get_class($class));
            } else {
                $this->assert($instance, '===', $class, "instance of " . $instance, false);
            }
        } else {
            $this->assert($class, '!==', $class, "instance of " . $instance, false);
        }
    }

    function assertImplements($instance, $class)
    {
        if (is_object($class)) {
            $interfaces = class_implements($class);
            if (in_array($instance, $interfaces))
            {
                $this->assert($instance, '===', $interfaces[$instance]);
            } elseif ($instance === get_class($class)) {
                $this->assert($instance, '===', get_class($class));
            } else {
                 $this->assert($instance, '===', $class, "Implimentation of " . $instance, false);
            }
        } else {
            $this->assert($class, '!==', $class, "Implimentation of " . $instance, false);
        }
    }

    function assertException($f)
    {
        $e = null;
        try {
            if (is_callable($f))
                $f();
            
            $e = $f;
        } catch (Exception $e) {
        }

        $this->assertInstanceOf(Exception::class, $e);
    }

    function assertExceptionMessage($msg, $f)
    {
        $e = null;
        try {
            if (is_callable($f))
                $f();

            $this->assert($f, '!==', $f, 'instance of Exception', false);
        } catch (Exception $e) {
            $this->assert($msg, '===', $e->getMessage());
        }
    }    
}
