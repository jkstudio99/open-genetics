<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OpenGenetics\Core\Validator;

/**
 * 🧬 Unit tests for Validator class.
 */
class ValidatorTest extends TestCase
{
    public function testRequiredPasses(): void
    {
        $v = Validator::make(['name' => 'Alice'], ['name' => 'required']);
        $this->assertTrue($v->passes());
    }

    public function testRequiredFails(): void
    {
        $v = Validator::make([], ['name' => 'required']);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors());
    }

    public function testEmailPasses(): void
    {
        $v = Validator::make(['email' => 'test@example.com'], ['email' => 'required|email']);
        $this->assertTrue($v->passes());
    }

    public function testEmailFails(): void
    {
        $v = Validator::make(['email' => 'not-an-email'], ['email' => 'email']);
        $this->assertTrue($v->fails());
    }

    public function testMinStringPasses(): void
    {
        $v = Validator::make(['pw' => '12345678'], ['pw' => 'min:8']);
        $this->assertTrue($v->passes());
    }

    public function testMinStringFails(): void
    {
        $v = Validator::make(['pw' => '1234'], ['pw' => 'min:8']);
        $this->assertTrue($v->fails());
    }

    public function testMaxStringFails(): void
    {
        $v = Validator::make(['name' => 'a very long name'], ['name' => 'max:5']);
        $this->assertTrue($v->fails());
    }

    public function testInPasses(): void
    {
        $v = Validator::make(['role' => 'admin'], ['role' => 'in:admin,hr,employee']);
        $this->assertTrue($v->passes());
    }

    public function testInFails(): void
    {
        $v = Validator::make(['role' => 'superadmin'], ['role' => 'in:admin,hr,employee']);
        $this->assertTrue($v->fails());
    }

    public function testNumericPasses(): void
    {
        $v = Validator::make(['age' => '25'], ['age' => 'numeric']);
        $this->assertTrue($v->passes());
    }

    public function testNumericFails(): void
    {
        $v = Validator::make(['age' => 'abc'], ['age' => 'numeric']);
        $this->assertTrue($v->fails());
    }

    public function testMultipleRules(): void
    {
        $v = Validator::make(
            ['email' => '', 'password' => '123'],
            ['email' => 'required|email', 'password' => 'required|min:8']
        );
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('email', $v->errors());
        $this->assertArrayHasKey('password', $v->errors());
    }

    public function testConfirmedPasses(): void
    {
        $v = Validator::make(
            ['password' => 'secret123', 'password_confirmation' => 'secret123'],
            ['password' => 'confirmed']
        );
        $this->assertTrue($v->passes());
    }

    public function testConfirmedFails(): void
    {
        $v = Validator::make(
            ['password' => 'secret123', 'password_confirmation' => 'different'],
            ['password' => 'confirmed']
        );
        $this->assertTrue($v->fails());
    }
}
