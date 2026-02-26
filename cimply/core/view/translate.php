<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht.
 * Contact: direkt@route-media.info. All rights reserved.
*/

namespace Cimply\Core\View {

    use Cimply\System\Helpers as Helper;

    class Translate
    {
        use \Properties, \Cast;

        protected static ?array $pattern = null;

        public function __construct($pattern = null)
        {
            self::$pattern = \is_array($pattern) ? $pattern : null;
        }

        public final function Cast($mainObject, $selfObject = self::class): self
        {
            return self::Cull($mainObject, $selfObject);
        }

        public function OnPropertyChanged(): void
        {
            self::$staticProperties = $this;
        }

        private static function normalizeToString($value): string
        {
            if ($value === null) {
                return '';
            }
            if (\is_string($value) || \is_int($value) || \is_float($value) || \is_bool($value)) {
                return (string)$value;
            }
            if (\is_object($value) && \method_exists($value, '__toString')) {
                return (string)$value;
            }
            if (\is_array($value)) {
                $json = \json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return $json !== false ? $json : '';
            }
            $json = \json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json !== false ? $json : '';
        }

        // Get Translation
        public static function GetTranslastion($s = "", $langCode = 'de_DE'): string
        {
            $s = self::normalizeToString($s);

            $pattern = self::$pattern['Trans'] ?? null;
            if (!\is_string($pattern) || $pattern === '') {
                return $s;
            }

            $matches = [];
            \preg_match_all($pattern, $s, $matches);

            if (!isset($matches[1]) || !\is_array($matches[1]) || \count($matches[1]) === 0) {
                return $s;
            }

            foreach ($matches[1] as $i => $value) {
                $trans = self::WordTranslation($value, $langCode);
                if ($trans !== null && $trans !== '') {
                    $s = \str_replace($matches[0][$i], $trans, $s);
                }
            }

            return $s;
        }

        public static function WordTranslation($value, $langCode = 'de_DE'): string
        {
            $value = self::normalizeToString($value);

            $translation = (array)self::GetStaticProperty('Translations');

            if (\array_key_exists($value, $translation)) {
                $entry = $translation[$value];
                if (\is_array($entry) && \array_key_exists($langCode, $entry)) {
                    return self::normalizeToString($entry[$langCode]);
                }
            }

            if (\is_array($translation) && \array_key_exists($langCode, $translation)) {
                return self::normalizeToString($translation[$langCode]);
            }

            return $value;
        }

        public static function Translation($value, $trans = null): string
        {
            if ($trans === null) {
                $trans = self::GetStaticProperty('Translastions');
            }

            $translation = self::GetTranslastion($value);

            $explString = \explode(' ', $translation);
            $last = \end($explString);
            $counts = \strlen((string)$last);

            $replacesWord = [];

            $transArray = \is_array($trans) ? $trans : [];
            $transEnd = \end($transArray);

            if (!\is_array($transEnd)) {
                return $translation;
            }

            foreach ($transEnd as $key => $val) {
                $keyStr = self::normalizeToString($key);

                if ($counts > 0 && \substr($keyStr, 0, $counts) === ($explString[0] ?? '')) {
                    $percent = 0.0;
                    if (\similar_text($keyStr, $translation, $percent) && $percent >= 50) {
                        $trimResult = \explode(' ', $keyStr);

                        foreach ($trimResult as $k => $v) {
                            if (isset($explString[$k]) && ($v != $explString[$k])) {
                                $i = \str_replace('%', '', (string)$v);
                                $replacesWord[$i] = self::WordTranslation($explString[$k]);
                            }
                        }

                        $valEnd = \end($val);
                        if (\is_string($valEnd) && $valEnd !== '') {
                            $translation = \vsprintf($valEnd, $replacesWord);
                        }
                    }
                }
            }

            return $translation;
        }
    }
}
