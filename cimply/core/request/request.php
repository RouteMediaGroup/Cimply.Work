<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht.
 * Contact: direkt@route-media.info. All rights reserved.
*/

namespace Cimply\Core\Request {

    use Cimply\Core\Core;
    use Cimply\Core\Validator\Validator;
    use Cimply\Interfaces\ICast;

    class Request implements ICast
    {
        private mixed $getRequestData = null;
        private mixed $postRequestData = null;
        private mixed $putRequestData = null;
        private mixed $deleteRequestData = null;
        private mixed $optionsRequestData = null;

        private mixed $clientRequestMethod = null;
        private mixed $serverRequestMethod = null;

        private ?array $files = null;

        private ?string $request_uri = null;

        private mixed $request = null;

        public array $method = [];
        public ?Validator $validate = null;
        public array $validationList = [];

        public function __construct(?Validator $validations = null)
        {
            $this->validate = $validations ?? new Validator();

            if (isset($_SERVER['REQUEST_URI'])) {
                $this->service();
            }
        }

        private function service(): void
        {
            $method = $_SERVER['REQUEST_METHOD'] ?? null;

            switch ($method) {
                case 'GET':
                    $this->setGetRequest();
                    break;
                case 'POST':
                    $this->setPostRequest();
                    break;
                case 'PUT':
                    $this->setPutRequest();
                    break;
                case 'DELETE':
                    $this->setDeleteRequest();
                    break;
                case 'OPTIONS':
                    $this->setOptionsRequest();
                    break;
            }

            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $this->request_uri = (string)\filter_var(\urldecode($uri), FILTER_SANITIZE_URL);

            $this->setRequest()
                ->getFileData()
                ->addSource();
        }

        private function setRequest(): self
        {
            $raw = \file_get_contents('php://input');
            $this->request = ($raw !== false && $raw !== '') ? $raw : null;
            return $this;
        }

        public final static function Cast($mainObject, $selfObject = self::class): self
        {
            return Core::Cast($mainObject, $selfObject);
        }

        private function setGetRequest(): void
        {
            $this->getRequestData = \filter_input(INPUT_GET, 'method');
        }

        private function setPostRequest(): void
        {
            $this->postRequestData = \filter_input(INPUT_POST, 'method');
        }

        private function setPutRequest(): void
        {
            $raw = \file_get_contents('php://input');
            $data = [];
            if ($raw !== false && $raw !== '') {
                \parse_str($raw, $data);
            }
            $this->putRequestData = $data;
        }

        private function setDeleteRequest(): void
        {
            $raw = \file_get_contents('php://input');
            $data = [];
            if ($raw !== false && $raw !== '') {
                \parse_str($raw, $data);
            }
            $this->deleteRequestData = $data;
        }

        private function setOptionsRequest(): void
        {
            $raw = \file_get_contents('php://input');
            $data = [];
            if ($raw !== false && $raw !== '') {
                \parse_str($raw, $data);
            }
            $this->optionsRequestData = $data;
        }

        public function getGetData(): mixed
        {
            return $this->getRequestData;
        }

        public function getPostData(): mixed
        {
            return $this->postRequestData;
        }

        public function getPutData(): mixed
        {
            return $this->putRequestData;
        }

        public function getDeleteData(): mixed
        {
            return $this->deleteRequestData;
        }

        public function getOptionsData(): mixed
        {
            return $this->optionsRequestData;
        }

        public function filteredServerRequest(): mixed
        {
            return $this->serverRequestMethod;
        }

        public function filteredRequestUri(): mixed
        {
            return $this->filteredRequestUri ?? null;
        }

        public function getRequest(): mixed
        {
            return $this->request;
        }

        public function getFiles(): ?array
        {
            return $this->files;
        }

        public function getFileData(): self
        {
            if (isset($_FILES['file']['tmp_name'])) {
                $tmp = $_FILES['file']['tmp_name'];

                $fileData = null;
                if (\is_array($tmp)) {
                    $first = $tmp[0] ?? null;
                    if ($first && \is_string($first) && \is_file($first)) {
                        $fileData = \file_get_contents($first);
                    }
                } else {
                    if (\is_string($tmp) && \is_file($tmp)) {
                        $fileData = \file_get_contents($tmp);
                    }
                }

                if ($fileData === false) {
                    $fileData = null;
                }

                $post = \is_array($_POST ?? null) ? $_POST : [];
                $file = \is_array($_FILES['file'] ?? null) ? $_FILES['file'] : [];

                $this->files = \array_merge($post, $file, ["Binary" => $fileData]);
            }

            return $this;
        }

        public function addSource($item = null): self
        {
            if ($item === null) {
                $req = $this->request;
                if (\is_string($req) && $req !== '') {
                    $item = \JsonDeEncoder::Decode($req, true);
                } else {
                    $item = [];
                }
            }

            $this->validate->addSource($item);
            return $this;
        }

        public function addValidationRules($item = null): self
        {
            if (!\is_array($item) || $item === []) {
                return $this;
            }

            $name = \array_key_first($item);
            if ($name === null) {
                return $this;
            }

            $this->validate->AddRules(\Lists::ListOfObjects($item[$name]));
            return $this;
        }

        public function getValidations(): array
        {
            $req = $this->request;
            $result = \Lists::ArrayList(\is_string($req) ? $req : '', 'dataObject');

            if (isset($result['dataObject']) && \is_array($result['dataObject'])) {
                foreach ($result['dataObject'] as $key => $value) {
                    $validation = $this->validate;
                    $validation->addSource($value)->AddRules($this->validate->GetRules());
                    $this->validationList[$key] = $validation;
                }
                return $this->validationList;
            }

            return [];
        }

        public function execute(): mixed
        {
            return $this->validate->run();
        }
    }
}
