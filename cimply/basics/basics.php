<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

declare(strict_types=1);

namespace Cimply\Basics {

    use Cimply\Core\Request\Uri\UriManager;
    use Cimply\Core\Validator\Validator;
    use Cimply\Core\View\View;
    use Cimply\System\Config;
    use Cimply\System\System;

    class Basics extends System
    {
        use \Properties, \Cast;

        protected ?string $actionPath = null;

        public string $type = 'html';
        public ?string $action = null;
        public mixed $controller = null;
        public mixed $method = null;
        public mixed $target = null;

        public string $markupFile = '';
        public array $markup = [];
        public array $routings = [];
        public array $templating = [];
        public array $requires = [];
        public mixed $validate = null;
        public array $params = [];
        public array $session = [];
        public bool $caching = false;

        public function __construct($instance = null)
        {
            parent::__construct(new Config(), 'system.config.yml');
        }

        final public static function Cast($mainObject, $selfObject = self::class): self
        {
            /** @var self */
            return static::Cull($mainObject, $selfObject, true);
        }

        final public function route(string $path, callable $action, $options = null): self
        {
            $this->actionPath = str_replace('/', '_', $path);
            $this->routings[$this->actionPath] = $action;
            return $this;
        }

        final public function assign(array $params = []): self
        {
            if ($this->actionPath !== null) {
                View::Assign(array_merge($this->params, $params));
            }
            return $this;
        }

        final public function validates(array $requires = []): self
        {
            if ($this->actionPath !== null) {
                $this->validate = (new Validator())->addRules($requires);
            }
            return $this;
        }

        final public function action(string $action = ''): self
        {
            if ($this->actionPath !== null) {
                $this->action = $action;
            }
            return $this;
        }

        /**
         * @param array<int, string> $expActionPath
         * @param array<int, string> $expPath
         */
        private function validRoutingChecker(array $expActionPath, array $expPath): bool
        {
            $checked = [];
            $validType = [];

            foreach ($expActionPath as $key => $value) {
                $typeCheck = [];
                $var = explode(':', $expPath[$key] ?? '');

                if (isset($var[1]) && $var[1] !== '') {
                    $t = $var[1][0] ?? '';

                    if ($t === 'i') {
                        $typeCheck[$var[1]] = is_numeric($value);
                    } elseif ($t === 'b') {
                        // URL params are strings; allow "true/false/0/1" as bool-ish
                        $typeCheck[$var[1]] = in_array(strtolower((string)$value), ['0', '1', 'true', 'false'], true);
                    } elseif ($t === 'f') {
                        $typeCheck[$var[1]] = is_numeric($value);
                    } elseif ($t === 's') {
                        $typeCheck[$var[1]] = is_string($value);
                    }

                    $validType[] = !empty($typeCheck[$var[1]]) ? (bool)$typeCheck[$var[1]] : false;
                } else {
                    if ($value !== ($expPath[$key] ?? null)) {
                        $checked[] = $value;
                    }
                }
            }

            return empty($checked) ? !in_array(false, $validType, true) : false;
        }

        final public function routing(?string $actionPath = null): ?array
        {
            $this->actionPath = UriManager::ActionPath();
            $lookup = $actionPath ?? $this->actionPath;

            $currentRoute = $this->routings[$lookup] ?? null;

            if ($currentRoute === null) {
                foreach ($this->routings as $path => $scope) {
                    $expPath = explode('_', (string)$path);
                    $expActionPath = explode('_', (string)$lookup);

                    if (count($expPath) !== count($expActionPath)) {
                        continue;
                    }

                    // keep original intent: ensures arrays can be combined
                    if ((implode('_', array_combine($expPath, $expActionPath)) ?? '') === '') {
                        continue;
                    }

                    if ($this->validRoutingChecker($expActionPath, $expPath) === true) {
                        $currentRoute = $scope;
                        break;
                    }
                }
            }

            if (is_callable($currentRoute)) {
                $result = $currentRoute();
                return [$this->actionPath => (array)$result];
            }

            return ['externalFile' => true];
        }
    }
}
