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

    abstract class PostsModel implements IBasics
    {
        public $infoMessage = array();
        public $saveAble = true;
        public $refresh = false;        
                
        protected $PostId;        
        protected $Title;        
        protected $Message;        
        public $BenutzerId;        
        public $Username;        
        public $Password;        
        public $EMail;        
        protected $CreateOn;        
        protected $Status;        
    }
        
    class PostsEntity extends PostsModel
    {
        use \Properties, \Cast;              
        
        //Constructor    
        public function __construct($postsModel = null) {
            if(!(is_array($postsModel))) {
                $value = \JsonDeEncoder::Decode($postsModel);
            } else {
                $value = \Lists::ObjectList($postsModel);
            }
            if(isset($value->message)) {
                $this->infoMessage = $value->message; 
            }
                                
            isset($value->PostId) ? settype($value->PostId, 'int') ? $this->PostId = $value->PostId : $this->infoMessage['PostId'] = 'wrong datatype "int"' : null;                                  
            isset($value->Title) ? settype($value->Title, 'string') ? $this->Title = $value->Title : $this->infoMessage['Title'] = 'wrong datatype "string"' : null;                                  
            isset($value->Message) ? settype($value->Message, 'string') ? $this->Message = $value->Message : $this->infoMessage['Message'] = 'wrong datatype "string"' : null;                      
            isset($value->BenutzerId) ? settype($value->BenutzerId, 'int') ? $this->BenutzerId = $value->BenutzerId : $this->infoMessage['BenutzerId'] = 'wrong datatype "int"' : null;        
            isset($value->Username) ? settype($value->Username, 'string') ? $this->Username = $value->Username : $this->infoMessage['Username'] = 'wrong datatype "string"' : null;        
            isset($value->Password) ? settype($value->Password, 'string') ? $this->Password = $value->Password : $this->infoMessage['Password'] = 'wrong datatype "string"' : null;        
            isset($value->EMail) ? settype($value->EMail, 'string') ? $this->EMail = $value->EMail : $this->infoMessage['EMail'] = 'wrong datatype "string"' : null;                    
            isset($value->CreateOn) ? settype($value->CreateOn, 'string') ? $this->CreateOn = $value->CreateOn : $this->infoMessage['CreateOn'] = 'wrong datatype "string"' : null;                                  
            isset($value->Status) ? settype($value->Status, 'string') ? $this->Status = $value->Status : $this->infoMessage['Status'] = 'wrong datatype "string"' : null;              
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