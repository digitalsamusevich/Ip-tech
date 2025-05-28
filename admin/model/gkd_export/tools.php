<?php
class ModelGkdExportTools extends Model {
  
  public function sanitize($string) {
    $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    $string = strip_tags($string);
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8', 0);
    
    $string = str_replace("&nbsp;", ' ', $string);
    $string = str_replace("\t", '', $string);
    $string = str_replace(PHP_EOL.PHP_EOL, ' ', $string);
    
    return $string;
  }
  
  public function truncate($string, $limit, $break = '.', $pad = '...') {
    if(strlen($string) <= $limit) return $string;

    if(false !== ($breakpoint = strpos($string, $break, $limit))) {
      if($breakpoint < strlen($string) - 1) {
        $string = substr($string, 0, $breakpoint) . $pad;
      }
    }
    
    $string = preg_replace("/ +/", " ", $string);
    $string = preg_replace("/^ +/", "", $string);
    
    return $string;
  }
  
}