<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author MikeCorner
 */

namespace Cimply\App {

    use \Cimply\Core\View\View;
    use \Cimply\Core\Request\Request;
    use \thiagoalessio\TesseractOCR\TesseractOCR;

    class GetDocTypeCtrl extends Init
    {
        
        function __construct() {

        }

        public final function Cast($mainObject, $selfObject = self::class): self {
            return Core::Cast($mainObject, $selfObject);
        }

        /**
         * 
         * @PageTitle Cimply.Work rock´s
         * @params {"Name1":"Value1", "Name2":10}
         * @redirect '{"conditions":{"BenutzerId":false},"fallback":"\login"}'
         * 
         */
        static function Init($service = null) {
            parent::Init($app = new self($service)) ? $app->execute() : null;
        }

        /**
         * 
         * @PageTitle Definition of DocTypes
         * @Version {"V":"0.10"}
         * @TestData "{\"MT\":\"Mitteilung\", \"SK\":\"Standortkennziffer\",\"AU\":\"Auftrag\", \"RE\":\"Rechnung\", \"MBL\":\"Leasing\", \"ZUE\":\"Zahlungsübersicht\", \"VK\":\"Vorkalkulation\", \"BE\":\"Bestellung\", \"VR\":\"Verrechnung\", \"AB\":\"Auftragsbestätigung\", \"KB\":\"KONTROLLBESTAETIGUNG\", \"PK\":\"Protokoll\", \"ZV\":\"Zusatzvereinbarung\", \"BL\":\"BELASTUNG\"}"
         * 
         */
        static function Execute() {
            $jsonObj = View::ParseTplVars("[+TestData+]");
            if(!($file = $_FILES['file']['tmp_name'])) {
                die('Keine Informationen vorhanden.');  
            }
            
            $finfo = \Mime::GetMime($file);
            if($finfo === "application/pdf") {
                $pdf = (new \Smalot\PdfParser\Parser())->parseFile($file);
                $input = $pdf->getText();
            } else {
                $input = (new TesseractOCR($file))->executable('tesseract')->lang('deu')->run();
            }
            
            // The Metaphone will be: "0 KK BRN FKS JMPT OFR 0 LS TK"
            $searchAgainst = json_decode($jsonObj, true);
            // Metaphones will be: "0 KK BRN KT JMPT OFR 0 LS TK", "0RS HMR JMPT OFR 0 LS TK", "0 KK BRN FKS JMPT OFR 0 LS TK"

            $SoundsLike = new \Cimply\Logic\Algorythm\SoundsLikeCtrl($searchAgainst, substr($input, 0, 400));
            die($SoundsLike->findBestMatch());

        }

    }
}