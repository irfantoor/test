<?php

use IrfanTOOR\Command;
use IrfanTOOR\Test\TestCommand;
use IrfanTOOR\Test;

class TestCommandTest extends Test
{
    function testInstance()
    {
        $cmd = new TestCommand([]);

        $this->assertInstanceOf(TestCommand::class, $cmd);
        $this->assertInstanceOf(Command::class, $cmd);
    }
}
