<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Input Validator
 *
 * Fluent validation for request data.
 *
 * Rules: required, email, min, max, numeric, in, regex, confirmed, url,
 *        date, boolean, array, nullable, integer, string, bail,
 *        unique:table,column
 *
 * Usage:
 *   $v = Validator::make($body, [
 *       'email'    => 'required|email',
 *       'password' => 'required|min:8',
 *       'role'     => 'in:admin,hr,employee',
 *       'username' => 'required|unique:users,username',
 *   ]);
 *   if ($v->fails()) Response::error('Validation failed', 422, $v->errors());
 *
 * Custom messages:
 *   Validator::make($body, $rules, [
 *       'email.required' => 'กรุณากรอกอีเมล',
 *       'email.email'    => 'รูปแบบอีเมลไม่ถูกต้อง',
 *   ]);
 *
 * bail — stop on first failure for a field:
 *   'password' => 'bail|required|min:8'
 */
final class Validator
{
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];

    private function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data     = $data;
        $this->rules    = $rules;
        $this->messages = $messages;
    }

    /**
     * Create a new validator instance and run validation.
     *
     * @param array $messages Custom error messages keyed as "field.rule"
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        $instance = new self($data, $rules, $messages);
        $instance->runValidation();
        return $instance;
    }

    /**
     * Validate and halt with 422 if failed — shorthand for endpoints.
     */
    public static function check(array $data, array $rules, array $messages = []): array
    {
        $v = self::make($data, $rules, $messages);
        if ($v->fails()) {
            Response::error('Validation failed', 422, $v->errors());
        }
        return $data;
    }

    public function fails(): bool  { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }

    // ─── Rule Methods ─────────────────────────────────

    private function ruleRequired(string $field, mixed $value, array $params): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return $this->msg($field, 'required', "{$field} is required.");
        }
        return null;
    }

    private function ruleEmail(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $this->msg($field, 'email', "{$field} must be a valid email address.");
        }
        return null;
    }

    private function ruleMin(string $field, mixed $value, array $params): ?string
    {
        $min = (int) ($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) < $min) {
            return $this->msg($field, 'min', "{$field} must be at least {$min} characters.");
        }
        if (is_numeric($value) && $value < $min) {
            return $this->msg($field, 'min', "{$field} must be at least {$min}.");
        }
        return null;
    }

    private function ruleMax(string $field, mixed $value, array $params): ?string
    {
        $max = (int) ($params[0] ?? PHP_INT_MAX);
        if (is_string($value) && mb_strlen($value) > $max) {
            return $this->msg($field, 'max', "{$field} must not exceed {$max} characters.");
        }
        if (is_numeric($value) && $value > $max) {
            return $this->msg($field, 'max', "{$field} must not exceed {$max}.");
        }
        return null;
    }

    private function ruleNumeric(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            return $this->msg($field, 'numeric', "{$field} must be a number.");
        }
        return null;
    }

    private function ruleIn(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !\in_array((string) $value, $params, true)) {
            return $this->msg($field, 'in', "{$field} must be one of: " . implode(', ', $params) . ".");
        }
        return null;
    }

    private function ruleRegex(string $field, mixed $value, array $params): ?string
    {
        $pattern = $params[0] ?? '';
        if ($value !== null && $value !== '' && !preg_match($pattern, (string) $value)) {
            return $this->msg($field, 'regex', "{$field} format is invalid.");
        }
        return null;
    }

    private function ruleConfirmed(string $field, mixed $value, array $params): ?string
    {
        $confirmValue = $this->data[$field . '_confirmation'] ?? null;
        if ($value !== $confirmValue) {
            return $this->msg($field, 'confirmed', "{$field} confirmation does not match.");
        }
        return null;
    }

    private function ruleString(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && !is_string($value)) {
            return $this->msg($field, 'string', "{$field} must be a string.");
        }
        return null;
    }

    private function ruleInteger(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            return $this->msg($field, 'integer', "{$field} must be an integer.");
        }
        return null;
    }

    private function ruleUrl(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            return $this->msg($field, 'url', "{$field} must be a valid URL.");
        }
        return null;
    }

    private function ruleDate(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && $value !== '') {
            $format = $params[0] ?? 'Y-m-d';
            $d = \DateTime::createFromFormat($format, (string) $value);
            if (!$d || $d->format($format) !== (string) $value) {
                return $this->msg($field, 'date', "{$field} must be a valid date (format: {$format}).");
            }
        }
        return null;
    }

    private function ruleBoolean(string $field, mixed $value, array $params): ?string
    {
        $valid = [true, false, 1, 0, '1', '0', 'true', 'false'];
        if ($value !== null && $value !== '' && !\in_array($value, $valid, true)) {
            return $this->msg($field, 'boolean', "{$field} must be true or false.");
        }
        return null;
    }

    private function ruleArray(string $field, mixed $value, array $params): ?string
    {
        if ($value !== null && !\is_array($value)) {
            return $this->msg($field, 'array', "{$field} must be an array.");
        }
        return null;
    }

    /**
     * nullable — allows null/empty to pass all subsequent rules.
     */
    private function ruleNullable(string $field, mixed $value, array $params): ?string
    {
        if ($value === null || $value === '') {
            $this->errors[$field . '__nullable_skip'] = true;
        }
        return null;
    }

    /**
     * bail — stop validation for this field after the first failure.
     * Place at the beginning of the rule list: 'bail|required|min:8'
     */
    private function ruleBail(string $field, mixed $value, array $params): ?string
    {
        // Handled in runValidation — this is a no-op rule marker
        return null;
    }

    /**
     * unique:table,column — check DB uniqueness.
     * Optionally ignore a row: unique:table,column,ignoreId,idColumn
     *
     * 'email' => 'required|unique:users,email'
     * 'email' => 'required|unique:users,email,42,id'   // ignore user 42
     */
    private function ruleUnique(string $field, mixed $value, array $params): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $table  = $params[0] ?? $field;
        $column = $params[1] ?? $field;

        $sql    = "SELECT COUNT(*) as cnt FROM `{$table}` WHERE `{$column}` = ?";
        $binds  = [$value];

        // Optional ignore row: unique:users,email,42,id
        if (isset($params[2]) && $params[2] !== '') {
            $idColumn = $params[3] ?? 'id';
            $sql     .= " AND `{$idColumn}` != ?";
            $binds[]  = $params[2];
        }

        try {
            $row   = Database::queryOne($sql, $binds);
            $count = (int) ($row['cnt'] ?? 0);
            if ($count > 0) {
                return $this->msg($field, 'unique', "{$field} has already been taken.");
            }
        } catch (\Throwable $e) {
            // DB failure must not silently allow duplicate data through
            error_log('[OpenGenetics Validator] unique check failed: ' . $e->getMessage());
            throw $e;
        }

        return null;
    }

    // ─── Core Validation Loop ─────────────────────────

    private function runValidation(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = \is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            $hasBail = \in_array('bail', $rules, true);

            foreach ($rules as $rule) {
                if ($rule === 'bail') continue; // skip — already handled via $hasBail

                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'rule' . ucfirst($rule);
                if (!method_exists($this, $method)) continue;

                $error = $this->$method($field, $value, $params);

                if ($error !== null) {
                    $this->errors[$field][] = $error;

                    // Stop field on required failure or bail modifier
                    if ($rule === 'required' || $hasBail) break;
                }

                // Skip remaining rules if nullable and value is empty
                if (isset($this->errors[$field . '__nullable_skip'])) {
                    unset($this->errors[$field . '__nullable_skip']);
                    break;
                }
            }

            // Clean up nullable skip sentinel (in case no error triggered unset)
            unset($this->errors[$field . '__nullable_skip']);
        }
    }

    // ─── Custom Message Lookup ────────────────────────

    /**
     * Resolve a custom message for field.rule, or fall back to default.
     *
     * @param string $field   Field name
     * @param string $rule    Rule name (e.g. 'required', 'email')
     * @param string $default Default message if no custom message defined
     */
    private function msg(string $field, string $rule, string $default): string
    {
        return $this->messages["{$field}.{$rule}"] ?? $this->messages[$rule] ?? $default;
    }
}
