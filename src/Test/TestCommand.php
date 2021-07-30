<?php

/**
 * IrfanTOOR\Test\TestCommand
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR\Test;

use Exception;
use Throwable;
use IrfanTOOR\Command;
use IrfanTOOR\Test;

/**
 * Test command manages the processing of multiple TestClasses and printing
 * the results of the tests
 */
class TestCommand extends Command
{
    const NAME        = "test";
    const DESCRIPTION = "Irfan's Test : A super fast bare minimum testing suite";

    /**
     * constants describing different assertion statuses
     */
    const FILE_EXCEPTION   = -5; # file including the class could not be loaded
    const METHOD_EXCEPTION = -6; # some method in the file throws an exception

    /** @var string -- root path */
    protected $root;

    /** @var string -- provided relative path */
    protected $path;

    /** @var int -- debug level from 0 to 4 */
    protected $level;

    /** @var string -- individual test */
    protected $itest;

    /** @var string -- filter for the methods to be tested */
    protected $filter;

    /** @var bool -- option to process the results for documentation */
    protected $testdox;

    /** @var bool --  option to process the results quitely */
    protected $quite;

    /** @var bool --  returns the result as an array */
    protected $results_only;

    /** @var array -- assertion results of a single test class */
    protected $result = [];

    /** @var int -- count of passed tests in a single test class */
    protected $passed = 0;

    /** @var int -- count of failed tests in a single test class */
    protected $failed = 0;

    /** @var int -- count of skipped tests in a single test class */
    protected $skipped = 0;

    /** @var int -- total count of skipped methods in a single test class */
    protected $skipped_methods = 0;

    /** @var bool -- wether the test class was skipped, e.g. caused an eception while loading */
    protected $file_skipped = false;

    /** @var array -- last message for testing purpose etc. */
    protected $last_message = [];

    /**
     * @var array -- messages array of all the test, which can be printed at the
     * the end of processing of all tests in a single method
     * Note: only collected and printed, if debug level is 2 or more
     */
    protected $messages = [];

    /* @var int -- total count of skipped files */
    protected $skipped_files = 0;

    /**
     * TestCommand constructor
     *
     * @param string $root Root path
     */
    public function __construct(?string $root = null)
    {
        parent::__construct();

        if (!$root)
            $root = __DIR__;

        $this->root = rtrim($root, "/") . "/";

        $this->setName(self::NAME);
        $this->setDescription(self::DESCRIPTION);
        $this->setVersion(Test::VERSION);
    }

    /**
     * Initializes the options and/or arguments
     */
    protected function init()
    {
        // $this->addOption('f|failed',  'Show failed tests');
        $this->addOption('i|individual', 'Result of an individual test only', '');
        $this->addOption('f|filter',     'Filter the methods to be tested test only', '');
        $this->addOption('r|results',    'Returns the results as array');
        $this->addOption('t|testdox',    'Do not print the result of individual tests');

        $this->addOption('q|quite',   'Only prints the final result');

        $this->addArgument(
            'path',
            'Folder or file containing test files ...',
            self::ARGUMENT_OPTIONAL,
            'tests'
        );
    }

    /**
     * Configure / process options and arguments
     */
    protected function configure()
    {
        $this->level        = $this->getOption('verbose');
        $this->itest        = $this->getOption('individual');
        $this->filter       = $this->getOption('filter');
        // $this->failed_only  = $this->getOption('failed');
        $this->testdox      = $this->getOption('testdox');
        $this->quite        = $this->getOption('quite');
        $this->results_only = $this->getOption('results');

        if ($this->results_only) {
            $this->quite = true;
        }
    }

    /**
     * Return the results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->result;
    }

    /**
     * Called when an assertion passes
     *
     * @param array $message
     */
    protected function processPassed(array $message)
    {
        $this->passed++;
        $this->processDot(".", "green");
    }

    /**
     * Called when an assertion fails
     *
     * @param array $message
     */
    protected function processFailed(array $message)
    {
        $this->failed++;
        $this->processDot("F", "red");
        $this->processMessage($message, "white, bg_red");
    }

    /**
     * Called when an assertion is skipped e.g. an unknown assertion
     * or bad count of arguments is passed
     *
     * @param array $message
     */
    protected function processSkipped(array $message)
    {
        $this->skipped++;
        $this->processDot("S", "yellow");
        $this->processMessage($message, "black, bg_light_yellow");
    }

    /**
     * Called when an method is skipped
     *
     * @param array $message
     */
    protected function processMethodSkipped(array $message)
    {
        $this->skipped_methods++;
        $this->processDot("M", "warning");
        $this->processMessage($message, "warning");
    }

