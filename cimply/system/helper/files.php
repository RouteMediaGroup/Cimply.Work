<?php
namespace
{
	/**
	 * @Author Michael Eckebrecht
	 */
	trait Files
    {
        static function GetFileContent($filename, $path = false, $options = null, $method = 'GET', $param1 = null, $param2 = null): ?string
        {
            $currentFile = "";
            $filename = str_replace('\\',DIRECTORY_SEPARATOR, $filename);
            if(\is_file($filename)) {
                $opts = isset($options) ? $options : array(
                    'http'=>array(
                        'method'=> $method,
                        'header'=>"Accept-language: en\r\n" . "Cookie: set=null\r\n"
                    )
                );
                $context = stream_context_create($opts);
                $currentFile = \file_get_contents($filename, $path, $context, $param1) ?? null;
            }
            return !(empty($currentFile)) ? $currentFile : null;
        }
        static function PutFileContent($filename, $data = "", $options = null, $method = 'POST', $deep = 0): void
        {
            if(\is_file($filename)) {
                $opts = isset($options) ? $options : array(
                    'http'=>array(
                        'method'=> $method,
                        'header'=>"Accept-language: en\r\n" . "Cookie: set=null\r\n"
                    )
                );
                $context = stream_context_create($opts);
                \file_put_contents($filename, $data, $deep, $context) ?? null;
            }
        }
        static function GetFilePath($path = '', $options = null) {
            $result = array();
            isset($path) ? $result = (isset($options) ? \pathinfo($path, $options) : \pathinfo($path)) : null;
            return $result;
        }

        static function GetHttpsFile($url, $path = null, $username = "", $password = "", $timeout = 60, $method = 'POST', $param1 = -1, $param2 = 40000)
        {
            $body = '';
            $opts = array('http' =>
                array(
                    'method'  => $method,
                    'header'  => "Content-Type: text/xml\r\n".
                    "Authorization: Basic ".base64_encode("{$username}:{$password}")."\r\n",
                    'content' => $body,
                    'timeout' => $timeout
                )
            );
            self::GetFile($url, $path, $opts, null, $param1, $param2);
        }

        static function GetUtf8File($fn) {
            return mb_convert_encoding($content = self::GetFileContent($fn) ?? '', 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
        }

        static function SetFileKey($project = 'global', $filename = '') {
            return md5($project.str_replace(' ','', $filename));
        }

        static function RemoveFile($filePath) {
            try {
                \unlink($filePath);
            } catch (Exception $ex) {
                \Debug::VarDump($ex);
            };
        }

        static function FileForceContents($filename = null, $data, $flags = 0):bool {
            return (bool)  (\is_file(str_replace('\\',DIRECTORY_SEPARATOR, $filename)) === true) ? (file_put_contents(str_replace('\\',DIRECTORY_SEPARATOR, $filename), $data, $flags) ?? false) : false;
        }
        static function CacheFile($filePath, $cacheFile) {
            \ob_start();
            require($filePath);
            $fileData = ob_get_contents();
            \ob_end_clean();
            self::FileForceContents($cacheFile, $fileData, 0);
            !(isset($fileData)) ? null : self::FileForceContents($cacheFile, $fileData, 0);
            return $fileData;
        }

        static function HasFileCached($fileName): bool {
            return (bool)\is_file($fileName);
        }

        static function DeCryptFile($fileName): void {
            (string)$tmpFile = ".compile";
            ob_start();
            require_once($fileName);
            (string)$cryptedData = ob_get_contents();
            \ob_end_clean();
            $splitData = \explode('=',$cryptedData);
            (string)$code_base64 = Crypto::Decrypt($splitData[0].'=','AES-256-CBC',end($splitData));
            if(self::FileForceContents($tmpFile,$code_base64,0)) {
                require_once($tmpFile);
            }
        }
    }
}
