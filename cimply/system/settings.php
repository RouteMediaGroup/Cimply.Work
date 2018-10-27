<?php
namespace Cimply\System {
    interface Settings {
        const SystemPath = __DIR__.DIRECTORY_SEPARATOR;
        const TempDir = self::SystemPath.'tmp';
        const Assembly = [
            'Helper' => self::SystemPath.'Helper',
            'Yaml' => self::SystemPath.'Vendor\\Yaml',
            'Linq' => self::SystemPath.'Vendor\\Linq'
        ];
    }
}