    /**
     * Called when a file is skipped because of some error/exception
     * produced by the class while loading
     *
     * @param array $message
     */
    protected function processFileSkipped(array $message)
    {
        $this->file_skipped = true;
        $this->skipped_files++;
        $this->processDot("X", "bg_red, white");
        $this->processMessage($message, "black, bg_light_yellow");

        $this->writeln();
    }

    /**
     * Called when an exception is thrown by an assertion
     *
     * @param array $message
     */
    protected function processException(array $message)
    {
        $this->skipped++;
        $this->processDot("E", "reverse");
        $this->processMessage($message, "reverse");
    }

    /**
     * Called to print the result of an individual assertion
     *
     * @param string $dot   A character showin the result of an assertion
     * @param string $style Style of color in which to print the dot
     */
    protected function processDot(string $dot, string $style)
    {
        if ($this->testdox || $this->quite)
            return;

        $this->write($dot, $style);
    }

    /**
     * Print the ending summary of assertions in a method
     */
    protected function processEnding()
    {
        if ($this->quite)
            return;

        if ($this->testdox) {
            $this->writeln();
            return;
        }


        if ($this->level && ($this->failed || $this->skipped)) {
            $this->write(" [", "dark");

            $sep = "";
            if ($this->failed) {
                $this->write($this->failed, "red");
                $sep = ", ";
            }

            if ($this->skipped) {
                $this->write($sep);
                $this->write($this->skipped, "yellow");
            }

            $this->write("]", "dark");
        }

        $this->writeln();
    }

    /**
     * Processes a message generated by an assertion in a method
     * note: these are printed for verbose level > 1 and each message with a
     *       specific style
     *
     * @param array  $message
     * @param string $style
     */
    protected function processMessage(array $message, string $style)
    {
        if ($this->testdox || $this->quite)
            return;

        if ($this->level > 1) {
            $text = sprintf(
                " line: %d >> %s ",
                $message['line'],
                $message['message']
            );

            $trace = "";

            if ($this->level > 2) {
                if (isset($message['trace'])) {
                    foreach ($message['trace'] as $t) {
                        $trace .= "line: " . ($t['line'] ?? '') .
                        ", file: " . ($t['file'] ?? '') . "\n";
                    }
                }

                if ($message['status'] === Test::ASSERTION_FAILED) {
                    $args = [];

                    foreach ($message['expected'] as $k => $name) {
                        $args[$name] = $message['args'][$k];
                    }
                }
            }

            $this->messages[] = [
                'text'  => $text,
                'trace' => $trace,
                'style' => $style,
                'args'  => $args ?? null,
            ];
        }
    }

    /**
     * Writes the name of the method
     *
     * @param string $method The method name
     */
    protected function writeMethod(string $method)
    {
        if ($this->quite)
            return;

        $this->write($method . " ", $this->noansi ? null : "green");
    }

    /**
     * Writes number of passed tests
     * Note: It prints the number of passed tests and then dumps the result of
     * all assertions collected in the output buffer
     *
     */
    protected function writePassed()
    {
        if ($this->quite)
            return;

        $this->ob_start();
        $this->write(sprintf(" [%3d] ", $this->passed), $this->passed ? "green" : "red");
        $this->ob_end_flush();
    }

    /**
     * Print the messages, if any after the tests
     * Note: it prints all the processed messages, generated during the call
     *       of a testMethod.
     */
    protected function writeMessages()
    {
        if ($this->quite)
            return;

        foreach ($this->messages as $message) {
            $this->writeln($message['text'], $message['style']);

            if ($this->level > 2) {
                $max = 0;

                foreach ($message['args'] ?? [] as $k => $value) {
                    $max = max($max, strlen($k));
                }

                $max += 2;

                # print the parsed arguments
                foreach ($message['args'] ?? [] as $k => $value) {
                    ob_start();
                    d($message['args'][$k], false);
                    $var = ob_get_clean();
                    $this->write(str_repeat(' ', $max - strlen($k)));
                    $this->write($k . ": " . $var, "magenta");
                }
            }

            if ($message['trace']) {
                $this->writeln($message['trace'], "");
            }
        }

        $this->messages = [];
    }

    /**
     * Returns the last notification message
     */
    public function getLastMessage(): array
    {
        return $this->last_message;
    }

