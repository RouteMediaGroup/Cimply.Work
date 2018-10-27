<?php
//------------------------------------------------------------------------------
// <generated-code>
//    This code generated by IEntity-Generator 
//
//    Manuelle Änderungen an dieser Datei führen möglicherweise zu unerwartetem Verhalten Ihrer Anwendung.
//    Manuelle Änderungen an dieser Datei werden überschrieben, wenn der Code neu generiert wird.
// </generated-code>
//------------------------------------------------------------------------------

namespace Cimply\App\Models\Procesa 
{
    use \Cimply\Core\Model\EntityBase;
    use \Cimply\Interfaces\IBasics;

    abstract class IntverrsschlModel extends EntityBase implements IBasics
    {        
        protected $INT_VERRECHNUNGSSCHLUESSEL; protected $VERRECHNUNGSTEXT; protected $VERRECHNUNGSTEXT2; protected $KOSTENKONTO; protected $KOSTENSTELLE; 

        //Storage-Data
        function storageData(): object {
            return (object)[
                'INT_VERRECHNUNGSSCHLUESSEL' => (string)$this->INT_VERRECHNUNGSSCHLUESSEL, 'VERRECHNUNGSTEXT' => (string)$this->VERRECHNUNGSTEXT, 'VERRECHNUNGSTEXT2' => (string)$this->VERRECHNUNGSTEXT2, 'KOSTENKONTO' => (string)$this->KOSTENKONTO, 'KOSTENSTELLE' => (string)$this->KOSTENSTELLE
            ];
        }
        
        //Storage-Data
        function identKeyValue($name = null): ?array {
            $result = [
                'INT_VERRECHNUNGSSCHLUESSEL' => ['INT_VERRECHNUNGSSCHLUESSEL = :INT_VERRECHNUNGSSCHLUESSEL' => (string)$this->INT_VERRECHNUNGSSCHLUESSEL]
            ];
            return $result[$name] ?? end($result);
        }
        
    }
        
    class IntverrsschlEntity extends IntverrsschlModel
    {
        public $table = 'IntVerrsschl';

        //Constructor    
        public function __construct($Model = null) {
            if(!(is_array($Model))) {
                $value = $Model;
            } else {
                $value = \Lists::ObjectList($Model);
            }
            if(isset($value->message)) {
                $this->infoMessage = $value->message; 
            }
                                
            isset($value->INT_VERRECHNUNGSSCHLUESSEL) ? settype($value->INT_VERRECHNUNGSSCHLUESSEL, 'string') ? $this->INT_VERRECHNUNGSSCHLUESSEL = $value->INT_VERRECHNUNGSSCHLUESSEL : $this->infoMessage['INT_VERRECHNUNGSSCHLUESSEL'] = 'wrong datatype "string"' : null;                                  
            isset($value->VERRECHNUNGSTEXT) ? settype($value->VERRECHNUNGSTEXT, 'string') ? $this->VERRECHNUNGSTEXT = $value->VERRECHNUNGSTEXT : $this->infoMessage['VERRECHNUNGSTEXT'] = 'wrong datatype "string"' : null;                                  
            isset($value->VERRECHNUNGSTEXT2) ? settype($value->VERRECHNUNGSTEXT2, 'string') ? $this->VERRECHNUNGSTEXT2 = $value->VERRECHNUNGSTEXT2 : $this->infoMessage['VERRECHNUNGSTEXT2'] = 'wrong datatype "string"' : null;                                  
            isset($value->KOSTENKONTO) ? settype($value->KOSTENKONTO, 'string') ? $this->KOSTENKONTO = $value->KOSTENKONTO : $this->infoMessage['KOSTENKONTO'] = 'wrong datatype "string"' : null;                                  
            isset($value->KOSTENSTELLE) ? settype($value->KOSTENSTELLE, 'string') ? $this->KOSTENSTELLE = $value->KOSTENSTELLE : $this->infoMessage['KOSTENSTELLE'] = 'wrong datatype "string"' : null;              
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

        function Execute() {
            return $this;
        }

    }
}