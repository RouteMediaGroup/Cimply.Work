<?php
namespace Cimply\App {
    class SoundsLikeCtrl
    {

      private $searchAgainst = array();
      private $input;

      /**
      *@param $searchAgainst - an array of strings to match against $input
      *@param $input - the string for which the class finds the closest match in $searchAgainst
      */
      public function __construct($searchAgainst, $input)
      {
        $this->searchAgainst = $searchAgainst;
        $this->input = $input;
      }

      /**
      *@param $phrase - string
      *@return an array of metaphones for each word in a string
      */
      private function getMetaPhone($phrase)
      {
        $metaphones = array();
        $words = str_word_count($phrase, 1);
        foreach ($words as $word) {
          $metaphones[] = metaphone($word);
        }
        return $metaphones;
      }

      /**
      *@return the closest matching string found in $this->searchAgainst when compared to $this->input
      */
      public function findBestMatch()
      {

        //get the metaphone equivalent for the input phrase
        $tempInput = implode(' ', $this->getMetaPhone($this->input));
        $result = [];
        foreach ($this->searchAgainst as $phrase)
        {
          //get the metaphone equivalent for each phrase we're searching against
          $foundbestmatch = 1;
          $closest = 'Dokument wurde nicht erkannt.';
          $tempSearchAgainst = implode(' ', $this->getMetaPhone($phrase));
          $similarity = \levenshtein($tempInput, $tempSearchAgainst);
          if ($similarity === 0) // we found an exact match
          {
            $closest = $phrase;
            $foundbestmatch = 0;
          }

          else if ($similarity <= $foundbestmatch || $foundbestmatch > 0)
          {
            $closest  = $phrase;
            $foundbestmatch = $similarity;
          }
          $lev = \similar_text($tempInput, $tempSearchAgainst, $percent); 
          
          $result[$lev] = $foundbestmatch. ' ' . $closest . ' ' .round($percent, 2).'%'; 
        }
        //ksort($result);
        return json_encode($result, true);
    }
}
}