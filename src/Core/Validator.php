<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Input Validator
 *
 * Fluent validation for request data.
 * Supports: required, email, min, max, numeric, in, regex, confirmed, url, date, boolean, array, nullable, integer, string.
 *
 * Usage:
 *   $v = Validator::make($body, [
 *       'email'    => 'required|email',
 *       'password' => 'required|min:8',
 *       'role'     => 'in:admin,hr,employee',
 *   ]);
 *   if ($v->fails()) Response::error('Validation failed', 422, $v->errors());
 */
final class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    private function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    /**
     * Create a new validator instance and run validation.
     */
    public static function make(array $data, array $rules): self
    {
        $instance = new self($data, $rules);
        $instance->runValidation();
        return $instance;
    }

    /**
     * Validate and halt with 422 if failed — shorthand for endpoints.
     */
    public static function check(array $data, array $rules): array
    {
        $v = self::make($data, $rules);
        if ($v->fails()) {
            Response::error('Validation failed', 422, $v->errors());
        }
        return $data;
    }

    /**
     * Check if validation failed.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get validation errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    // ─── Rule Methods ─────────────────────────────────

    private function ruleRequired(string $field, mixed $value, array $params): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return "{$field} is required.";
        }
        return null;
    }

    private function ruleEmail(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "{$field} must be a valid email address.";
        }
        return null;
    }

    private function ruleMin(string $field, mixed $value, array $params): ?string
    {
        $min = (int) ($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) < $min) {
            return "{$field} must be at least {$min} characters.";
        }
        if (is_numeric($value) && $value < $min) {
            return "{$field} must be at least {$min}.";
        }
        return null;
    }

    private function ruleMax(string $field, mixed $value, array $params): ?string
    {
        $max = (int) ($params[0] ?? PHP_INT_MAX);
        if (is_string($value) && mb_strlen($value) > $max) {
            return "{$field} must not exceed {$max} characters.";
        }
        if (is_numeric($value) && $value > $max) {
            return "{$field} must not exceed {$max}.";
        }
        return null;
    }

    private function ruleNumeric(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            return "{$field} must be a number.";
        }
        return null;
    }

    private function ruleIn(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !in_array((string) $value, $params, true)) {
            return "{$field} must be one of: " . implode(', ', $params) . ".";
        }
        return null;
    }

    private function ruleRegex(string $field, mixed $value, array $params): ?string
    {
        $pattern = $params[0] ?? '';
        if ($value !== null && $value !== '' && !preg_match($pattern, (string) $value)) {
            return "{$field} format is invalid.";
        }
        return null;
    }

    private function ruleConfirmed(string $field, mixed $value, array $params): ?string
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        if ($value !== $confirmValue) {
            return "{$field} confirmation does not match.";
        }
        return null;
    }

    private function ruleString(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && !is_string($value)) {
            return "{$field} must be a string.";
        }
        return null;
    }

    private function ruleInteger(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "{$field} must be an integer.";
        }
        return null;
    }

    private function ruleUrl(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            return "{$field} must be a valid URL.";
        }
        return null;
    }

    private function ruleDate(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '') {
            $format = $params[0] ?? 'Y-m-d';
            $d = \DateTime::createFromFormat($format, (string) $value);
            if (!$d || $d->format($format) !== (string) $value) {
                return "{$field} must be a valid date (format: {$format}).";
            }
        }
        return null;
    }

    private function ruleBoolean(string $field, mixed $value, array $params): ?string
    {
        $valid = [true, false, 1, 0, '1', '0', 'true', 'false'];
        if ($value !== null && $value !== '' && !in_array($value, $valid, true)) {
            return "{$field} must be true or false.";
        }
        return null;
    }

    private function ruleArray(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && !is_array($value)) {
            return "{$field} must be an array.";
        }
        return null;
    }

    /**
     * nullable — allows null/empty values to pass all subsequent rules.
     * Place first: 'nullable|email'
     */
    private function ruleNullable(string $field, mixed $value, array $params): ?string
    {
        if ($value === null || $value === '') {
            // Signal to skip remaining rules for this field
            $this->errors[$field . '__nullable_skip'] = true;
        }
        return null;
    }

    private function runValidation(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'rule' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $error = $this->$method($field, $value, $params);
                    if ($error !== null) {
                        $this->errors[$field][] = $error;
                        if ($rule === 'required') break;
                    }
                    // Skip remaining rules if nullable and value is empty
                    if (isset($this->errors[$field . '__nullable_skip'])) {
                        unset($this->errors[$field . '__nullable_skip']);
                        break;
                    }
                }
            }
        }
    }
}
