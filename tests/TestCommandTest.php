<?php

use IrfanTOOR\Command;
use IrfanTOOR\Test\TestCommand;
use IrfanTOOR\Test;

class TestCommandTest extends Test
{
    function testInstance()
    {
        $cmd = new TestCommand([]);

        $this->assertInstanceOf(IrfanTOOR\Test\TestCommand::class, $cmd);
        $this->assertInstanceOf(IrfanTOOR\Command::class, $cmd);
    }
}
