<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

declare(strict_types=1);

namespace Cimply\Core\Validator\Types;

trait Strings
{
    /**
     * Check String
     * @param mixed $var
     * @param object $opt  expects: ->min, ->max, ->required
     */
    public function checkStrings($var, object $opt): void
    {
        $this->validateString($var, (int)($opt->min ?? 0), (int)($opt->max ?? 0), (bool)($opt->required ?? false));

        if (!array_key_exists((string)$var, $this->errors)) {
            $this->sanitizeString($var);
        }
    }

    /**
     * Validate a string
     * @param mixed $var
     */
    private function validateString($var, int $min = 0, int $max = 0, bool $required = false): bool
    {
        $key = (string)$var;
        $val = $this->source[$key] ?? null;

        // if not required and empty => ok
        if ($required === false && ($val === null || (is_string($val) && trim($val) === ''))) {
            return true;
        }

        if ($val === null) {
            if ($required) {
                $this->errors[$key] = "[+%0% is required|{$key}+]";
            }
            return false;
        }

        // normalize scalars to string for length checks
        if (is_scalar($val)) {
            $str = (string)$val;
        } else {
            $this->errors[$key] = "[+%0% is an invalid string|{$key}+]";
            return false;
        }

        $len = mb_strlen($str);

        if ($min > 0 && $len < $min) {
            $this->errors[$key] = "[+%0% is to short|{$key}+]";
            return false;
        }

        if ($max > 0 && $len > $max) {
            $this->errors[$key] = "[+%0% is to long|{$key}+]";
            return false;
        }

        return true;
    }

    /**
     * Sanitize a string
     * @param mixed $var
     */
    private function sanitizeString($var): void
    {
        $key = (string)$var;
        $val = $this->source[$key] ?? null;

        // FILTER_SANITIZE_STRING is deprecated in PHP 8.1; use a safe replacement.
        $str = is_scalar($val) ? (string)$val : '';

        $str = strip_tags($str);
        // keep quotes and encode specials
        $this->sanitized[$key] = htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
