<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
*/

declare(strict_types=1);

namespace Cimply\System;

use ReflectionClass;
use ReflectionMethod;
use SimpleXMLElement;

class Helpers
{
    /** @var mixed */
    public static $response = null;

    public static string $namespace = 'default';
    public static string $project = 'default';

    /** @var array<string|int, mixed> */
    public static array $vars = [];

    public static function Setter($key = null, $var = null, ?string $filter = null, bool $overwrite = false): void
    {
        if ($key === null || $var === null) {
            return;
        }

        $existing = (is_string($key) || is_int($key)) && array_key_exists($key, self::$vars) ? (self::$vars[$key] ?? null) : null;

        if (class_exists(\ArrayParser::class) && method_exists(\ArrayParser::class, 'FilterArray')) {
            $var = \ArrayParser::FilterArray($var, $filter);
        }

        if (is_array($existing) && !$overwrite) {
            if ($filter !== null) {
                $filtered = (is_array($var) && isset($var[$filter]) && is_array($var[$filter])) ? $var[$filter] : null;
                self::$vars[$key] = is_array($filtered) ? array_merge($existing, $filtered) : $existing;
                return;
            }

            self::$vars[$key] = is_array($var) ? array_merge($existing, $var) : $existing;
            return;
        }

        if (is_string($key) || is_int($key)) {
            self::$vars[$key] = $var;
        }
    }

    public static function Getter($key, bool $isArray = true)
    {
        $value = self::$vars[$key] ?? null;
        return $isArray ? [$key => $value] : $value;
    }

    public static function GetItems($key = null, $subkey = null, bool $explicitly = false)
    {
        if ($explicitly) {
            return ($key !== null && $subkey !== null && isset(self::$vars[$key]) && is_array(self::$vars[$key]) && array_key_exists($subkey, self::$vars[$key]))
                ? self::$vars[$key][$subkey]
                : null;
        }

        if ($key !== null && array_key_exists($key, self::$vars)) {
            if ($subkey !== null && is_array(self::$vars[$key]) && array_key_exists($subkey, self::$vars[$key])) {
                return self::$vars[$key][$subkey];
            }
            return self::$vars[$key];
        }

        return $key !== null ? null : self::$vars;
    }

    public static function GetUnique(string $varName = ''): mixed
    {
        if ($varName === '') {
            return null;
        }

        if (isset($_SESSION[$varName])) {
            $result = $_SESSION[$varName];
            self::ClearSession($varName);
            return $result;
        }

        return null;
    }

    public static function GetGlobal(string $varName): mixed
    {
        return $_SESSION[$varName] ?? null;
    }

    public static function SetSession(string $varName, mixed $varValue): void
    {
        if ($varName === '') {
            return;
        }

        if (isset($_SESSION[$varName]) && is_array($_SESSION[$varName]) && is_array($varValue)) {
            $_SESSION[$varName] = array_merge($_SESSION[$varName], $varValue);
            return;
        }

        $_SESSION[$varName] = $varValue;
    }

    public static function GetSession(string $varName = '', $key = null): mixed
    {
        if ($varName === '' || !isset($_SESSION[$varName])) {
            return null;
        }

        if ($key !== null && is_array($_SESSION[$varName])) {
            return $_SESSION[$varName][$key] ?? null;
        }

        return $_SESSION[$varName];
    }

    public static function ClearSession(string $varName = ''): void
    {
        if ($varName === '') {
            if (!isset($_SESSION) || !is_array($_SESSION)) {
                return;
            }

            foreach (array_keys($_SESSION) as $key) {
                if (($_SESSION[$key] ?? null) !== 'project') {
                    unset($_SESSION[$key]);
                }
            }
            return;
        }

        if (array_key_exists($varName, $_SESSION)) {
            unset($_SESSION[$varName]);
        }
    }

    public function SetBaseDir(string $dir = ''): ?string
    {
        if ($dir === '/' || $dir === '') {
            return null;
        }

        if (property_exists($this, 'conf') && property_exists($this, 'baseDir')) {
            /** @var array<string,mixed> $conf */
            $conf = $this->conf ?? [];
            $symlink = (bool)($conf['symlink'] ?? false);

            if ($symlink) {
                $baseDir = (string)($conf['baseDir'] ?? '');
                if ($baseDir !== '' && ($this->baseDir ?? null) === $baseDir) {
                    $this->baseDir = '/' . $baseDir . '/';
                }
                $this->baseDir = (string)($this->baseDir ?? '') . $dir;
                return $this->baseDir;
            }
        }

        return null;
    }

    public function __invoke(): mixed
    {
        if (!property_exists($this, 'callable') || !property_exists($this, 'args')) {
            return null;
        }

        $callable = $this->callable;
        $args = is_array($this->args ?? null) ? $this->args : [];

        return call_user_func_array($callable, array_merge($args, func_get_args()));
    }

