<?php
class ModelGkdImportTool extends Model {

  public $logs = array();
  
  public function getObject() {
    return $this;
  }
  
  public function log($log) {
    if (!isset($log['row']) && !isset($log['status']) && !isset($log['title'])) {
      return;
    }
    
    if (defined('GKD_CRON')) {
      if ($this->config->get('gkd_impexp_cron_log') == 'off') {
        return;
      } else if ($this->config->get('gkd_impexp_cron_log') == 'error' && !($log['status'] == 'error' || $log['status'] == 'warning')) {
        return;
      }
      
      $echo = false;
      $msg = '';
      
      if ($log['row'] != '') {
        $msg .= $log['row'] . ' - ';
      }
      
      if (isset($log['cron_msg'])) {
        $msg .= $log['title'] . ' - ' . $log['cron_msg'];
      } else {
        if (is_array($log['msg'])) {
          $log['msg'] = implode(', ', $log['msg']);
        }
        
        $msg .= $log['title'] . ' - ' . strip_tags($log['msg']);
      }
      
      if ($echo) {
        echo $msg . PHP_EOL;
      } else {
        file_put_contents(DIR_LOGS.'universal_import_cron.log', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
      }
    } else {
      $this->logs[] = array(
        'row' => isset($log['row']) ? $log['row'] : '',
        'status' => isset($log['status']) ? $log['status'] : '',
        'title' => isset($log['title']) ? $log['title'] : '',
        'msg' =>  isset($log['msg']) ? $log['msg'] : '',
      );
    }
  }
  
  public function cron_log($msg, $type = '') {
    if ($this->config->get('gkd_impexp_cron_log') == 'off') {
      return;
    } else if ($this->config->get('gkd_impexp_cron_log') == 'error' && !($type == 'error' || $type == 'warning')) {
      return;
    }
    
    $echo = false;

    if ($echo) {
      echo $msg . PHP_EOL;
    } else {
      file_put_contents(DIR_LOGS.'universal_import_cron.log', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
  }
  
  public function copy_image($url, $path) {
    // try copy method first
    /* disabled - cause too much delay
    if (@copy($url, $path)) {
      return true;
    }
    */
    
    //$url = str_replace('https', 'http', $url); // cause 301 redirect on full https with fail to dl image
    
    // if not working try curl method
    //$url = str_replace(' ', '%20', $url);
    
    // encode url
    //$url = htmlspecialchars_decode($url);
    /*
    $url = htmlspecialchars_decode(urldecode($url));
    $url = str_replace(array('%20'), array(' '), $url);
    $url = $this->encode_url($url);
    */
    
    
    // full url encoding
    /*
    $url_parts = parse_url($url);
    
    // encode url for working with foreign languages
    if (!empty($url_parts['path'])) {
      $url_parts['path'] = implode('/', array_map('rawurlencode', explode('/', $url_parts['path'])));
    }
    
    $url = $this->build_url($url_parts);
    */

    $fp = fopen ($path, 'w+');
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (!ini_get('open_basedir')) {
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:30.0) Gecko/20100101 Firefox/30.0');

    // write data to local file
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_exec($ch);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // if 400 or 404 try with encoded url
    if ($httpCode == 400 || $httpCode == 404) {
      fclose($fp);
      $fp = fopen ($path, 'w+');
      curl_setopt($ch, CURLOPT_FILE, $fp);
      
      $url = htmlspecialchars_decode(urldecode($url));
      $url = str_replace(array('%20'), array(' '), $url);
      $url = $this->encode_url($url);
      
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_exec($ch);
      
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    
    $error_msg = '';
    if (curl_error($ch)) {
      $error_msg = curl_error($ch);
    }
    
    curl_close($ch);
    fclose($fp);
    
    if ($httpCode != 200 && $httpCode != 226) {
      unlink($path);
      return $this->language->get('warning_remote_image_not_found') . $url . ' - HTTP Code: ' . $httpCode . ' - ' . $error_msg;
    }
    
    // test image
    //if(!@getimagesize($path) {
    //  return 'Image download failed';
    //}

    if (filesize($path) > 0) {
      return true;
    } else {
      if (!is_writable(dirname($path))) {
        return $this->language->get('warning') . ' - ' . 'Copy failed, make sure the image path is writable: ' . dirname($path);
      }
      
      return $this->language->get('warning_remote_image_not_found') . str_replace(' ', '%20', $url);
      return 'Image download failed';
    }
  }
  
  function encode_url($url) {
    $encodedUrl = preg_replace_callback('%[^:/@?&=#]+%usD', function ($matches) {
      $str = urlencode($matches[0]);
      $str = str_replace('+', '%20', $str);
      return $str;
    }, $url);
    
    return $encodedUrl;
	}
  
  public function build_url(array $parts) {
    return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') . 
        ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') . 
        (isset($parts['user']) ? "{$parts['user']}" : '') . 
        (isset($parts['pass']) ? ":{$parts['pass']}" : '') . 
        (isset($parts['user']) ? '@' : '') . 
        (isset($parts['host']) ? "{$parts['host']}" : '') . 
        (isset($parts['port']) ? ":{$parts['port']}" : '') . 
        (isset($parts['path']) ? "{$parts['path']}" : '') . 
        (isset($parts['query']) ? "?{$parts['query']}" : '') . 
        (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
  }
  
  public function filter_seo($seo_kw) {
    if (!is_string($seo_kw)) return '';
    
		$whitespace = '-';
    if (function_exists('mb_strtolower')) {
      $seo_kw = mb_strtolower($seo_kw, 'UTF-8');
    } else {
      $seo_kw = strtolower($seo_kw);
    }
		$seo_kw = str_replace(' ', $whitespace, $seo_kw);
    $seo_kw = str_replace(array('"','&','&amp;','+','?','/','%','#','<','>'), '', $seo_kw);
    $seo_kw = preg_replace('/'.$whitespace.$whitespace.'+/', $whitespace, $seo_kw);
    $seo_kw = trim($seo_kw, '_'.$whitespace);
		return $seo_kw;
	}
  
  public function urlify($value, $lang = null, $filename = false, $lowercase = true) {
    if (!empty($lang)) {
      include_once(DIR_SYSTEM . 'library/gkd_urlify.php');
      $value = URLify::downcode($value, $lang);
    }
    
    if (!empty($filename)) {
      include_once(DIR_SYSTEM . 'library/gkd_urlify.php');
      return URLify::filter($value, 256, $lang, true, true, $lowercase);
    }
    
    $value = str_replace(array('\'','`','?','?','|','%7C', "\n"), '-', $value);
    $value = str_replace(array('"','?','?','&','&amp;','+','?','!','/','%','#',',',':','&gt;','&lt;',';','<','>','(',')','?','?','?','&copy;','&reg;','&trade;'), '', $value);
    
    $whitespace = '-';
    if ($lowercase) {
      if (function_exists('mb_strtolower')) {
        $value = mb_strtolower($value, 'UTF-8');
      } else {
        $value = strtolower($value);
      }
    }
		$value = str_replace(' ', $whitespace, $value);
    $value = preg_replace('/'.$whitespace.$whitespace.'+/', $whitespace, $value);
    $value = trim($value, '_'.$whitespace);
    
    //$value = trim(preg_replace('/--+/', '-', str_replace(' ', '-', mb_strtolower($value))), '-');
    
    return $value;
  }
  
  public function mb_parse_url($url, $component = -1) {
    $encodedUrl = preg_replace_callback('%[^:/@?&=#]+%usD', function ($matches) {
      return urlencode($matches[0]);
    }, $url);
    
    $parts = parse_url($encodedUrl, $component);
    
    if ($parts === false) {
      throw new Exception('Invalid URL: ' . $url);
    }
    
    if (is_array($parts) && count($parts) > 0) {
      foreach ($parts as $name => $value) {
        $parts[$name] = rawurldecode($value);
      }
    }
    
    return $parts;
  }
  
  public function floatValue($val) {
    if (!is_scalar($val)) {
      if (is_array($val) && isset($val[0]) && is_scalar($val[0])) {
        $this->log(array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => 'Trying to get float from array data, the 1st row of the array have been used for the value, input data: '.print_r($val, 1),
        ));
      
        return floatval(str_replace(',', '.', str_replace('.', '', $val[0]))); 
      }
      
      $this->log(array(
        'row' => $this->session->data['obui_current_line'],
        'status' => 'error',
        'title' => $this->language->get('warning'),
        'msg' => 'Incorrect numerical input, make sure to not send an array into fields that expects a number, input data: '.print_r($val, 1),
      ));
      
      return 0;
    }
    
    if (strpos($val, ',')) {
      return floatval(str_replace(',', '.', str_replace('.', '', $val)));
    }
    
    return $val;
  }
  
  // API Calls
  public function callAPI($url, $login = '', $debug = false, $copyPath = false, $getPath = false, $method = 'GET', $data = array(), $headers = array()) {
    $ch = curl_init();
    
    if (is_string($login) && strpos($login, 'DEBUG') !== false) {
      $debug = true;
      $login = str_replace('DEBUG|', '', $login);
    }
    
    // remove zero-width space and trim url
    $url = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $url);
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
    
    $url = str_replace(
      array('{timestamp}'),
      array(time()),
      $url);
    
    preg_match( '/\{hash:(.+?):(.+?)\}/u', $url, $hashes);
    if (isset($hashes[2])) {
      $hash = hash($hashes[1], $hashes[2]);
      $url = str_replace($hashes[0], $hash, $url);
    }
    
    
    if ($debug) {
      echo '<div class="well">';
      echo 'Call URL: '.$url.'<br/>';
    }
    
    if (!empty($login) && is_string($login)) {
      if (preg_match('/OPT\{(.+)\}\|?/', $login, $curlOptions)) {
        $login = str_replace($curlOptions[0], '', $login);
        
        if (!empty($curlOptions[1])) {
          foreach (explode('|', $curlOptions[1]) as $curlOption) {
            list($curlOptName, $curlOptVal) = explode(':', $curlOption);
            curl_setopt($ch, constant('CURLOPT_'.$curlOptName), $curlOptVal);
          }
        }
      }
    }
    
    // set authorization header
   if (is_string($login) && (substr($login, 0, 5) == 'OAUTH')) {
      $oauthMode = true;
       
      $oauthParams = explode('|', substr($login, 6));
      
      $consumer_key    = isset($oauthParams[0]) ? $oauthParams[0] : '';
      $consumer_secret = isset($oauthParams[1]) ? $oauthParams[1] : '';
      $token           = isset($oauthParams[2]) ? $oauthParams[2] : '';
      $token_secret    = isset($oauthParams[3]) ? $oauthParams[3] : '';

      $parameters = array();

      $headers    = array(
        'Content-Type' => 'application/json',
        'Accept'       => 'application/json'
      );
      
      try {
        $oauthClient = new OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);
        $oauthClient->setToken($token, $token_secret);
        $oauthClient->fetch($url, $parameters, OAUTH_HTTP_METHOD_GET, $headers);
        $res = $oauthClient->getLastResponse();

        file_put_contents($copyPath, $res);
        
        if (!empty($this->session->data['univimport_downloadFilename'])) {
          $newFile = dirname($copyPath).'/'.$this->session->data['univimport_downloadFilename'];
          rename($copyPath, $newFile);
          $this->session->data['univimport_temp_file'] = $newFile;
        } else {
          $this->session->data['univimport_temp_file'] = $copyPath;
        }
        
        if ($copyPath) {
          return true;
        } else {
          return $res;
        }
        
      } catch (Exception $e) {
        throw new Exception(sprintf($this->language->get('error_oauth_api'), $e->getMessage(), $url));
      }
    } else if (is_string($login) && (substr($login, 0, 2) == 'H:')) {
      //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', substr($login, 2)));
      $headers[] = 'Content-Type: application/json';
      $headers[] = substr($login, 2);
      
      $login = '';
    } else if (is_string($login) && (substr($login, 0, 5) == 'HEAD|')) {
      //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', substr($login, 5)));
      $headers[] = 'Content-Type: application/json';
      $headers[] = substr($login, 5);
      
      $login = '';
    } else if (isset($login['auth'])) {
      //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $login['auth']));
      $headers[] = 'Content-Type: application/json';
      $headers[] = $login['auth'];
      $login = '';
    } else if (!empty($login) && is_string($login)) {
      $authQuery = explode('|', $login);
      
      if ($authQuery[0] == 'POST' || $authQuery[0] == 'GET' || $authQuery[0] == 'JSON' || $authQuery[0] == 'XWWW') {
        $method = array_shift($authQuery);
        
        foreach ($authQuery as $authCommand) {
          $authParams = explode(':', $authCommand, 2);
          if (isset($authParams[1])) {
            $data[$authParams[0]] = $authParams[1];
          }
        }
      }
    }
    
    switch ($method) {
      case 'JSON':
        if ($debug) {
          echo 'Set JSON params: '.json_encode($data).'<br/>';
        }
        
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'cache-control: no-cache'));
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'cache-control: no-cache';
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        break;
      case 'POST':
        if ($debug) {
          echo 'Set POST params: '.json_encode($data).'<br/>';
        }
        /*
        $post_data = $data;
        
        if (strpos($login, '|')) {
          foreach (explode('|', $login) as $queries) {
            $query = explode(':', $queries);
            
            if (isset($query[0]) && isset($query[1])) {
              $post_data[$query[0]] = $query[1];
            }
          }
        }
        */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        break;
      case 'XWWW':
        if ($debug) {
          echo 'Set XWWW params: '.json_encode($data).'<br/>';
        }

        $headers[] = 'Content-Type: application/x-www-form-urlencoded';

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        break;
      case 'PUT':
        curl_setopt($ch, CURLOPT_PUT, 1);
        break;
      default:
        if ($data) {
          $url = sprintf("%s?%s", $url, http_build_query($data));
        }
    }
    
    if (!empty($headers)) {
      if ($debug) {
        echo 'Set header:' . print_r($headers, true);
      }
      
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    } else {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }
    
    /*
    if (!empty()) {
      foreach () {

      }
    }
    */
    // Authentication
    if (!empty($login)) {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $login);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    
    if (!ini_get('open_basedir')) {
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // handle session
    
    if ($login) {
      curl_setopt($ch, CURLOPT_COOKIEJAR, DIR_CACHE.'uiep_cookie.txt'); //write, can be empty, but cause problems on some hosts
    } else {
      curl_setopt($ch, CURLOPT_COOKIEFILE, DIR_CACHE.'uiep_cookie.txt'); //read, can be empty, but cause problems on some hosts
    }
    
    // save into a file
    if ($copyPath) {
      unset($this->session->data['univimport_downloadFilename']);
      
      $localCopy = fopen($copyPath, "w+");
      //curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
      
      // curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
      curl_setopt($ch, CURLOPT_FILE, $localCopy);
      curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'HandleHeaderLine'));
    } else {
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }
    $res = curl_exec($ch);
    
