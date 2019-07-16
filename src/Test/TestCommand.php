<?php

namespace IrfanTOOR\Test;

use Exception;
use IrfanTOOR\{
    Command,
    Test
};

class TestCommand extends Command
{
    protected $root;
    protected $total;

    function __construct($path)
    {
        $this->root = $path;

        parent::__construct([
            'name'        => Test::NAME,
            'description' => Test::DESCRIPTION,
            'version'     => Test::VERSION,
        ]);

        register_shutdown_function([$this, 'shutdown']);

        $this->addOption('f', 'failed',  'Show failed only tests');
        $this->addOption('t', 'testdox', 'Do not print the result of individual tests');
        $this->addOption('q', 'quite',   'Only prints the final result');

        $this->addArgument(
            'path',
            'Folder or file containing test files ...',
            self::ARGUMENT_OPTIONAL,
            'tests'
        );
    }

    private function _limitVar($var)
    {
        ob_start();
        var_dump($var);

        $result = ob_get_clean();
        $lines = explode("\n", $result);
        $line = $lines[0];
        $l = strlen($line);
        $line = substr($line, 0, 50);

        if ($l >= 50) {
            $line .= ' ...';
        }

        if (is_string($var) && $l > 50)
            $line .= '"';

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

    function shorten($path)
    {
        $pa = explode('/', $path);
        $p = array_pop($pa);
        return array_pop($pa) . '/' . $p;
    }

    function error($lines)
    {
        $c = $this->console;
        $max = 0;
        $l = 2;

        foreach ($lines as $v) {
            $max = max($max, strlen($v));
            $l--;
            if ($l === 0)
                break;
        }

        $c->writeln('');
        $i = 0;

        foreach ($lines as $k => $v) {
            $pad = str_repeat(' ', max(0, $max - strlen($v)));
            if ($i<=1) {
                $c->write('       ');
                $c->write(' ', 'bg_red');
                $c->writeln(' ' . $v . $pad . ' ', ['bg_yellow', 'black']);
                $i++;
            } else {
                $c->write('         ');
                $c->writeln($v, ['dark']);
            }
        }
    }

    function dump($file, $name = null, $result = null, $style = "none")
    {
        static $prevfile = '';
        static $prevname = '';

        $c = $this->console;
        $q = $this->getOption('quite');
        $f = $this->getOption('failed');
        $t = $this->getOption('testdox');
        $v = $this->getOption('verbose');

        if (!is_array($result)) {
            $this->total['skipped'] += 1;
            $skipped = true;
            $passed  = $failed  = 0;
            $out = "(skipped)";
            $style = "red";
        } else {
            extract($result);
            $this->total['count']  += $count;
            $this->total['passed'] += $passed;
            $failed = $count !== $passed;
            $skipped = false;
        }

        if ($q) {
            return;
        } else {
            if (!$f || ($f && $failed) || ($f && $skipped)) {
            } else {
                return;
            }
        }

        if ($file !== $prevfile) {
            $prevfile = $file;
            $prevname = '';

            $c->writeln();
            $c->writeln($file, 'info');
        }

        if ($name && ($name !== $prevname)) {
            $prevname = $name;

            if (!is_array($result)) {
                $c->write('  [  0] ' . $name . ' ', 'red');
            } else {
                if ($count === $passed) {
                    $c->write(sprintf("  [%3d] $name ", $passed), 'green');
                } else {
                    $c->write("  [", 'red');
                    if ($passed) {
                        $c->write(sprintf("%3d", $passed), 'green');
                    } else {
                        $c->write(sprintf("%3d", $passed), 'red');
                    }
                    $c->write("] $name ", 'red');
                }
            }
        }

        if ($result) {
            if (!is_array($result)) {
                $e = $result;
                $c->write("(skipped)", 'red');
                if ($v === 0) {
                    # versbosity = 0
                    $short_message = explode(',', $e->getMessage())[0];
                    $c->write(' Exception: ' . $short_message . ' ', 'dark');
                } else {
                    # versbosity = 1
                    $lines = [
                        $e->getMessage(),
                        $this->shorten($e->getFile()) . ' line[' . $e->getLine() . ']'
                    ];

                    # versbosity = 2 or more
                    if ($v > 1) {
                        foreach ($e->getTrace() as $t) {
                            if (isset($t['file'])) {
                                $lines[] = $this->shorten($t['file']) . ' line[' . $t['line'] .']';
                            }
                        }
                    }

                    $this->error($lines);
                }
            } else {
                if (!$t) {
                    foreach ($result as $r) {
                        if ($r === '.') {
                            $c->write('.', 'green');
                        } else {
                            $c->write('F', 'red');
                        }
                    }

                    # verbosity = 0
                    # no detail of Failed test

                    if ($v) {
                        foreach ($result as $r) {
                            if ($r === '.')
                                continue;

                            # verbosity = 1
                            extract($r);

                            $l = [];
                            $l[0] = 'expected: ';

                            if (strpos($message, '%s')) {
                                $message = str_replace('%s', $this->_limitVar($expected), $message);
                            }

                            $l[0] .= $message ? $message . ' ' : '';
                            $l[1] = 'returned: ';
                            $l[1] .= $this->_limitVar($var);
                            $l[1] .= " -> line: " . $trace[1]['line'];

                            # verbosity = 2 or more
                            if ($v > 1
                                && (
                                    (is_array($expected) && is_array($var))
                                    || (is_string($expected) && is_string($var))
                                )
                            ) {
                                if (is_string($expected) && is_string($var))
                                {
                                    $e = explode("\n", $expected);
                                    $vv = explode("\n", $var);

                                    $d1 = array_diff($e, $vv);
                                    $d2 = array_diff($vv, $e);

                                    foreach ($d1 as $k => $d) {
                                        $l[] = "expected: " . json_encode($d);
                                        $l[] = "returned: " . json_encode($d2[$k]);
                                    }
                                } else {
                                    $l[] = "expected: " . json_encode($expected);
                                    $l[] = "returned: " . json_encode($var);
                                }
                            }

                            $this->error($l);
                        }
                    }
                }
            }
        }

        $this->writeln();
    }

    function main()
    {
        # verbose
        $v = $this->getOption('verbose');

        if ( $v === 0)
            error_reporting(0);
        elseif ($v === 1)
            error_reporting(E_ALL && ~E_NOTICE);
        else
            error_reporting(E_ALL);

        $this->writeln($this->name . ' ' . $this->version, "info");

        $c = $this->console;

        $this->total = [
            'count'   => 0,
            'passed'  => 0,
            'failed'  => 0,
            'skipped' => 0
        ];

        $path = $this->getArgument('path');

        if (is_dir($this->root . $path)) {
            $path = rtrim($path, '/') . '/';
            $filter = '.*Test\.php';
            $files = null;
        } elseif (is_file($this->root . $path)) {
            $files = [$path];
            $path = "";
            $filter = null;
        } else {
            $files = null;
        }

        if (!$files) {
            $files = [];
            $d = dir($this->root . $path);

            while (false !== ($file = $d->read())) {
                if ($file === '.' || $file === '..')
                    continue;

                preg_match_all('|' . $filter . '|s', $file, $m);

                if (!isset($m[0][0]))
                    continue;

                $files[] = $file;
            }

            $d->close();
        }

        foreach ($files as $file) {
            require $this->root . $path . $file;

            $class   = str_replace('.php' , '', $file);
            $class   = preg_replace('|.*\/|s' , '', $class);
            $test    = new $class($this->options);
            $methods = get_class_methods($test);

            foreach ($methods as $method) {
                preg_match_all('|test.*|uS', $method, $m);

                if (!isset($m[0][0]))
                    continue;

                $name = substr($method, 4);
                $test->setup();
                $skipped = false;

                try {
                    $e = null;
                    ob_start();
                    $test->$method();
                } catch (\Exception $e) {
                } catch (\Error $e) {
                }

                ob_get_clean();
                $result = $test->getResult();

                if ($e) {
                    $result = $e;
                }

                $this->dump($file, $name, $result);
            }
        }

        define('__NOTHING_LEFT_TO_PROCESS__', 1);
    }

    function Shutdown()
    {
        $c = $this->console;

        # Some error occurred ...
        if (!defined('__NOTHING_LEFT_TO_PROCESS__')) {
            $last_error = error_get_last();

            if ($last_error) {
                extract($last_error);
                $short_message = explode(',', $message)[0];
                $fa   = explode('/', $file);
                $file = array_pop($fa);
                $file = array_pop($fa) . '/' . $file;
                $c->writeln($short_message . ' >> file: ' . $file. ', line: ' . $line, ['bg_red', 'white']);
            }
        }

        if ($this->total) {
            extract($this->total);

            $failed = $count - $passed;

            $c->writeln();

            foreach (['passed', 'failed', 'skipped'] as $t) {

                $style = (
                    ($t == 'passed' && $$t === 0) || ($t !== 'passed' && $$t !== 0)
                    ? ["bg_red", "white"]
                    : ["bg_green", "white"]
                );

                if ($$t)
                    $c->write(sprintf(" %d $t ", $$t), $style);
            }

            $c->writeln();
        }
    }
}
