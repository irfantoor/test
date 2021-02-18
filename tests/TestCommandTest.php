<?php

use IrfanTOOR\Command;
use IrfanTOOR\Test\TestCommand;
use IrfanTOOR\Test;

class TestCommandTest extends Test
{
    function testInstance()
    {
        $cmd = new TestCommand();

        $this->assertInstanceOf(TestCommand::class, $cmd);
        $this->assertInstanceOf(Command::class, $cmd);
    }

    function _testSkipMethod()
    {
        // throws exception ...
        call_unknown();
    }

    function testCanSkipAMethodThrowingException()
    {
        $cmd = new TestCommand();

        ob_start();

        try {
            $cmd->runMethodUnitTests($this, '_testSkipMethod', []);
        } catch (Throwable $e) {
        }

        ob_get_clean();

        $notification = $cmd->getLastMessage();

        $this->assertEquals(
            $cmd::METHOD_EXCEPTION,
            $notification['status']
        );

        $this->assertEquals(
            "Call to undefined function call_unknown()",
            $notification['message']
        );
    }

    function testCanSkipAnAbsentFile()
    {
        $cmd = new TestCommand();

        $cmd->ob_start();

        try {
            $cmd->runFileUnitTests('UnknownFile.php');
        } catch (Throwable $e) {
        }

        $cmd->ob_get_clean();

        $notification = $cmd->getLastMessage();

        $this->assertEquals(
            $cmd::FILE_EXCEPTION,
            $notification['status']
        );

        $this->assertEquals(
            "File: UnknownFile.php, does not exist",
            $notification['message']
        );
    }

    function testCanSkipAFileThrowingException()
    {
        $cmd = new TestCommand(__DIR__);

        $cmd->ob_start();

        try {
            $cmd->runFileUnitTests('/classes/SkipFileTest.php');
        } catch (Throwable $e) {
        }

        $cmd->ob_get_clean();

        $notification = $cmd->getLastMessage();

        $this->assertEquals(
            $cmd::FILE_EXCEPTION,
            $notification['status']
        );

        $this->assertEquals(
            "Call to undefined function unknown_method()",
            $notification['message']
        );
    }

    # todo -- tests concerning all of the options -vftqr ...
}
