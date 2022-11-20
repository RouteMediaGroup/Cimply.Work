<?php
namespace {  
    abstract class Session {
        /**
         * 
         * @param type $value
         * @param type $return
         * @return type
         * 
         */
        public static function HasRegist($value = null, $session = null) {
            if(is_array($value)) {
                foreach($value as $key => $val) {
                    if((bool)$session::GetSession($key)) {
                        return true;
                    }
                }
            }
            return false;
        }
        
        public static function SetSession($value = null, $session = null) {
            session_start();
            isset($value) ? $_SESSION[$value] = $session : null;
        }

        public static function GetSession($value = null) {
            return isset($value) ? $_SESSION[$value] : [];
        }

        public static function CloseSession($value = null) {
            session_destroy();
        }

    }
}