    /**
     * Adds up the results
     *
     * @param array $result Array of assertion results of a testMethod
     */
    protected function accumulateResult(array $result)
    {
        $passed =
        $failed =
        $skipped =
        $skipped_methods = 0;

        foreach ($result as $k => $v) {
            $passed  += $v['passed'] ?? 0;
            $failed  += $v['failed'] ?? 0;
            $skipped += $v['skipped'] ?? 0;
            $skipped_methods += $v['skipped_methods'] ?? 0;
        }

        return [
            'passed' => $passed,
            'failed' => $failed,
            'skipped' => $skipped,
            'skipped_methods' => $skipped_methods,
            'file_skipped' => $this->file_skipped,
        ];
    }

    /**
     * Write a line of summary of passed, failed and skipped tests
     *
     * @param array $result
     */
    protected function writeSummary(array $result)
    {
        extract($result);

        if ($passed) {
            $this->write(sprintf("%5d passed ", $passed), "white, bg_green");
        }

        if ($failed) {
            $this->write(sprintf(" %d failed ", $failed), "white, bg_red");
        }

        if ($skipped) {
            $this->write(sprintf(" %d skipped ", $skipped), "black, bg_light_yellow");
        }

        $this->write(" ");

        if ($skipped_methods) {
            $this->write(" $skipped_methods method" . ($skipped_methods > 1 ? "s" : "")  . " skipped ", "warning");
        }

        if (isset($skipped_files) && $skipped_files) {
            $this->write(" $skipped_files file" . ($skipped_files > 1 ? "s" : "")  . " skipped ", "error");
        }

        $this->writeln();
    }

    /**
     * Test class notify with a notification message, which is processed
     * according to the message status
     *
     * @param array $message
     */
    public function notify(array $message)
    {
        $this->last_message = $message;

        switch ($message['status']) {
            case Test::ASSERTION_PASSED:
                $this->processPassed($message);
                break;

            case Test::ASSERTION_FAILED:
                $message['message'] = "Assertion failed: " . $message['method'];
                $this->processFailed($message);
                break;

            case Test::ARGUMENTS_COUNT_ERROR:
                $message['message'] = "Bad number of arguments: " . $message['method'];
                $this->processSkipped($message);
                break;

            case Test::ASSERTION_UNKNOWN:
                $message['message'] = "Unknown assertion: " . $message['method'];
                $this->processSkipped($message);
                break;

            case Test::ASSERTION_MISMATCH:
                $message['message'] = "Not an assertion: " . $message['method'];
                $this->processSkipped($message);
                break;

            case Test::ASSERTION_EXCEPTION:
                $message['message'] = "Exception: " . $message['message'] . " : " . $message['method'];
                $this->processException($message);
                break;

            case self::METHOD_EXCEPTION:
                $this->processMethodSkipped($message);
                break;

            case self::FILE_EXCEPTION:
                $this->processFileSkipped($message);
                break;
        }
    }

    /**
     * Returns the list of files matching the patern from the given path,
     * or an empty array if it could not find a match
     * @param string $path  Relative path of the folder, e.g. tests
     * @param string $regex Regex to filter the name of the files in the folder
     */
    protected function getFiles(string $path, string $regex): array
    {
        $files = [];

        if (is_dir($this->root . $path)) {
            $d = dir($this->root . $path);

            while (false !== ($file = $d->read())) {
                if ($file === '.' || $file === '..')
                    continue;

                preg_match('|^' . $regex . '$|s', $file, $m);

                if (!isset($m[0][0]))
                    continue;

                $files[] = $file;
            }

            $d->close();
        }

        return $files;
    }

    /**
     * Returns the list of the files to process, according to the passed argument
     * Throws exception if the 'path' argument is not a file or a folder
     *
     * @return array List of files to be tested
     */
    protected function getFilesToProcess(): array
    {
        # find the files to be tested in the provided to default dir
        $this->path = rtrim($this->getArgument('path'), "/") . "/";
        $abs_path = $this->root . rtrim($this->path, "/");

        # find out the files to process
        if (is_dir($abs_path)) {
            return $this->getFiles($this->path, '.*Test\.php');
        } elseif (is_file($abs_path)) {
            $path_info = pathinfo($abs_path);
            $this->path = str_replace($this->root, "", $path_info['dirname'] . "/");
            return [$path_info['basename']];
        } else {
            # consider the path as regex
            $path_info = pathinfo($this->path);
            $this->path = $path_info['dirname'] . "/";
            return $this->getFiles($this->path, str_replace("*", ".*", $path_info['basename']));
        }
    }

