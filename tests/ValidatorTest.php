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

    public function testUrlPasses(): void
    {
        $v = Validator::make(['site' => 'https://example.com'], ['site' => 'url']);
        $this->assertTrue($v->passes());
    }

    public function testUrlFails(): void
    {
        $v = Validator::make(['site' => 'not-a-url'], ['site' => 'url']);
        $this->assertTrue($v->fails());
    }

    public function testDatePasses(): void
    {
        $v = Validator::make(['dob' => '2000-01-15'], ['dob' => 'date']);
        $this->assertTrue($v->passes());
    }

    public function testDateFails(): void
    {
        $v = Validator::make(['dob' => '15-01-2000'], ['dob' => 'date']);
        $this->assertTrue($v->fails());
    }

    public function testDateCustomFormat(): void
    {
        $v = Validator::make(['dob' => '15/01/2000'], ['dob' => 'date:d/m/Y']);
        $this->assertTrue($v->passes());
    }

    public function testBooleanPasses(): void
    {
        foreach (['1', '0', 'true', 'false', true, false, 1, 0] as $val) {
            $v = Validator::make(['flag' => $val], ['flag' => 'boolean']);
            $this->assertTrue($v->passes(), "Expected boolean pass for: " . var_export($val, true));
        }
    }

    public function testBooleanFails(): void
    {
        $v = Validator::make(['flag' => 'yes'], ['flag' => 'boolean']);
        $this->assertTrue($v->fails());
    }

    public function testArrayPasses(): void
    {
        $v = Validator::make(['tags' => ['a', 'b']], ['tags' => 'array']);
        $this->assertTrue($v->passes());
    }

    public function testArrayFails(): void
    {
        $v = Validator::make(['tags' => 'not-array'], ['tags' => 'array']);
        $this->assertTrue($v->fails());
    }

    public function testNullableSkipsOtherRules(): void
    {
        // nullable|email should pass when value is empty
        $v = Validator::make(['email' => ''], ['email' => 'nullable|email']);
        $this->assertTrue($v->passes());
    }

    public function testNullableWithValueStillValidates(): void
    {
        // nullable|email should fail when value is present but invalid
        $v = Validator::make(['email' => 'bad-email'], ['email' => 'nullable|email']);
        $this->assertTrue($v->fails());
    }

    public function testIntegerPasses(): void
    {
        $v = Validator::make(['count' => '42'], ['count' => 'integer']);
        $this->assertTrue($v->passes());
    }

    public function testIntegerFails(): void
    {
        $v = Validator::make(['count' => '3.14'], ['count' => 'integer']);
        $this->assertTrue($v->fails());
    }

    public function testStringPasses(): void
    {
        $v = Validator::make(['name' => 'Alice'], ['name' => 'string']);
        $this->assertTrue($v->passes());
    }

    public function testStringFails(): void
    {
        $v = Validator::make(['name' => 123], ['name' => 'string']);
        $this->assertTrue($v->fails());
    }

    public function testArrayRulesFormat(): void
    {
        // Rules can be passed as array instead of pipe-string
        $v = Validator::make(['email' => 'x'], ['email' => ['required', 'email']]);
        $this->assertTrue($v->fails());
    }
}
