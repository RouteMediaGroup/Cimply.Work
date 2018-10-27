<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\System {

    class Config {
        protected static $result = [], $conf, $hasValues = false;
        function __construct($conf = [], $arr = []) {
            self::$conf = empty($conf) ? : \ArrayParser::MergeArrays($conf, $arr);
        }

        public static function getConf($conf = [], $filter = null, $needle = null): ?array {
            if(isset($filter)) {
                $filters = explode("/", $filter);
                if(isset($filters[1])) {
                    self::$result = \ArrayParser::SearchArrayRecursive($filters, $conf);
                    self::$hasValues = isset($needle) ? (in_array($needle, self::$result)) ? true : false : self::$result;
                    return self::$result;
                }
                return isset($conf[$filter]) ? $conf[$filter] : null;
            }
            return $conf;
        }

        public static function loader($configFile, $conf = []): array {
            $result = [];
            try {
                if(isset($configFile) && is_file($configFile)) {
                    $result = \YamlParser::AddYaml($conf, $configFile) ?? $conf;
                }
            } catch (Exception $ex) {
                //$this->logger->log(sprintf('Set Project: %s', $this->SystemConfig['project']), 'NOTICE');
                //$this->logger->log(sprintf('Message: "%s" Error on Line %s', $ex->getMessage(), $ex->getLine()), 'ERROR');
                throw new \Exception($ex);
            }
            return $result;
        }

    }
}