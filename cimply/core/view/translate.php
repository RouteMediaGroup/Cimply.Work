<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cimply\Core\View {

    /**
     * Description of CIM
     *
     * @author MikeCorner
     */
    use \Cimply\System\Helpers as Helper;
    class Translate
    {
        use \Properties, \Cast;

        protected static $pattern = null; 
        function __construct($pattern = null) {
            self::$pattern = $pattern;
        }

        public final function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        function OnPropertyChanged() {
            self::$staticProperties = $this;
        }

        //Get Translation
        static function GetTranslastion(string $s = "", $langCode = 'de_DE') {
            $matches = [];
            preg_match_all(self::$pattern['Trans'], $s, $matches);
            if(($matches[1])) {
                $i = 0;
                foreach($matches[1] as $key => $value) {
                    if($trans = self::WordTranslation($matches[1][$i], $langCode)) {
                        $s = str_replace($matches[0][$i], $trans, $s);
                    }
                    $i++;
                }
            }
            return $s;
        }

        static function WordTranslation($value, $langCode = 'de_DE') {
            $checkString = self::GetStaticProperty('Translations')[$value] ?? null;
            return $checkString[$langCode] ?? $value;
        }
        
        static function Translation($value, $trans = null): string {
            !(isset($trans)) ? $trans = self::GetStaticProperty('Translastions') : null;
            $translation = self::GetTranslastion($value);
            //die(\var_dump(self::GetTranslastion($value)));
            //count($translation) === 0 ? $translation = $value : null;
            $explString = \explode(" ", $translation);
            $counts = strlen(end($explString));
            //die(\var_dump($explString[2]));
            $replacesWord = [];
            foreach($checkString = end($trans) as $key => $val) {
                if(substr($key, 0, $counts) === $explString[0]) {
                    if(similar_text($key, $translation, $percent)) {
                        if($percent >= 50) {
                            $trimResult = explode(" ", $key);
                            foreach ($trimResult as $k => $v) {
                                if(isset($explString[$k]) && ($v != $explString[$k])) {
                                    $i = str_replace('%', '', $v);
                                    $replacesWord[$i] = self::WordTranslation($explString[$k]);
                                }
                            }
                            $translation = \vsprintf(end($val), $replacesWord);
                        }
                    }
                }
            }
            return ($translation);
        }
    }
}