    public static function Invoke($name = null, ?string $class = null, ?string $method = null, $data = [], $viewModel = null)
    {
        if ($class === null) {
            return null;
        }

        $fqcn = str_replace(['/', '\\\\'], ['\\', '\\'], $class);

        if (!class_exists($fqcn)) {
            return null;
        }

        $ref = new ReflectionClass($fqcn);
        if ($ref->isAbstract()) {
            return false;
        }

        $objClass = new $fqcn($data, $viewModel);

        if ($method === null || $method === '') {
            $method = '__construct';
        } else {
            if (class_exists(\Annotation::class) && method_exists(\Annotation::class, 'RouteClass')) {
                self::Setter('Annotate', \Annotation::RouteClass($fqcn, $method) ?? []);
            }
        }

        $parseData = is_array($data)
            ? $data
            : (class_exists(\JsonDeEncoder::class) && method_exists(\JsonDeEncoder::class, 'Decode')
                ? (\JsonDeEncoder::Decode($data, true) ?? [])
                : []);

        if (!is_array($parseData)) {
            $parseData = [];
        }

        foreach ($parseData as $k => $v) {
            if (is_array($v)) {
                self::Setter($k, $v);
            } else {
                if (!property_exists($objClass, 'parameter') || !is_array($objClass->parameter ?? null)) {
                    $objClass->parameter = [];
                }
                $objClass->parameter[$k] = $v;
            }
        }

        return self::CallFunction($objClass, $method, $parseData);
    }

    private static function CallFunction(object $objClass, string $method, array $parseData = []): mixed
    {
        $reflection = new ReflectionMethod($objClass, $method);

        if (!$reflection->isPublic()) {
            return "expects parameter 1 to be a valid callback, cannot access private method '{$method}()'";
        }

        $result = call_user_func_array([$objClass, $method], $parseData);

        $objClass->$method = $result;

        $path = self::GetItems('CurrentObject', 'databinding');
        if (is_array($path)) {
            if (
                isset($path['name'], $path['filetype'], $path['callback']) &&
                isset($result) &&
                method_exists(self::class, 'SetStorage')
            ) {
                self::SetStorage($path['name'] . '.' . $path['filetype'], $result, $path['callback']);
            } else {
                $type = self::GetItems('CurrentObject', 'type');
                self::Callback($result, $type);
            }
        }

        return $objClass->$method;
    }

    public static function Callback($result, $type = true, ?string $key = null): mixed
    {
        if ($result === null || $result === '' || $result === []) {
            return false;
        }

        switch ($type) {
            case 'config':
                return json_encode(self::GetItems(), JSON_UNESCAPED_UNICODE);

            case 'xml':
                $xml = new SimpleXMLElement('<root/>');
                if (is_array($result)) {
                    array_walk_recursive($result, [$xml, 'addChild']);
                }
                echo $xml->asXML();
                return null;

            case 'yml':
                return class_exists(\YamlParser::class) && method_exists(\YamlParser::class, 'ArrayToYAML')
                    ? \YamlParser::ArrayToYAML($result)
                    : null;

            case 'json':
                header('Content-Type: application/json');
                die(json_encode($result, JSON_UNESCAPED_UNICODE));

            case '_json':
                header('Content-Type: application/json');
                return json_encode($result, JSON_UNESCAPED_UNICODE);

            case 'localstorage':
                return json_encode($result, JSON_UNESCAPED_UNICODE);

            case 'jsVar':
                $safeKey = $key ?? 'data';
                $val = is_scalar($result) ? (string)$result : json_encode($result, JSON_UNESCAPED_UNICODE);
                return "<script> var {$safeKey} = '" . addslashes((string)$val) . "'; console.log({$safeKey});</script>";

            case 'console':
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                if ($result) {
                    exit;
                }
                return null;

            case 'html':
                return htmlentities((string)$result);

            case 'text':
                return strip_tags((string)$result);

            case 'serialize':
                return serialize($result);

            case 'unserialize':
                return is_string($result) ? @unserialize($result) : null;

            case 'array':
                return is_array($result) ? $result : [$result];

            case 'yaml':
                if (method_exists(self::class, 'ArrayToYAML')) {
                    return self::ArrayToYAML($result);
                }
                return class_exists(\YamlParser::class) && method_exists(\YamlParser::class, 'ArrayToYAML')
                    ? \YamlParser::ArrayToYAML($result)
                    : null;

            case 'object':
                echo (string)$result;
                return null;

            case 'trim':
                return trim((string)$result, '"');

            case 'list':
                // IMPORTANT: no parent:: here (Helpers has no parent)
                $tb = self::Invoke('result', 'TableBuilder', 'buildTable', (array)$result);
                return (is_object($tb) && isset($tb->result)) ? $tb->result : null;

            case 'clean':
                return $result;

            default:
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                return null;
        }
    }

    private static function determiningClass(string $code): array
    {
        $classes = [];
        $tokens  = token_get_all($code);
        $count   = count($tokens);

        for ($i = 2; $i < $count; $i++) {
            if (
                is_array($tokens[$i - 2]) && $tokens[$i - 2][0] === T_NAMESPACE &&
                is_array($tokens[$i - 1]) && $tokens[$i - 1][0] === T_WHITESPACE &&
                is_array($tokens[$i]) && $tokens[$i][0] === T_STRING
            ) {
                $classes[] = (string)$tokens[$i][1];
            }

            if (
                is_array($tokens[$i - 2]) && $tokens[$i - 2][0] === T_CLASS &&
                is_array($tokens[$i - 1]) && $tokens[$i - 1][0] === T_WHITESPACE &&
                is_array($tokens[$i]) && $tokens[$i][0] === T_STRING
            ) {
                $classes[] = (string)$tokens[$i][1];
            }
        }

        return $classes;
    }
}
