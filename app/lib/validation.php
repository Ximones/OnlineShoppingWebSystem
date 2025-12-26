<?php

$_err = [];

function validate(array $rules): bool
{
    global $_err;
    $_err = [];

    foreach ($rules as $key => $ruleSet) {
        $value = trim((string) ($_POST[$key] ?? ''));
        foreach ($ruleSet as $rule => $message) {
            if ($rule === 'required' && $value === '') {
                $_err[$key] = $message;
                break;
            }

            if ($rule === 'unique' && is_callable($message)) {
                $result = $message($value);
                if ($result !== true) {
                    $_err[$key] = $result;
                    break;
                }
            }

            if ($rule === 'numeric' && $value !== '' && !is_numeric($value)) {
                $_err[$key] = $message;
                break;
            }

            if (str_starts_with($rule, 'min_value:')) {
                $min = (float) substr($rule, 10);
                if (is_numeric($value) && (float)$value < $min) {
                    $_err[$key] = $message;
                    break;
                }
            }

            if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $_err[$key] = $message;
                break;
            }

            if (str_starts_with($rule, 'min:')) {
                $min = (int) substr($rule, 4);
                if (strlen($value) < $min) {
                    $_err[$key] = $message;
                    break;
                }
            }

            if (str_starts_with($rule, 'max:')) {
                $max = (int) substr($rule, 4);
                if (strlen($value) > $max) {
                    $_err[$key] = $message;
                    break;
                }
            }

            if (str_starts_with($rule, 'same:')) {
                $other = substr($rule, 5);
                if ($value !== ($_POST[$other] ?? '')) {
                    $_err[$key] = $message;
                    break;
                }
            }
        }
    }

    return empty($_err);
}
