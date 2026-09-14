<?php
class Validator
{
    private array $errors = [];

    private function __construct(private array $data, private array $rules, private array $messages = [])
    {
        $this->validate();
    }

    public static function make(array $data, array $rules, array $messages = []): static
    {
        return new static($data, $rules, $messages);
    }

    public function fails(): bool   { return !empty($this->errors); }
    public function passes(): bool  { return empty($this->errors); }
    public function errors(): array { return $this->errors; }

    public function first(): string
    {
        foreach ($this->errors as $errs) return $errs[0] ?? '';
        return '';
    }

    public function firstFor(string $field): string { return $this->errors[$field][0] ?? ''; }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleStr) {
            $value = trim((string)($this->data[$field] ?? ''));
            $label = ucfirst(str_replace('_', ' ', $field));
            foreach (explode('|', $ruleStr) as $rule) {
                $param = null;
                if (str_contains($rule, ':')) [$rule, $param] = explode(':', $rule, 2);
                $err = $this->check($rule, $field, $value, $label, $param);
                if ($err) { $this->errors[$field][] = $err; if ($rule === 'required') break; }
            }
        }
    }

    private function check(string $rule, string $field, string $value, string $label, ?string $param): ?string
    {
        $mk = "{$field}.{$rule}";
        return match($rule) {
            'required'          => $value === ''  ? ($this->messages[$mk] ?? "{$label} is required.") : null,
            'email'             => $value && !filter_var($value, FILTER_VALIDATE_EMAIL) ? ($this->messages[$mk] ?? "{$label} must be a valid email.") : null,
            'url'               => $value && !filter_var($value, FILTER_VALIDATE_URL)   ? ($this->messages[$mk] ?? "{$label} must be a valid URL.") : null,
            'numeric'           => $value && !is_numeric($value) ? ($this->messages[$mk] ?? "{$label} must be a number.") : null,
            'min'               => $value && mb_strlen($value) < (int)$param ? ($this->messages[$mk] ?? "{$label} must be at least {$param} characters.") : null,
            'max'               => $value && mb_strlen($value) > (int)$param ? ($this->messages[$mk] ?? "{$label} must not exceed {$param} characters.") : null,
            'min_val'           => $value && (float)$value < (float)$param   ? ($this->messages[$mk] ?? "{$label} must be at least {$param}.") : null,
            'max_val'           => $value && (float)$value > (float)$param   ? ($this->messages[$mk] ?? "{$label} must not exceed {$param}.") : null,
            'in'                => $value && !in_array($value, explode(',', $param ?? ''), true) ? ($this->messages[$mk] ?? "{$label} is invalid.") : null,
            'confirmed', 'same' => !isset($this->data[$param]) || ($this->data[$param] ?? '') !== $value ? ($this->messages[$mk] ?? "{$label} confirmation does not match.") : null,
            'regex'             => $value && !preg_match($param, $value) ? ($this->messages[$mk] ?? "{$label} format is invalid.") : null,
            default             => null,
        };
    }

    public function validated(): array
    {
        $out = [];
        foreach ($this->rules as $field => $_)
            if (!isset($this->errors[$field]) && isset($this->data[$field]))
                $out[$field] = trim((string)$this->data[$field]);
        return $out;
    }
}
