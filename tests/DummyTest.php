<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    public function testDummyTrueIsTrue(): void
    {
        $this->assertTrue(true);
    }

    public function testDummyAddition(): void
    {
        $this->assertSame(2, 1 + 1);
    }
}