    /**
     * Runs a method with an exception expected
     *
     * @param object $class
     * @param string $method
     * @param array  $options
     */
    protected function runMethodWithException(
        $class,
        string $method,
        array $options
    )
    {
        $throws  = $options['throws'] ?? null;
        $message = $options['message'] ?? null;
        $args    = $options['args'] ?? null;

        $throws = "\\" . $throws;
        eval('$throws = ' . $throws . ";");
        $e = null;

        try {
            if ($args) {
                $key = $options[$args[0]] ?? null;
                call_user_func_array([$class, $method], [$key]);
            } else {
                $class->$method();
            }
        } catch(Throwable $e) {
        }

        # verifies the exception class
        $class->assertInstanceOf($throws, $e);

        # verifies the message of exception
        if ($message) {
            if (strpos($message, '{') !== false) {
                foreach ($options as $k => $v) {
                    if (!is_string($v))
                        continue;

                    $message = str_replace('{$' . $k . '}', $v, $message);
                }
            }

            $class->assertEquals($message, $e->getMessage());
        }
    }

    /**
     * Runs a method
     * Note: The assertion results are received as notifications
     *
     * @param object $class
     * @param string $method
     * @param array  $options
     */
    public function runMethod($class, string $method, array $options)
    {
        $throws  = $options['throws'] ?? null;

        if (!isset($options['args'])) {
            if ($throws) {
                $this->runMethodWithException($class, $method, $options);
            } else {
                $class->$method();
            }
        } else {
            foreach ($options['args'] as $k => $v) {
                $key = $v;
                $source = $options[$key] ?? null;

                if (!$source)
                    throw new Exception("Source $key not defined!");

                $source = str_replace('$this', '$class', $source);
                eval('$source = ' . $source . ';');

                foreach ($source as $key) {
                    if ($throws) {
                        $data = $options;
                        $data[$v] = $key;
                        $this->runMethodWithException($class, $method, $data);
                    } else {
                        call_user_func_array([$class, $method], [$key]);
                    }
                }
            }
        }
    }

    /**
     * Runs the unit tests defined in a testMethod of a class and prints the
     * results to output
     *
     * @param object $class
     * @param string $method
     * @param array  $options
     */
    public function runMethodUnitTests($class, string $method, array $options)
    {
        if (!$this->quite)
            $this->ob_start();

        # call method
        try {
            # setup
            $class->setup();

            # write the title (removing the 'test')
            $this->writeMethod(substr($method, 4));

            $this->runMethod($class, $method, $options);
        } catch (Throwable $e) {
            $this->notify(
                [
                    'status'  => self::METHOD_EXCEPTION,
                    'line'    => $e->getLine(),
                    'method'  => $method,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTrace(),
                ]
            );
        }

        # method ending [F:Failed count S:Skipped count]
        $this->processEnding();

        # passed tests are written in the brackets
        $this->writePassed();

        # display the messages for verbosity level 2 (-vv) and onwards
        $this->writeMessages();

        $this->result[$method] = [
            'passed'  => $this->passed,
            'failed'  => $this->failed,
            'skipped' => $this->skipped,
            'skipped_methods' => $this->skipped_methods,
        ];

        # initialize the counters before calling the next method
        $this->passed   = 0;
        $this->failed   = 0;
        $this->skipped  = 0;
        $this->messages = [];
        $this->skipped_methods = 0;
    }

    /**
     * Process all the unit tests defined in the given file
     *
     * @param string $file Filename
     */
    public function runFileUnitTests(string $file)
    {
        # write the file name
        if (!($this->quite || $this->results_only))
            $this->write($file . " ", "info");

        try {
            if (!file_exists($this->root . $this->path . $file))
                throw new Exception("File: $file, does not exist");

            $options = $this->parseFile($this->root . $this->path . $file);

            require $this->root . $this->path . $file;

            $classname = preg_replace(
                '|.*\/|s' ,
                '',
                str_replace('.php' , '', $file)
            );

            # create a Test Class
            $class = new $classname();

            # register this command as a notification sevrer
            # so that every assertion can be recieved and processed
            $class->register($this);

            if (!$this->quite)
                $this->writeln();

            # run the unit tests in the class
            # todo -- transform it into $class->runUnitTests()
            $methods = get_class_methods($class);

            foreach ($methods as $method) {
                # only the methods name starting with "test" e.g. testBlaBla() ...
                if (strpos($method, 'test') !== 0)
                    continue;

                # if individual test is present, skip the rest
                if ($this->itest !== "") {
                    $i = strtolower("test" . preg_replace('|^test|', '', $this->itest));

                    if ($i != strtolower($method))
                        continue;
                }

                # if filter is present, filterout the methods not to be tested
                if ($this->filter !== "") {
                    preg_match(
                        '|.*' . strtolower($this->filter) . '.*|Us',
                        strtolower($method),
                        $m
                    );

                    if (!$m)
                        continue;
                }

                $m_options = $options[$method] ?? [];
                $this->runMethodUnitTests($class, $method, $m_options);
            }

            // $class->runUnitTests();
        } catch (Throwable $e) {
            $this->notify(
                [
                    'status'  => self::FILE_EXCEPTION,
                    'line'    => $e->getLine(),
                    'message' => $e->getMessage(),
                ]
            );

            $this->writeMessages();
        }

        # collect summary of file, (will be printed if verbosity level > 0)
        $summary = $this->accumulateResult($this->result);

        # save for this file
        $this->total[] = $summary;

        if ($this->level > 0) {
            if ($summary['file_skipped']) {
                $summary['skipped_files'] = 1;
            }

            unset($summary['file_skipped']);

            if (!$this->quite)
                $this->writeSummary($summary);
        }

        $this->result = [];
        $this->file_skipped = false;

        if (!$this->quite) {
            $this->writeln();
        }
    }

