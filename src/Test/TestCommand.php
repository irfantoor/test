<?php

namespace IrfanTOOR\Test;

use ArgumentCountError;
use Exception;
use IrfanTOOR\Command;
use IrfanTOOR\Test\Constants;

class TestCommand extends Command
{
    protected $root;
    protected $total;

    function __construct($path)
    {
        $this->root = $path;

        parent::__construct(
            Constants::NAME,
            Constants::DESCRIPTION,
            null,
            Constants::VERSION,
            true
        );

        register_shutdown_function([$this, 'shutdown']);

        $this->addOption('f', 'failed',  'Show failed only tests');
        $this->addOption('t', 'testdox', 'Do not print the result of individual tests');
        $this->addOption('q', 'quite',   'Only prints the final result');

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
        $filter  = $this->getOperand('filter');

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
            $filter = '.*Test\.php';
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

                preg_match_all('|' . $filter . '|s', $file, $m);
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

        define('__NOTHING_LEFT_TO_PROCESS__', 1);
    }

    function Shutdown()
    {
        $c = $this->console;
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

            $bg = ($passed !== 0) ? "bg_green" : "bg_black";
            $fg = ($passed !== 0) ? "white" : "dark";

            $c->writeln('');
            $c->write(sprintf("%5d passed ", $passed), [$bg, $fg]);
            $style = ($failed === 0 ? [$bg, $fg] : ['bg_red', 'white']);
            $c->writeln(sprintf("%5d failed ", $failed), $style);
        }
    }
}