    if (is_string($res) && substr($res, 0, 1) == '{') {
      $resJson = @json_decode($res);
    }
    
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($debug) {
      echo 'Return code: '.$httpCode.'<br/>';
      echo 'Query result: '.$res.'<br/>';
    }
    
    if (in_array($httpCode, array(401, 403, 404, 405, 406, 429, 500, 503, 504))) {
      sleep(1);
      header('Content-type: application/json');
      
      if (isset($resJson) && isset($resJson->description)) {
        $err = 'Error '.$httpCode.': '. $resJson->description;
      } else {
        switch ($httpCode) {
          case 401: $err = 'Error 401: API call is unauthorized'; break;
          case 403: $err = 'Error 403: API call is forbidden'; break;
          case 404: $err = 'Error 404: Not found'; break;
          case 405: $err = 'Error 405: Method not allowed ('.$method.')'; break;
          case 406: $err = 'Error 406: Not acceptable'; break;
          case 429: $err = 'Error 429: API call quota exceeded'; break;
          case 500: $err = 'Error 500: Server error'; break;
          case 503: $err = 'Error 503: Server error'; break;
          case 504: $err = 'Error 504: No response from server'; break;
        }
      }
      
      curl_close($ch);
      
      if ($copyPath) {
        fclose($localCopy);
      }
      
      throw new Exception(sprintf($this->language->get('error_curl_api'), $err, $url));
      
      //echo json_encode(array('file_error' => sprintf($this->language->get('error_curl_api'), $err, $url)));
      die;
    }
    
