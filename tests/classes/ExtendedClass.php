<?php

namespace Tests;

class ExtendedClass extends SomeClass
{
    function fg()
    {
        return $this->f() . $this->g();
    }

    function gf()
    {
        throw new Exception("Error Processing GF", 1);
    }
}
