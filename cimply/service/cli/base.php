<?php
namespace Cimply\Service\Cli {
    use \Cimply\System\System;
    class Base extends System {

        protected static $app, $currentSelect = null, $projects = [];

        static function CLI():bool {
            $state = false;
            if (php_sapi_name() == "cli-server") {
                $extensions = array("php", "jpg", "jpeg", "gif", "css");
                $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $state = true;
                if (in_array($ext, $extensions)) {
                    $state = false;
                }
            }
            return $state;
        }

        protected static function GetMessage(): string {
            exec('.\execute.bat', $result);
            return strtolower(end($result));
        }

        protected static function GetProjectName($project = null): ?string {
            $newName = str_replace([' ', '_', '-', '/', '\\', '&', '.', ',', '=', '?', '#'], ' ', ucwords(strtolower($project), ' '));
            return str_replace(' ','', ucwords(strtolower($newName), ' '));
        }

        protected static function LoadProject($projects, $show = true): array {
            $i = 0;
            $project = [];
            $directories = array_diff(scandir($projects), array('..', '.'));
            foreach($directories as $value) {
                $i++;
                $project[strtolower($value)] = strtolower($value);
                $project[$value] = $value;
                $output = $i.': '.$value;
                $project[$i] = $value;
                if($show) {
echo <<<Inhalt
$output

Inhalt;
                }
            }
            return $project;
        }
    }
}