    if ($res) {
      curl_close($ch);
      
      if ($copyPath) {
        // copy the file
        fclose($localCopy);
        
        if (!empty($this->session->data['univimport_downloadFilename'])) {
          $newFile = dirname($copyPath).'/'.$this->session->data['univimport_downloadFilename'];
          rename($copyPath, $newFile);
          $this->session->data['univimport_temp_file'] = $newFile;
        } else {
          $this->session->data['univimport_temp_file'] = $copyPath;
        }
      } else if ($getPath) {
        // get content of the file
        if (!class_exists('JsonMachine')) { 
          require_once DIR_SYSTEM.'library/JsonMachine/Loader.php'; 
          require_once DIR_SYSTEM.'library/JsonMachine/StringBytes.php'; 
        }
        
        if (strpos($contentType, 'application/json') !== false) {
          $jsonArray = \JsonMachine\JsonMachine::fromString($res);
          
          $dataFoundInGivenPath = true;
          
          if (!empty($getPath)) {
            $jsonPath = explode('/', $getPath);
            
            $dataFoundInGivenPath = false;
            $result = array();
            foreach ($jsonArray as $jsonKey => $jsonVal) {
              // level 1
              if ($jsonKey == $jsonPath[0] || $jsonPath[0] == '*') {
                // level 2
                if (isset($jsonPath[1]) && is_array($jsonVal)) {
                  foreach ($jsonVal as $jsonKey1 => $jsonVal1) {
                    if ($jsonKey1 == $jsonPath[1] || $jsonPath[1] == '*') {
                      // level 3
                      if (isset($jsonPath[2]) && is_array($jsonVal1)) {
                        foreach ($jsonVal1 as $jsonKey2 => $jsonVal2) {
                          if ($jsonKey2 == $jsonPath[2] || $jsonPath[2] == '*') {
                            // level 4
                            if (isset($jsonPath[3]) && is_array($jsonVal2)) {
                              foreach ($jsonVal2 as $jsonKey3 => $jsonVal3) {
                                if ($jsonKey3 == $jsonPath[3] || $jsonPath[3] == '*') {
                                  $result[] = $this->model_gkd_import_tool->arrayFlat($jsonVal3);
                                }
                              }
                            } else {
                              $result[] = $this->model_gkd_import_tool->arrayFlat($jsonVal2);
                            }
                          }
                        }
                      } else {
                        $result[] = $this->model_gkd_import_tool->arrayFlat($jsonVal1);
                      }
                    }
                  }
                } else {
                  $result[] = $this->model_gkd_import_tool->arrayFlat($jsonVal);
                }
              }
            }
          } else {
            foreach ($jsonArray as $json) {
              $result[] = $this->model_gkd_import_tool->arrayFlat($json);
            }
          }
          
          $res = $result;
        } else if (strpos($contentType, 'application/xml') !== false) {
          $xml = new SimpleXMLElement($res);
          //$result = $xml->xpath($getPath);
          $result = $this->model_tool_universal_import->XML2Array($xml);
          
          $res = $this->model_gkd_import_handler->getArrayPath($result, $getPath, false);
        }
      }
    } else {
      if (curl_errno($ch)) {
        sleep(1);
        
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($copyPath) {
          fclose($localCopy);
        }
        
        throw new Exception(sprintf($this->language->get('error_curl'), $curl_error, $url));
        
        //header('Content-type: application/json');
        //echo json_encode(array('file_error' => sprintf($this->language->get('error_curl'), curl_error($ch), $url)));
        
        die;
      }
    }
    
