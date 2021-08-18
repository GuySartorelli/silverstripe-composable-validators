<?php

namespace Signify\ComposableValidators\Tests;

/**
 * A simple class with no __toString method.
 */
class TestUnstringable
{
    private $value = 'A value';

    public function getValue()
    {
        return $this->value;
    }
}
