<?php
namespace Cimply\Service\Api {
    use \Cimply\Core\View\View;
    class Http {
        static function Service(string $serviceAddr, $method = 'POST', $protocol = 'http', $port = null) {
            $opts = ['http' =>
                [
                    'method'  => $method,
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query(View::GetVars())
                ]
            ];
            return View::Show(\file_get_contents((!empty($protocol) ? $protocol."://" : $protocol).$serviceAddr.($port ? ":{$port}" : ""), false, stream_context_create($opts)), true);
        }
    }
}