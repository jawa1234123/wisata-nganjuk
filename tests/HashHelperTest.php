<?php

namespace Tests;

use App\HashHelper;
use PHPUnit\Framework\TestCase;

class HashHelperTest extends TestCase
{
    public function testGenerateHashReturnsString()
    {
        $hash = HashHelper::generateHash('secret-password');

        $this->assertIsString($hash);
        $this->assertStringStartsWith('$2y$', $hash);
    }

    public function testVerifyHashReturnsTrueForValidPassword()
    {
        $password = 'secret-password';
        $hash = HashHelper::generateHash($password);

        $this->assertTrue(HashHelper::verifyHash($password, $hash));
    }

    public function testVerifyHashReturnsFalseForInvalidPassword()
    {
        $password = 'secret-password';
        $hash = HashHelper::generateHash($password);

        $this->assertFalse(HashHelper::verifyHash('wrong-password', $hash));
    }
}
