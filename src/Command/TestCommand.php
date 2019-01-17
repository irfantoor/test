<?php

namespace IrfanTOOR\Test\Command;

use IrfanTOOR\Command;
use Exception;
use ArgumentCountError;

class TestCommand extends Command
{
    protected $root;
    protected $error;

    protected $total;

    function __construct($path)
    {
        $this->root = $path;
        $this->error = true;

        parent::__construct(
            'test',
            'test with a twist',
            null,
            null,
            true
        );

        register_shutdown_function([$this, 'shutdown']);

        $this->addOption('f', 'failed', 'Show failed only tests');
        $this->addOption('t', 'testdox', 'Do not print the result of individual tests');
        $this->addOption('q', 'quite',  'Only prints the final result');

        $this->addOperand(
            'filter', 
            'Folder or file containing test files',
            self::ARGUMENT_OPTIONAL,
            'tests'
        );
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

        $this->writeln([$this->name . ' ' . $this->version, $this->description], ['blue', 'bold']);

        $quite   = $this->getOption('quite');
        $failed  = $this->getOption('failed');
        $testdox = $this->getOption('testdox');

        $filter = $this->getOperand('filter');

        $c = $this->console;

        $this->total = [
            'passed' => 0,
            'failed' => 0,
        ];

        if ($quite) {
            ob_start();
        }

        if (is_dir($this->root . $filter)) {
            $path = rtrim($filter, '/') . '/';
            $filter = '*Tests.php';
            $files = null;
        } elseif (is_file($this->root . $filter)) {
            $path = "";
            $files = [$filter];
            $filter = null;
        } else {
            $path = "";
            $files = null;
        }

        if (!$files) {
            $files = [];
            $d = dir($this->root . $path);
            while (false !== ($file = $d->read())) {
                if ($file === '.' || $file === '..')
                    continue;

                preg_match_all('|.*\.php|s', $file, $m);
                if (!isset($m[0][0]))
                    continue;

                $files[] = $file;
            }

            $d->close();
        }

        foreach ($files as $file) {
            $c->writeln($file);

            require $this->root . $path . $file;

            $class = str_replace('.php' , '', $file);
            $class = preg_replace('|.*\/|s' , '', $class);
            $test = new $class($this->options);

            $r = $test->run();

            $passed = $failed = 0;
            foreach($r as $m) {
                $passed += $m['passed'];
                $failed += $m['failed'];
            }

            $this->total['passed'] += $passed;
            $this->total['failed'] += $failed;
        }

        if ($quite) {
            ob_get_clean();
        }

        $this->error = false;
    }

    function Shutdown()
    {
        $c = $this->console;

        if (($this->getOption('verbose') === 0) && $this->error) {
            $last_error = error_get_last();
            if ($last_error) {
                extract($last_error);

                #$c->write(" (skipped) ", 'blue');
                $c->write('Fatal error -- line: ' . $line . ' >> ', ['bg_red', 'white']);
                $short_message = explode(',', $message)[0];
                $c->writeln("$short_message", ['bg_red', 'white']);
            }
        }

        if ($this->total) {
            extract($this->total);

            $bg = ($passed !== 0) ? "bg_green" : "bg_black";
            $fg = ($passed !== 0) ? "white" : "dark";

            $c->writeln('');
            $c->write(sprintf("%5d passed ", $passed), [$bg, $fg]);
            $style = ($failed === 0 ? [$bg, $fg] : ['bg_red', 'white']);
            $c->writeln(sprintf("%5d failed ", $failed), $style);
        }
    }
}
