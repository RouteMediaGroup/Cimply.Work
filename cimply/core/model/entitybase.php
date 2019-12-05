<?php
namespace Cimply\Core\Model {
    class EntityBase
    {
        use \Properties, \Cast;
        public $table, $infoMessage = array(), $saveAble = true, $refresh = false;

        function __construct($table = null) {
            isset($table) ? $this->table = $table : null;
        }

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        static function Cast($mainObject, $selfObject = self::class): self {
            return static::Cull($mainObject, $selfObject, true);
        }
    }
}
