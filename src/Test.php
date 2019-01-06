<?php

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\Console;

class Test
{
    protected $options;

    protected $count, $passed;
    protected $tcount, $tpassed;
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
            } catch (Exception $e) {

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

            if ($this->options['failed'] || !$this->options['testdox']) {
                foreach ($this->traces as $t) {
                    $tt = $t['trace'];
                    $c->writeln("   " . $tt['file'] . " -- [line: " .$tt['line']. "]", 'yellow');
                    if (isset($t['info'])) {
                        $tt = $t['info'];
                        $c->write("expected: ", 'yellow');
                        if ($tt[2]) {
                            echo $tt[2] . ' ';
                        } else {
                            if (is_object($tt[0])) {
                                echo explode("\n", print_r($tt[0], 1))[0];
                            } else {
                                var_dump($tt[0]);    
                            }
                        }
                        $c->write("returned: ", 'yellow');
                        if (is_object($tt[1])) {
                            echo explode("\n", print_r($tt[1], 1))[0];
                        } else {
                            var_dump($tt[1]);    
                        }
                    }
                }
            }

            $this->traces = [];
        }

        return [
            $this->tcount,
            $this->tpassed
        ];
    }

    function assert($expected, $exp, $result, $type = null)
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
            $t = debug_backtrace();
            $this->traces[] = [
                'trace' => $t[1],
                'info' => [$expected, $result, $type],
            ];
            if (!$this->options['testdox'])
                echo "F";
            return false;
        }
    }

    function assertEquals($exp, $res)
    {
        $this->assert($exp, '==', $res);
    }    

    function assertNotEquals($exp, $res)
    {
        $this->assert($exp, '!=', $res);
    }

    function assertSame($exp, $res)
    {
        $this->assert($exp, '===', $res);
    }

    function assertNotSame($exp, $res)
    {
        $this->assert($exp, '!==', $res);
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
            $this->assert($var, '!==', $var, 'string');
        }
    }

    function assertNotString($var)
    {
        if (!is_string($var)) {
            $this->assert(false, '===', is_string($var));
        } else {
            $this->assert($var, '!==', $var, 'non_string');
        }
    }

    function assertObject($var)
    {
        if (is_object($var)) {
            $this->assert(true, '===', is_object($var));
        } else {
            $this->assert($var, '!==', $var, 'object');
        }
    }

    function assertNotObject($var)
    {
        if (!is_object($var)) {
            $this->assert(false, '===', is_object($var));
        } else {
            $this->assert($var, '!==', $var, 'non_object');
        }
    }

    function assertInstanceOf($instance, $class)
    {
        if (is_object($class)) {
            $parents = class_parents($class);
            if (in_array($instance, $parents))
            {
                $this->assert($instance, '===', $parents[$instance]);
            } else {
                $this->assert($instance, '===', get_class($class));
            }
        } else {
            $this->assert($class, '!==', $class, 'class');
        }
    }

    function assertImplements($instance, $class)
    {
        if (is_object($class)) {
            $interfaces = class_implements($class);
            if (in_array($instance, $interfaces))
            {
                $this->assert($instance, '===', $interfaces[$instance]);
            } else {
                $this->assert($instance, '===', get_class($class));
            }
        } else {
            $this->assert($class, '!==', $class, 'class');
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

            $this->assert($f, '!==', $f, 'exception');
        } catch (Exception $e) {
            $this->assert($msg, '===', $e->getMessage());
        }
    }    
}
