<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

declare(strict_types=1);

namespace Cimply\App;

use Cimply\Basics\Basics;
use Cimply\Basics\Repository\Support;
use Cimply\Basics\ServiceLocator\ServiceLocator;
use Cimply\Core\Model\Mapper;
use Cimply\Core\Model\Wrapper;
use Cimply\Core\Request\Request;
use Cimply\Core\Request\Uri\UriManager;
use Cimply\Core\Routing\Routing;
use Cimply\Core\View\Translate;
use Cimply\Interfaces\Support\Enum\AppSettings;
use Cimply\Interfaces\Support\Enum\RootSettings;

class Run extends Basics
{
    public bool $isDebug = false;

    protected ServiceLocator $instance;

    /** @var callable */
    protected $autoloader;

    protected string $projectName;
    protected string $projectPath;
    protected Support $settings;

    public function __construct(string $projectName, callable $autoloader)
    {
        parent::__construct();

        $this->ensureSession($projectName);

        $this->instance    = ServiceLocator::Cast(null);
        $this->autoloader  = $autoloader;
        $this->projectName = $projectName;
        $this->projectPath = str_replace('%project%', $projectName, Settings::ProjectPath);

        $config = parent::GetConfig()->loader($this->projectPath . 'config.yml', static::$conf) ?? [];

        // recursive replacement of %project% only in string values
        $replace = function ($v) use (&$replace) {
            if (is_string($v)) {
                return str_replace('%project%', $this->projectName, $v);
            }
            if (is_array($v)) {
                foreach ($v as $k => $vv) {
                    $v[$k] = $replace($vv);
                }
            }
            return $v;
        };

        $config = $replace($config);

        $this->settings = $this->instance->addInstance(new Support($config));
        $this->isDebug  = (bool)$this->settings->getSettings([], RootSettings::DEVMODE);
    }

    public function register(): ServiceLocator
    {
        $rootUrl = (string)$this->settings->getSettings([], AppSettings::BASEURL);

        $routingPath = (new UriManager())->getRoutingPath($rootUrl);
        $routingCfg  = parent::GetConfig()->loader(
            $this->projectPath . 'routing.yml',
            $this->routing($routingPath)
        );

        $this->instance->addInstance(new Routing($routingCfg));

        $this->instance->addInstance(new Request($this->validate));

        $rootPattern = Support::Cast($this->instance->getService())->getRootSettings(RootSettings::PATTERN);
        $globals     = parent::GetConfig()->loader($this->projectPath . 'globals.yml', []) ?? [];

        $this->instance->addInstance(
            (new Translate($rootPattern))->set($globals, true)
        );

        $mapperCfg = parent::GetConfig()->loader($this->projectPath . 'mapper.yml', []) ?? [];
        $mapper    = $this->instance->addInstance((new Mapper())->set($mapperCfg, true));

        $models = [];
        foreach (Mapper::Cast($mapper)->getMappers() ?? [] as $file) {
            $models = parent::GetConfig()->loader($this->projectPath . $file, $models) ?? $models;
        }

        $this->instance->addInstance((new Wrapper())->set($models));

        return $this->instance;
    }

    public function execute(): self
    {
        ($this->autoloader)($this->settings->getAssembly());

        $appClass = (string)$this->settings->getSettings([], AppSettings::PROJECTNAMESPACE);

        return new $appClass($this->register());
    }

    private function ensureSession(string $sessionId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (session_id() === '') {
                session_id($sessionId);
            }
            session_start();
        }
    }
}