    /**
     * Parses arguments in a comment line
     *
     * @param string $line Comment line, containing the args
     * @return array key and associated value of parsed argument
     */
    protected function parseArgs(string $args): array
    {
        $regex = '|\$(\w+)|Us';
        preg_match_all($regex, $args, $m);

        if (!$m)
            return [];

        return $m[1];
    }

    /**
     * Parse a comment like this one, starting with /** and ending with /
     * Following are two arguments:
     * throws: Exception
     * message: The message thrown by the exception
     *
     * @param string $comment Comment to be parsed
     * @return array All of the arguments found in the comment
     */
    protected function parseComment(string $comment): array
    {
        $data = [];
        preg_match_all('|\*\s*(\w*)\s*:\s(.*)\n|Us', $comment, $m);

        if (!$m)
            return $data;

        foreach ($m[1] as $k => $v) {
            $data[$v] = $m[2][$k];
        }

        return $data;
    }

    /**
     * Parse all of the comments from the contents of a file
     *
     * @param string $contents Contents of a PHP file
     * @return array An associative array of function => comments is returned
     */
    protected function parseComments(string $contents): array
    {
        $regex = '|\/\*\*(.*)\*\/.*function\s*(\w.*)\(|Us';
        preg_match_all($regex, $contents, $m);

        if (!$m)
            return [];

        $comments = [];

        foreach ($m[2] as $k => $v) {
            preg_match('|^test.*$|Us', $v, $mm);

            if (!$mm)
                continue;

            $comments[$v] = $this->parseComment($m[1][$k]);
        }

        return $comments;
    }

    /**
     * Returns the methods containing the Unit tests, all these methods
     * starts with the name test e.g. testMethod
     *
     * @param $contents Contents of the PHP file
     */
    protected function parseMethods(string $contents)
    {
        $regex = '|function\s*(test\w+)\s?\((.*)\)|Us';
        preg_match_all($regex, $contents, $m);

        if (!$m)
            return null;

        $methods = [];

        foreach ($m[1] as $k => $v) {
            $name = trim($v);

            if ($m[2][$k]) {
                $methods[$name] = [
                    'args' => $this->parseArgs($m[2][$k]),
                ];
            } else {
                $methods[$name] = [];
            }
        }

        return $methods;
    }

    /**
     * Parses a PHP file
     *
     * @param string $file Absolute path of the $file
     * @return array An associative array of $method => $options is returned
     */
    protected function parseFile(string $file): array
    {
        $contents = file_get_contents($file);

        $options = array_merge_recursive(
            $this->parseMethods($contents),
            $this->parseComments($contents)
        );

        return $options;
    }

    /**
     * Main function consists of finding the test classes and processing each
     * file, and then printing a summry of totals at the end
     */
    public function main()
    {
        if (!$this->results_only) {
            $this->title();
            $this->writeln(Test::DESCRIPTION, "dark");
            $this->writeln();
        }

        $files = $this->getFilesToProcess();

        # run the unit test for all the files from the previous steps
        foreach ($files as $file) {
            $this->runFileUnitTests($file);
        }

        # only print the title "total" if more than one files are tested
        if ($this->level > 0 && count($this->total) > 1) {
            if (!$this->quite) {
                $this->writeln(" total: ", "dark");
            }
        }

        $summary = $this->accumulateResult($this->total);

        # print the totals
        if (
            !$this->results_only
            && (
                $this->quite
                || !$this->level
                || (
                    $this->level
                    && count($this->total) > 1
                )
            )
        ) {
            unset($summary['file_skipped']);
            $summary['skipped_files'] = $this->skipped_files;
            $this->writeSummary($summary);
            $this->writeln();
        }

        return $summary;
    }
}
