<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

declare(strict_types=1);

namespace Cimply\Core\Routing;

use Cimply\Core\Core;
use Cimply\Core\Request\Uri\UriManager;
use Cimply\Core\View\View;
use Cimply\Interfaces\ICast;
use Cimply\System\System;

class Routing implements ICast
{
    use \Properties, \Cast;

    protected mixed $scope = null;
    protected ?string $route = null;
    protected string $lastRoute = '/';
    protected ?string $nextRoute = null;

    private ?string $file = null;
    private ?string $fileName = null;
    private ?string $baseFile = null;
    private ?string $fileType = null;
    private ?string $path = null;
    private ?string $action = null;

    /** @var array<string, mixed>|null */
    private ?array $routeParams = null;

    private bool $external = false;

    public function __construct(array $query = [])
    {
        $this->setRoute(new UriManager())->setScope($query);
    }

    public static function Cast($mainObject, $selfObject = self::class): self
    {
        /** @var self */
        return Core::Cast($mainObject, $selfObject);
    }

    /**
     * Set route with fallback to lastRoute
     */
    protected function setRoute(UriManager $route): self
    {
        $this->path     = $route->getFilePath();
        $this->file     = $route->currentFile();
        $this->fileName = $route->getFileName();
        $this->baseFile = $route->getFileBasename();
        $this->fileType = $route->getFileType();

        if (isset($this->route)) {
            // keep original behavior (even if string): snapshot of previous value
            $this->lastRoute = (string)$this->route;
        }

        $this->route = $route->getFileNameUrl() ?? $this->lastRoute;

        return $this;
    }

    /**
     * Set upcoming Route
     */
    protected function setNextRoute(string $route): self
    {
        $this->nextRoute = $route ?: $this->route;
        return $this;
    }

    private function setScope(?array $query = null): void
    {
        $this->setRouteParams();

        if ($query !== null) {
            $vars = View::GetVars();
            if ($vars !== []) {
                System::SetSession('storageData', $vars);
            }
            $this->setExternalRoute($query);
        }
    }

    private function setExternalRoute(array $query): void
    {
        $params = [
            'requires' => $this->routeParams ?? [],
        ];

        $this->scope = (function (array $params) use ($query): array {
            $fallback = $query[$this->getPath()]
                ?? $query[$this->getBaseFile()]
                ?? $query[$this->getFilename()]
                ?? $query[$this->getFile()]
                ?? ($this->action !== null ? ($query[$this->action] ?? null) : null);

            if ($fallback === null) {
                $this->external = true;

                $fallback = [
                    'type'    => $this->fileType,
                    'params'  => $this->routeParams,
                    'action'  => '\Cimply\App\Base\FileCtrl::Init',
                    'target'  => '{->' . $this->get('baseFile') . '}',
                    'caching' => 'false',
                ];
            }

            if (!is_array($fallback)) {
                $fallback = [];
            }

            return array_merge($fallback, $params);
        })($params);
    }

    private function setRouteParams(): void
    {
        $explPath = explode('/', (string)$this->path);
        $last     = end($explPath);
        $arrayResult = explode('_', ((count($explPath) % 2) ? '_' : '') . (string)$last);

        $this->routeParams = $this->parseParams($arrayResult);
    }

    /**
     * @param array<int, string> $keyParam
     * @return array<string, string>
     */
    public function parseParams(array $keyParam): array
    {
        ksort($keyParam);
        $result = [];
        $keyName = '';

        foreach ($keyParam as $key => $value) {
            if ($key % 2) {
                $keyName = (string)$value;
            } else {
                if (!empty($key)) {
                    $result[$keyName] = (string)$value;
                } else {
                    $this->action = (string)$value;
                }
            }
        }

        return $result;
    }

    public function getFile(): ?string
    {
        return empty($this->file) ? null : $this->file;
    }

    public function getPath(?string $path = null): ?string
    {
        $p = $path ?? $this->path;
        if ($p === null) {
            return null;
        }

        if (substr($p, -1) === '/') {
            $p = substr($p, 0, -1);
        }

        return str_replace('/', '_', $p);
    }

    public function getActionPath(?string $path = null): ?string
    {
        $p = $path;
        if ($p === null) {
            return null;
        }

        if (substr($p, -1) === '/') {
            $p = substr($p, 0, -1);
        }

        return $p;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getFilename(): string
    {
        return (string)$this->fileName;
    }

    public function getBaseFile(): string
    {
        return (string)$this->baseFile;
    }

    public function getScope(): ?array
    {
        return is_array($this->scope) ? $this->scope : null;
    }

    /** @return array<string, mixed>|null */
    public function getParams(): ?array
    {
        return $this->routeParams;
    }

    public function execute(): object
    {
        return (object)[
            'file'   => $this->getFile(),
            'path'   => $this->getPath(),
            'params' => $this->getParams(),
            'scope'  => (object)($this->getScope() ?? []),
        ];
    }

    public function isExternal(): bool
    {
        return $this->external;
    }
}
