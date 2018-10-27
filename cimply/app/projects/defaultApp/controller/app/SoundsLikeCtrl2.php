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
    public function __construct(array $searchAgainst, $input)
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
      $foundbestmatch = 0;

      $lastPos = 0;
      $positions = array();
      foreach ($this->searchAgainst as $needle) {
        \similar_text(strtolower($needle), strtolower($this->input), $percent);
        $tempInput = implode('.', $this->getMetaPhone($this->input));
        $tempSearchAgainst = implode('.', $this->getMetaPhone($needle));
        $similarity = \levenshtein($tempInput, $tempSearchAgainst);
        $percent = round($percent, 2);
        if ($similarity <= 2) {
          \similar_text(\soundex($tempInput), \soundex($tempSearchAgainst), $percent);
          $positions[] = "{'type' : '{$needle}', 'state' : '1', 'percent' : '{$percent}' }";
        } else {
          while (($lastPos = strpos($this->input, $needle, $lastPos)) !== false) {
                //ToDo: Berechnung der Pronzentangabe neu stellen
            $positions[] = "{'type' : '{$needle}', 'state' : " . (($percent > 5) ? '1' : '0') . ", 'percent' : '{$percent}' }";
            $lastPos = $lastPos + strlen($needle);
          }
        }
      }
      return json_encode($positions, true);
    }
  }
}