<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht.
 * Contact: direkt@route-media.info. All rights reserved.
*/

namespace Cimply\Core\Request\Uri {

    use Cimply\Core\Core;

    class UriManager
    {
        private static ?string $actionPath = null;
        private static ?string $baseUrl = null;

        protected static ?string $filePath = null;
        protected static ?string $fileName = null;
        protected static ?string $fileBasename = null;
        protected static ?string $fileType = null;
        protected static ?string $fileNameUrl = null;
        protected static ?string $currentFile = null;

        private string $basePath = '/';

        public function __construct($FilePath = null, string $defaultIndex = 'index', ?string $basePath = null)
        {
            if ($basePath !== null && $basePath !== '') {
                $this->basePath = $basePath;
            }

            if ($FilePath !== null && $FilePath !== '') {
                self::$baseUrl = '/' . ltrim((string)$FilePath, '/');
            } else {
                $requestUri = $_SERVER['REQUEST_URI'] ?? null;
                $originalUrl = $_SERVER['HTTP_X_ORIGINAL_URL'] ?? null;

                if (empty($requestUri)) {
                    $fallback = $originalUrl ?? '';
                    self::$baseUrl = str_replace('/', '_', (string)$fallback);
                } else {
                    self::$baseUrl = (string)$requestUri;
                }

                if (self::$baseUrl === '' || self::$baseUrl === '0') {
                    self::$baseUrl = self::$actionPath ?? '/';
                }
            }

            $explodePath = explode('?', (string)self::$baseUrl, 2);
            self::$baseUrl = $explodePath[0] !== '' ? $explodePath[0] : '/';

            if (self::$baseUrl === '/') {
                self::$baseUrl .= $defaultIndex;
            }

            self::$filePath = self::$baseUrl;

            $this->setCurrentFile();
            $this->setBaseUrl();
        }

        final public static function Cast($mainObject, $selfObject = self::class): self
        {
            return Core::Cast($mainObject, $selfObject);
        }

        public function getFileNameUrl(): ?string
        {
            return self::$fileNameUrl;
        }

        public function getFileBasename(): ?string
        {
            return self::$fileBasename;
        }

        public function getFilePath(): ?string
        {
            if (self::$filePath === null) {
                return null;
            }
            return ltrim((string)self::$filePath, '/');
        }

        public function getRoutingPath(): ?string
        {
            $filePath = (string)$this->getFilePath();

            $subPath = explode((string)$this->basePath, $filePath);
            $tail = end($subPath);
            $actionPath = explode('/', (string)$tail);

            self::ActionPath(implode('_', $actionPath));

            if ((bool)$this->basePath !== true) {
                array_splice($actionPath, 0, 1);
            }

            self::$filePath = implode('/', $actionPath);
            return str_replace('/', '_', (string)self::$filePath);
        }

        public function getFileName(): ?string
        {
            return self::$fileName;
        }

        public function getFileType(): ?string
        {
            return self::$fileType;
        }

        public function setBaseUrl(): void
        {
            $newUri = self::$filePath ?? 'index';

            $host = getHostName();
            $hostIP = $host ? getHostByName($host) : '127.0.0.1';

            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;

            $url = $scheme . $hostIP;

            if ($port !== 80 && $port !== 443) {
                $url .= ':' . $port;
            }

            $url .= '/' . ltrim((string)$newUri, '/');

            $baseUrl = pathinfo($url);

            self::$fileNameUrl = $baseUrl['dirname'] ?? null;
            self::$fileBasename = $baseUrl['basename'] ?? null;
            self::$fileName = $baseUrl['filename'] ?? null;
            self::$fileType = $baseUrl['extension'] ?? null;
        }

        private function setCurrentFile(): void
        {
            $filePath = pathinfo((string)(self::$filePath ?? '/'));
            $dirname = (string)($filePath['dirname'] ?? '/');

            $urlToArray = explode('/', ltrim($dirname, '/'));
            self::$currentFile = $urlToArray[0] ?? null;
        }

        public function currentFile(): ?string
        {
            return self::$currentFile;
        }

        public static function ActionPath($actionPath = null): ?string
        {
            if ($actionPath !== null) {
                self::$actionPath = (string)$actionPath;
            }
            return self::$actionPath;
        }
    }
}
