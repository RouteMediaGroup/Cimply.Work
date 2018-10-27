<?php
namespace Cimply\System {
    interface Settings {
        const SystemPath = __DIR__.DIRECTORY_SEPARATOR;
        const TempDir = 'C:\\inetpub\\temp\\procesa';
        const Assembly = [
            'Helper' => self::SystemPath.'Helper',
            'Yaml' => self::SystemPath.'Vendor\\Yaml',
            'Linq' => self::SystemPath.'Vendor\\Linq'
        ];
    }
}