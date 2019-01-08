<?php

namespace IrfanTOOR\Test\Command;

use IrfanTOOR\Command;
use Exception;
use ArgumentCountError;

class TestCommand extends Command
{
    protected $root;

    function __construct($path)
    {
        $this->root = $path;

        parent::__construct(
            'test',
            'testing without a twist',
            null,
            '0.1',
            true
        );

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


        $this->writeln($this->name . ' ' . $this->version, 'bold');

        $quite   = $this->getOption('quite');
        $failed  = $this->getOption('failed');
        $testdox = $this->getOption('testdox');

        $filter = $this->getOperand('filter');

        $c = $this->console;

        $count = [0, 0];

        if ($quite)
            ob_start();

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
            $test = new $class(compact(['quite', 'failed', 'testdox']));

            $r = $test->run();

            $count[0] += $r[0];
            $count[1] += $r[1];

        }

        if ($quite)
            ob_get_clean();

        $c->writeln('');
        $c->write(sprintf("%5d passed ", $count[1]), ['bg_green', 'white', 'bold']);
        $style = ($count[0] == $count[1] ? ['bg_green', 'white'] : ['bg_red', 'white']);
        $c->writeln(sprintf(" %d failed ", ($count[0] - $count[1])), $style);
    }
}
