<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

namespace Cimply\Core\View {

    use Cimply\System\Helpers as Helper;
    use Cimply\Core\{
        Document\Dom,
        Request\Uri\UriManager
    };
    use Cimply\Interfaces\Support\Enum\{
        PatternSettings,
        CryptoSettings,
        RootSettings,
        SystemSettings,
        AppSettings
    };
    use Cimply\Interfaces\IProperty;

    class View implements IProperty
    {
        use \Properties, \Cast, \Files;

        private static array $vars = [];
        private static int $ttl = 1;
        private static string $view = "";

        protected ?Scope $scope = null;

        public static string $mimeType = 'x-conference/x-cooltalk';
        public static bool $externalFile = true;

        public function __construct(?Scope $scope = null)
        {
            $this->scope = $scope;
        }

        final public static function Cast($mainObject, $selfObject = self::class): self
        {
            return self::Cull($mainObject, $selfObject);
        }

        public function OnPropertyChanged(): void
        {
            self::$staticProperties = $this;
        }

        public static function GetTemplateArgs($tpl = null): ?array
        {
            $result = null;
            $path = [];

            if ($modul = self::GetModules($tpl)) {
                foreach ($modul as $key => $value) {
                    $path = \is_string($key)
                        ? ['file' => $key, 'attr' => $value]
                        : ['file' => $value];
                }

                if (!isset($path['file']) || !\is_string($path['file']) || $path['file'] === '') {
                    return \array_merge(['filePath' => null], $path);
                }

                $fileInfo = new UriManager($path['file']);
                $extension = $fileInfo->getFileType();
                $basename = \str_replace('_', DIRECTORY_SEPARATOR, $fileInfo->getFileBasename());
                $baseFile = self::GetStaticProperty(AppSettings::ASSETS) . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . $basename;

                $result = (\is_file($baseFile) && (self::GetStaticProperty(AppSettings::CLIENTFILESALLOW) == true))
                    ? $baseFile
                    : self::GetStaticProperty(AppSettings::PROJECTPATH) . DIRECTORY_SEPARATOR . $baseFile;

                self::$externalFile = false;
            }

            return \array_merge(['filePath' => $result], $path);
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

        // Set Parameters
        public function setTemplateParams($params): array
        {
            $msg = [];

            if (!\is_array($params)) {
                return $msg;
            }

            foreach ($params as $key => $val) {
                if (\is_array($val)) {
                    foreach ($val as $k => $v) {
                        $msg[$key][$k] = $v;
                    }
                } else {
                    $msg[$key] = $val;
                }
            }

            return $msg;
        }

        // Get Parameter
        public static function GetVar(string $s, string $key = '')
        {
            return self::$vars[$key . $s] ?? null;
        }

        // Set Value
        public static function SetVar(?string $key = null, $value = null): void
        {
            if ($key !== null) {
                self::$vars[$key] = $value;
            }
        }

        // Set Parameters
        public static function SetVars($params = null, string $key = ''): void
        {
            if (!\is_array($params)) {
                return;
            }

            foreach ($params as $k => $v) {
                if ($v !== null && $v !== '' && $v !== []) {
                    self::$vars[$key . $k] = $v;
                }
            }
        }

        // Get Parameters
        public static function GetVars(): ?array
        {
            return self::$vars;
        }

        public function isExternalFile(): bool
        {
            return self::$externalFile;
        }

        public static function GetPattern(string $key): ?string
        {
            return PatternSettings::isValidValue($key) ? self::GetStaticProperty($key) : null;
        }

        /**
         * Create
         */
        public static function Create($template = null): ?string
        {
            $tplArgs = self::GetTemplateArgs($template);
            $filePath = $tplArgs['filePath'] ?? null;

            if (\is_string($filePath) && $filePath !== '' && \is_file($filePath)) {
                self::$mimeType = \Mime::GetMime($tplArgs['file'] ?? '');
                $cacheFile = self::GetStaticProperty(AppSettings::CACHEDIR) . DIRECTORY_SEPARATOR . 'tmp_' . md5($filePath);

                $iFiletime = self::HasFileCached($cacheFile) ? (int)\filemtime($cacheFile) : 0;
                $template = ($iFiletime > (\time() - (10 * self::$ttl)))
                    ? self::GetFileContent($cacheFile)
                    : self::$staticProperties->cacheFile($filePath, $cacheFile);

                if (isset($tplArgs['attr'])) {
                    $template = Dom::SetAttrFromArray($template, $tplArgs['attr']);
                }
            }

            return $template ?? null;
        }

        /**
         * Render View
         */
        public static function Render($template = null, $passthru = false, $encode = null): void
        {
            if (!\headers_sent() && isset(self::$mimeType) && \is_string(self::$mimeType)) {
                \header('Content-type: ' . self::$mimeType);
            }

            if (\is_array($template)) {
                $template = self::EncodeBeforeRendering($template);
            } else {
                $template = self::ParseTplVars($template);
            }

            self::Show($template ?? self::$view, $passthru, $encode);
        }

        /**
         * Convert ViewParsing
         */
        public static function EncodeBeforeRendering(array $template = []): string
        {
            $k = \array_key_first($template);
            if ($k === null) {
                return \JsonDeEncoder::Encode($template);
            }

            $template[$k] = self::ParseTplVars($template[$k]);
            return \JsonDeEncoder::Encode($template);
        }

        public static function Show($body = "", $loopThrough = false, $secure = null, $mime = null): string
        {
            $scope = Scope::Cast(self::GetStaticProperty('scope'));
            $fileType = $scope->getType();
            $mimeType = \Mime::GetMime('.' . $fileType, true) ?? self::$mimeType;

            $caching = !$scope->getCaching()
                ? $scope->getCaching()
                : !\in_array($fileType, (array)self::GetStaticProperty(SystemSettings::USENOTCACHINGFOR), true);

            $toTranslation = self::GetStaticProperty(SystemSettings::USENOTTRANSLATIONFOR);
            $toTranslationArr = \is_array($toTranslation) ? $toTranslation : [];

            if (empty($toTranslationArr) || !\in_array($scope->getType(), $toTranslationArr, true)) {
                $body = Translate::GetTranslastion($body);
            }

            $body = self::ParseTplVars($body);

            if ($secure === 1 || $secure === true) {
                $body = (function ($body) use ($scope, $fileType, $mimeType, $caching) {
                    $salt = self::GetStaticProperty(CryptoSettings::SALT);
                    $pepper = md5(\time() . self::GetStaticProperty(CryptoSettings::PEPPER));

                    return \JsonDeEncoder::Encode([
                        "Scope"   => $scope,
                        "fileTyp" => $fileType,
                        "mimeTyp" => $mimeType,
                        "hash"    => $pepper,
                        "caching" => $caching,
                        "data"    => (\Crypto::Encrypt($body, $salt, $pepper))
                    ]);
                })($body);
            }

            if ($mime) {
                \header("Content-Type: {$mime}");
            }

            $output = self::normalizeToString($body);

            if (!$loopThrough) {
                die($output);
            }

            return $output;
        }

        public static function ParseTplVars($template = null, $vars = null): ?string
        {
            if ($template === null) {
                return null;
            }

            $template = self::normalizeToString($template);
            $pattern = self::GetStaticProperty(PatternSettings::PARAM);

            if (!\is_string($pattern) || $pattern === '') {
                return $template;
            }

            $matches = [];
            \preg_match_all($pattern, $template, $matches);

            if (!isset($matches[1]) || \count($matches[1]) === 0) {
                return $template;
            }

            $sourceVars = \is_array($vars) ? $vars : self::$vars;

            foreach ($matches[1] as $idx => $name) {
                if (!\is_string($name) || $name === '') {
                    continue;
                }

                if (\array_key_exists($name, $sourceVars)) {
                    $raw = $sourceVars[$name];
                } elseif (\array_key_exists($name, self::$vars)) {
                    $raw = self::$vars[$name];
                } else {
                    continue;
                }

                $replacement = Translate::WordTranslation(self::normalizeToString($raw)) ?? '';
                $template = \str_replace($matches[0][$idx], $replacement, $template);
            }

            return $template;
        }

        public static function ParseTplView($template = null): string
        {
            if ($template === null) {
                return '';
            }

            $template = self::normalizeToString($template);
            $pattern = self::GetStaticProperty(PatternSettings::MODUL);

            if (!\is_string($pattern) || $pattern === '') {
                return $template;
            }

            $matches = [];
            \preg_match_all($pattern, $template, $matches);

            if (isset($matches[1]) && \count($matches[1]) > 0) {
                foreach ($matches[1] as $key => $value) {
                    $template = \str_replace($matches[0][$key], self::Create($matches[0][$key]) ?? '', $template);
                }
            }

            return $template;
        }

        public static function ParseTplAttr($element = null): ?array
        {
            if ($element === null) {
                return null;
            }

            $pattern = self::GetStaticProperty(PatternSettings::ATTR);
            if (!\is_string($pattern) || $pattern === '') {
                return null;
            }

            $matches = [];
            \preg_match_all($pattern, self::normalizeToString($element), $matches);

            $result = $matches[0] ?? null;

            if (isset($matches['name'][0]) && $matches['name'][0] !== '' && isset($matches['value'][0]) && \count($matches[1] ?? []) > 0) {
                $explAttr = \explode(',', $matches['value'][0]);
                $remove = ["\"", "'"];

                foreach ($explAttr as $value) {
                    $v = \explode('=', \ltrim($value), 2);
                    if (!isset($v[0], $v[1])) {
                        continue;
                    }
                    $result[$matches['name'][0]][$v[0]] = \str_replace($remove, '', $v[1]);
                }
            }

            return $result;
        }

        public static function GetModules($s): ?array
        {
            if (!self::GetPattern(PatternSettings::MODUL)) {
                throw new \Exception("Modul-Pattern " . PatternSettings::MODUL . " not found.");
            }

            $matches = [];
            \preg_match_all(self::GetPattern(PatternSettings::MODUL), self::normalizeToString($s), $matches);

            if (!empty($matches[1])) {
                return self::ParseTplAttr($matches[1][0]);
            }

            return null;
        }

        // Set Parameters
        public static function Assign($value): void
        {
            if (!\is_array($value)) {
                return;
            }

            $vars = [];
            foreach ($value as $key => $val) {
                if (\is_array($val)) {
                    foreach ($val as $k => $v) {
                        $vars[$key][$k] = $v;
                    }
                } else {
                    $vars[$key] = $val;
                }
            }

            self::$vars = \array_merge(self::$vars, $vars);
        }
    }
}
