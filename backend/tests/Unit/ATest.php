<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ATest extends TestCase
{
    public function testUserName()
    {
        $this->assertEquals('John', 'John');
    }
}
