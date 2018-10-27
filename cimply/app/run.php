<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cimply\App {
    use \Cimply\Basics\Repository\Support;
    use \Cimply\Core\{Request\Request, Request\Uri\UriManager, Routing\Routing, Model\Mapper, Model\Wrapper, View\Translate};
    use \Cimply\Basics\{Basics, ServiceLocator\ServiceLocator};
    use \Cimply\Interfaces\Support\Enum\{RootSettings, AppSettings};

    class Run extends Basics {
        public $isDebug = false;
        protected $instance = null, $autoloader = null, $projectName = null, $projectPath = null, $settings = null;
        function __construct(...$args) {
            parent::__construct();
            session_id() === null ? session_id($args[0]) : (session_status() != 1) ? session_start() : true;
            $this->instance = ServiceLocator::Cast(null);
            
            $this->autoloader = $args[1];
            $this->projectName = $args[0];
            $this->projectPath = (str_replace('%project%', $args[0], Settings::ProjectPath));
            //add instance of project settings
            $this->settings = $this->instance->addInstance(new Support(array_map(
                (function($str) {
                    return str_replace('%project%', $this->projectName, $str);
                }), parent::GetConfig()->loader($this->projectPath.'config.yml', static::$conf) ?? []
            )));
            $this->isDebug = $this->settings->getSettings([], RootSettings::DEVMODE);
        }

        final function register(): ServiceLocator {
            //add instance of routing
            $rootUrl = $this->settings->getSettings([], AppSettings::BASEURL);
            $this->instance->addInstance(new Routing(parent::GetConfig()->loader($this->projectPath.'routing.yml', $this->routing((new UriManager)->getRoutingPath($rootUrl)))));

            //add instance of request-data
            $this->instance->addInstance(new Request($this->validate));
            //add instance of globale translations
            $this->instance->addInstance((new Translate(Support::Cast($this->instance->getService())->getRootSettings(RootSettings::PATTERN)))->set(parent::GetConfig()->loader($this->projectPath.'globals.yml', []) ?? [], true));
            //add instance of mapping
            $mapper = $this->instance->addInstance((new Mapper())->set(parent::GetConfig()->loader($this->projectPath.'mapper.yml', []) ?? [], true));

            $models = [];
            foreach( Mapper::Cast($mapper)->getMappers() ?? [] as $value) {
                $models = parent::GetConfig()->loader($this->projectPath.$value, $models);
            }

            //add instance of model-wrappers
            $this->instance->addInstance((new Wrapper())->set($models));
            return $this->instance;
        }

        final function execute(): self {
            ($this->autoloader)($this->settings->getAssembly());
            $app = $this->settings->getSettings([], AppSettings::PROJECTNAMESPACE);
            return (new $app($this->register()));
        }
    }
}