    if ($debug) {
      echo '</div>';
    }
    
    if ($copyPath) {
      return true;
    } else {
      return $res;
    }
  }
  
  public function HandleHeaderLine($ch, $header_line) {
    if (preg_match('/^Content-Disposition: .*?filename=(?<f>[^\s]+|\x22[^\x22]+\x22)\x3B?.*$/m', $header_line, $mDispo)) {
      $filename = trim($mDispo['f'],' ";');

      $this->session->data['univimport_downloadFilename'] = $filename;
      //return false;
    }
    
    return strlen($header_line);
  }

  public function arrayFlat($array, $prefix = '', $max_level = 1, $level = 0) {
    $result = array();
    
    if ($level >= $max_level) return array();
    
    if (is_scalar($array) && !$level) {
      return $array;
    } else if (!is_array($array)) {
      return array($array);
    }
    
    foreach ($array as $key => $value) {
      $new_key = $prefix . (empty($prefix) ? '' : '/') . $key;

      if (is_array($value)) {
        $result[$new_key] = $value;
        $result = array_merge($result, $this->arrayFlat($value, $new_key, $max_level, ++$level));
      } else {
        $result[$new_key] = $value;
      }
    }

    return $result;
  }
  
  public function validateCondition($operator, $value, $compare) {
    if (
        ($operator == '='   && $compare == $value) ||
        ($operator == '!='  && $compare != $value) ||
        ($operator == '~'   && strpos($value, $compare) !== false) ||
        ($operator == '!~'  && strpos($value, $compare) === false) ||
        ($operator == '>'   && $compare > $value) ||
        ($operator == '<'   && $compare < $value) ) {
          return true;
        }
    
    return false;
  }
  
   public function updateArray($line, $path, $val) {
    if (strpos($path, '/')) {
      $arrItems = explode('/', $path);
      
      $arrayVal = array();
      
      $arrayVal = $this->recursiveAppendChild($arrayVal, $arrItems, $val);

      $line = array_replace_recursive($line, $arrayVal);
    }
    
    return $line;
  }
  
  private function recursiveAppendChild($arr, $path, $values, $valKey = null) {
    if (empty($path)) {
      if (!is_null($valKey)) {
        return $values[$valKey];
      }
      
      return array();
    }
    
    $newKey = array_shift($path);
    
    if ($newKey == '*') {
      foreach ($values as $valKey => $v) {
        $arr[$valKey] = array();
        $arr[$valKey] = $this->recursiveAppendChild($arr[$valKey], $path, $values, $valKey);
      }
    } else {
      $arr[$newKey] = array();
      $arr[$newKey] = $this->recursiveAppendChild($arr[$newKey], $path, $values, $valKey);
    }

    return $arr;
  }
}