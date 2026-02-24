<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

declare(strict_types=1);

namespace Cimply\Core\Validator\Types;

trait Json
{
    /**
     * Check Json
     * @param mixed  $var
     * @param object $opt expects: ->min, ->max, ->required
     */
    public function checkJson($var, object $opt): void
    {
        $this->validateJson($var, (int)($opt->min ?? 0), (int)($opt->max ?? 0), (bool)($opt->required ?? false));

        if (!array_key_exists((string)$var, $this->errors)) {
            $this->sanitizeJson($var);
        }
    }

    /**
     * Validate a JSON string
     * @param mixed $var
     */
    private function validateJson($var, int $min = 0, int $max = 0, bool $required = false): bool
    {
        $key = (string)$var;
        $val = $this->source[$key] ?? null;

        // if not required and empty => ok
        if ($required === false && ($val === null || (is_string($val) && trim($val) === ''))) {
            return true;
        }

        if ($val === null) {
            if ($required) {
                $this->errors[$key] = $key . ' [+is required+]';
            }
            return false;
        }

        if (!is_string($val)) {
            $this->errors[$key] = $key . ' [+is invalid+]';
            return false;
        }

        $len = mb_strlen($val);

        if ($min > 0 && $len < $min) {
            $this->errors[$key] = $key . ' [+is too short+]';
            return false;
        }

        if ($max > 0 && $len > $max) {
            $this->errors[$key] = $key . ' [+is too long+]';
            return false;
        }

        // JSON validity check (only when non-empty)
        $trim = trim($val);
        if ($trim !== '') {
            json_decode($trim, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->errors[$key] = $key . ' [+is invalid JSON+]';
                return false;
            }
        }

        return true;
    }

    /**
     * Sanitize a JSON string (keep as-is if valid/checked; default to {})
     * @param mixed $var
     */
    private function sanitizeJson($var): void
    {
        $key = (string)$var;
        $val = $this->source[$key] ?? null;

        $this->sanitized[$key] = is_string($val) && trim($val) !== '' ? $val : '{}';
    }
}
