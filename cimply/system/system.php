<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\System {
    use \Cimply\System\Config;
    class System extends Helpers {
        protected static $conf = array(), $configHelper;
        function __construct($config = null, $configFile = null) {
            self::setConfig($config, $configFile);
        }
        private static function SetConfig(Config $config = null, $configFile = 'config.yml'): void {
            if(isset($config)) {
                self::$configHelper = $config;
            }
            self::$conf = self::$configHelper::loader(Settings::SystemPath.$configFile, self::$conf);
        }
        private static function GetUsings($searchPatttern): ?array {
            return self::$configHelper::getConf(self::$conf, $searchPatttern);
        }
        protected function Reference($loader = null, $usings = null): void {
            $loader(self::GetUsings($usings));
        }
        public static function GetConfig(): ?Config {
            return self::$configHelper ?? null;
        }
    }
}