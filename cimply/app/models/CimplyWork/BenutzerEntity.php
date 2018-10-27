<?php
//------------------------------------------------------------------------------
// <generated-code>
//    This code generated by IEntity-Generator 
//
//    Manuelle Änderungen an dieser Datei führen möglicherweise zu unerwartetem Verhalten Ihrer Anwendung.
//    Manuelle Änderungen an dieser Datei werden überschrieben, wenn der Code neu generiert wird.
// </generated-code>
//------------------------------------------------------------------------------

namespace Cimply\App\Models\CimplyWork 
{
    use \Cimply\Interfaces\IBasics;

    abstract class BenutzerModel implements IBasics
    {
        public $infoMessage = array();
        public $saveAble = true;
        public $refresh = false;        
                
        protected $BenutzerId;        
        protected $Username;        
        protected $Password;        
        protected $EMail;        
    }
        
    class BenutzerEntity extends BenutzerModel
    {
        use \Properties, \Cast;              
        
        //Constructor    
        public function __construct($benutzerModel = null) {
            if(!(is_array($benutzerModel))) {
                $value = \JsonDeEncoder::Decode($benutzerModel);
            } else {
                $value = \Lists::ObjectList($benutzerModel);
            }
            if(isset($value->message)) {
                $this->infoMessage = $value->message; 
            }
                                
            isset($value->BenutzerId) ? settype($value->BenutzerId, 'int') ? $this->BenutzerId = $value->BenutzerId : $this->infoMessage['BenutzerId'] = 'wrong datatype "int"' : null;                                  
            isset($value->Username) ? settype($value->Username, 'string') ? $this->Username = $value->Username : $this->infoMessage['Username'] = 'wrong datatype "string"' : null;                                  
            isset($value->Password) ? settype($value->Password, 'string') ? $this->Password = $value->Password : $this->infoMessage['Password'] = 'wrong datatype "string"' : null;                                  
            isset($value->EMail) ? settype($value->EMail, 'string') ? $this->EMail = $value->EMail : $this->infoMessage['EMail'] = 'wrong datatype "string"' : null;              
        }

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }
        
        public function CalculateStorable() {
            return $this->saveAble;
        }
        
        public function Epilogue() {
            return false;
        }

        public function Prologue() {
            return false;
        }
    }
}