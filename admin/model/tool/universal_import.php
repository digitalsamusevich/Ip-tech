<?php
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

class GkdSkipException extends Exception { }

function obuiErrorHandler($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
      return false;
    }
    
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

class ModelToolUniversalImport extends Model {
  const benchmark = false;
  
  private $simulation = true;
  private $line_decay = 1;
  
  private $processed = array(
    'processed' => 0,
    'inserted' => 0,
    'updated' => 0,
    'disabled' => 0,
    'deleted' => 0,
    'skipped' => 0,
    'error' => 0,
  );
  
  private $pre_processed = false;
  private $file;
  private $fileHandle;
  private $encoding;
  private $handler;
  private $filetype;
  private $xml_node;
  private $csv_separator;
  private $processedIdArray;
  private $processedIdFile;
  private $xfn_multiple_separator = array();
  private $token;
  private $order_statuses = array();
  private $currencyCodeToId = array();
  private $storeIdToName = array();
  private $ocCurrencyRates = array();
  private $currencyRates = array();
    
  public function pre_process($config) {
    // extra function handling before populate
    if ($this->pre_processed) return;
    
    // disable config options
    if (!empty($config['disable_cfg'])) {
      $toDisable = explode(',', $config['disable_cfg']);
      
      foreach ($toDisable as $cfgOption) {
        $this->config->set(trim($cfgOption), false);
      }
    }
    
    $type = str_replace('_update', '', $config['import_type']);
    
    if (in_array($type, array('product', 'category', 'information', 'manufacturer'))) {
      $this->language->load('catalog/'.$type);
    }
    
    // extra functions
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'multiple_separator') {
            switch ($func_values['value']) {
              case '[\n]': $func_values['value'] = "\n"; break;
              case '[\t]': $func_values['value'] = "\t"; break;
              default: break;
            }
            $this->xfn_multiple_separator[$func_values['field']] = $func_values['value'];
          }
        }
      }
    }
    
    $this->pre_processed = true;
  }
  
  public function __construct($registry) {
		parent::__construct($registry);
    
    $this->load->model('gkd_import/tool');
    $this->load->model('gkd_import/handler');
    $this->tool = $this->model_gkd_import_tool->getObject();
    $this->handler = $this->model_gkd_import_handler->getObject();
    
    if (isset($this->session->data['user_token'])) {
      $this->token = 'user_token='.$this->session->data['user_token'];
    } else if (isset($this->session->data['token'])) {
      $this->token = 'token='.$this->session->data['token'];
    }
  }
  
  public function process($config, $limit = null) {
    if (!$limit) {
      $limit = 200;
      
      if ((int) $this->config->get('gkd_impexp_batch_imp') > 0) {
        $limit = (int) $this->config->get('gkd_impexp_batch_imp');
      }
    }
    
    if (defined('GKD_CRON')) {
      $this->simulation = $this->handler->simulation = false;
    } else if ($this->user->hasPermission('modify', 'module/universal_import')) {
      $this->simulation = $this->handler->simulation = !empty($config['simulation']);
    } else {
      $this->simulation = $this->handler->simulation = $config['simulation'] = true;
    }
    
    $simu_row = $this->simulation ? 'simu' : 'rows';
    
    $this->language->load('module/universal_import');
    
    set_error_handler('obuiErrorHandler');
    //file_put_contents(DIR_SYSTEM . 'logs/import.log', $res . "\n", FILE_APPEND | LOCK_EX);
    
    $this->filetype = !empty($config['import_filetype']) ? $config['import_filetype'] : strtolower(pathinfo($config['import_file'], PATHINFO_EXTENSION));
    $this->handler->filetype = $this->filetype;
    
    if (in_array($this->filetype, array('csv', 'txt', 'tsv'))) {
      $this->csv_separator = !empty($config['csv_separator']) ? $config['csv_separator'] : ',';
      
      if ($this->csv_separator == 'tab') {
        $this->csv_separator = "\t";
      }
      
      $this->encoding = !empty($config['encoding']) ? $config['encoding'] : '';
    } else if ($this->filetype == 'xml') {
      /*
      $this->xml_node = $config['xml_node'];
      
      if (true) {
        if (substr($this->xml_node, 0, 1) != '/') {
          $this->xml_node = '//'.$this->xml_node;
        }
      }
      */
    }
    
    $type = $config['import_type'];
    $subtype = !empty($config['import_subtype']) ? '_'.$config['import_subtype'] : '';
    
    if (!in_array($type, array('product', 'product_update', 'order', 'order_status_update', 'category', 'information', 'manufacturer', 'customer', 'attribute', 'filter', 'restore'))) {
      //die('Invalid type');
    }
    
    if (in_array($type, array('product', 'product_update', 'category', 'information', 'manufacturer'))) {
      $this->load->model('catalog/'.str_replace('_update', '', $type));
    } else if ($type == 'customer') {
      $this->load->model((version_compare(VERSION, '2.1', '>=') ? 'customer':'sale').'/customer');
    } else if (in_array($type, array('order', 'order_status_update'))) {
      $this->load->model('sale/order');
      $this->load->model('gkd_import/order');
    }
    
    $this->session->data['obui_errors'] = array();
    $this->session->data['obui_log'] = array();
    
    if (!empty($this->session->data['univimport_temp_file'])) {
      $config['import_file'] = $this->session->data['univimport_temp_file'];
    } else if ($config['import_source'] == 'upload') {
      $config['import_file'] = DIR_CACHE.'universal_import/'.str_replace(array('../', '..\\'), '', $config['import_file']);
    } else if ($config['import_source'] == 'ftp') {
      $config['import_file'] = $config['import_ftp'].$config['import_file'];
    }
    
    // init processedIdFile
    if ($config['profile']) {
      $processedIdFilename = $config['profile'];
    } else {
      $processedIdFilename = $config['import_file'];
    }
    
    $this->processedIdFile = DIR_CACHE.'universal_import/'.md5($processedIdFilename).'.proc';
    
    // first init
    if (empty($this->session->data['obui_current_line'])) {
      $this->deleteIdentifierFile();
      
      $this->session->data['obui_deleted_ids'] = array();
      
      // delete all items or init id array
      if (!empty($config['delete'])) {
        if ($config['delete'] == 'all' || $config['delete'] == 'batch') {
          $this->delete($config);
          
          // do not return if in cron context
          if (defined('GKD_CRON')) {
            $this->session->data['obui_current_line'] = 0;
          } else {
            $this->session->data['obui_current_line'] = 'preproc';
            return $this->tool->logs;
          }
        } else {
          $this->session->data['obui_no_delete'] = array();
          $this->session->data['obui_delete_brand'] = array();
        }
      }
      
      // set current position for tail mode
      if ($config['row_start'] < 0) {
        $total_rows = $this->model_tool_universal_import->getTotalRows($config['import_file'], false, !empty($config['xml_node']) ? $config['xml_node'] : '', '', !empty($config['sheet']) ? (int) $config['sheet'] : 0, $config);
        
        $config['row_start'] = $total_rows + $config['row_start'];
        
        if ($config['row_start'] < 0) {
          $config['row_start'] = 0;
        }
      }
    } else if ($this->session->data['obui_current_line'] == 'preproc') {
      $this->session->data['obui_current_line'] = 0;
      
      // set current position for tail mode
      if ($config['row_start'] < 0) {
        $total_rows = $this->model_tool_universal_import->getTotalRows($config['import_file'], false, !empty($config['xml_node']) ? $config['xml_node'] : '', '', !empty($config['sheet']) ? (int) $config['sheet'] : 0, $config);
        
        $config['row_start'] = $total_rows + $config['row_start']+1;
        
        if ($config['row_start'] < 0) {
          $config['row_start'] = 0;
        }
      }
    }
    
    // load identifier file
    $this->loadIdentifierFile();
    
    // init import file
    $this->file = $this->loadFile($config['import_file'], $config['import_filetype'], (!empty($config['sheet']) ? $config['sheet'] : 0), $config);
    
    $first_row = $this->initFilePosition($this->file, $config);
    
    $usleep = $this->config->get('gkd_impexp_sleep');
    
    if (!empty($config['csv_header'])) {
      $this->line_decay = 2;
    }
    
    if ($this->file) {
      $i = 0;
      
      while ($i < $limit && ($line = $this->getNextRow($this->file, $config)) !== false) {
        if (!empty($config['row_end']) && $config['row_end']+1 <= $this->session->data['obui_current_line']) {
          break;
        }
        
        $i++;
        if ($first_row) {
          $first_row = false;
          continue;
        }
        
        // skip empty line
        if (!count(array_filter((array) $line))) {
          $this->session->data['obui_processed']['processed']++;
          $this->session->data['obui_processed']['skipped']++;
          
          $this->tool->log(array(
            'row' => $this->session->data['obui_current_line'],
            'status' => 'skipped',
            'title' => $this->language->get('text_'.$simu_row.'_skipped'),
            'msg' =>  $this->language->get('text_empty_line_skip'),
          ));
          
          continue;
        }
        
        try {
          if ($usleep) {
            usleep((int) $usleep * 1000); // 1 000 000 = 1s
          }
          
          $resArray = $this->{'process_' . $type . $subtype}($config, $line);
          
          if (empty($resArray['gkdIsResArray'])) {
            $resArray = array($resArray);
          } else {
            unset($resArray['gkdIsResArray']);
          }
          
          foreach ($resArray as $res) {
            $this->tool->log(array(
              'row' => $this->session->data['obui_current_line'],
              'status' => isset($res['row_status']) ? $res['row_status'] : 'unknown',
              'title' => $this->language->get('text_'.$simu_row.'_'.$res['row_status']),
              'msg' => !empty($res['row_msg']) ? $res['row_msg'] : '',
            ));
          }
        } catch (Exception $e) {
          isset($this->session->data['obui_processed']['processed']) && $this->session->data['obui_processed']['processed']++;
          isset($this->session->data['obui_processed']['processed']) && $this->session->data['obui_processed']['error']++;
          //$this->session->data['obui_errors'][] = $e->getMessage() . ' - line: ' . $e->getLine() . ' - file: ' . $e->getFile();
          
          $extraErrorInfo = '';
          
          // extra info about error
          if (strpos($e->getMessage(), '2147483647') !== false) {
            $extraErrorInfo = '<br/>This error is because you have reached maximum number that can contain an id field in the database.<br/>To fix you would have to increase the INT number for this field in this table schema (and all other tables where it is used), you can do that in phpmyadmin, by default it is INT 11, so put INT 15 to have more possible values.';
          }
          
          if (strpos($e->getMessage(), 'Undefined index:') !== false) {
            preg_match('/Undefined index: (.*)$/', $e->getMessage(), $forCustomField);
            $extraErrorInfo = '<br/>This error is generally because you have some custom module that tries to insert some data into the database<br/>Try to set in Step 3 > Extra functions > Custom fields a custom field named "<b>'.$forCustomField[1].'</b>"';
          }
          
          if (strpos($e->getMessage(), 'real_escape_string()') !== false) {
            $extraErrorInfo = '<br/>This error is because there is some array sent to insert in database, make sure that you do not assign an array of values in fields that expect a string.';
          }
          
          $this->tool->log(array(
            'row' => $this->session->data['obui_current_line'],
            'status' => 'error',
            'title' => $this->language->get('text_simu_error'),
            'msg' => $e->getMessage() . ' in file ' . $e->getFile() . ' on line ' . $e->getLine() . $extraErrorInfo,
          ));
        }
      }
      
      // close file
      if (in_array($this->filetype, array('csv', 'txt', 'tsv', 'ods', 'xlsx')) && isset($this->fileHandle)) {
        $this->fileHandle->close();
      }
      
      if (in_array($this->filetype, array('csv_', 'sql'))) {
        fclose($this->file);
      }
      
      if (empty($this->tool->logs) && $line === false) {
        return false;
      }
    } else {
      // error opening the file.
    }
    
    restore_error_handler();

    return $this->tool->logs;
    //return 1;
  }
  
  public function delete($config) {
    if (isset($this->session->data['user_token'])) {
      $this->token = 'user_token='.$this->session->data['user_token'];
    } else if (isset($this->session->data['token'])) {
      $this->token = 'token='.$this->session->data['token'];
    }
    
    $type = $this->db->escape(str_replace('_update', '', $config['import_type']));
    $mode = $config['delete'];
    $action = $config['delete_action'];
    
    if ($type == 'car_shop') {
      $type = ($config['item_identifier'] == 'carshop_list_id') ? 'carshop_list' : 'carshop_part';
    }
    
    if (defined('GKD_CRON')) {
      $this->simulation = $this->handler->simulation = false;
    } else if ($this->user->hasPermission('modify', 'module/universal_import')) {
      $this->simulation = $this->handler->simulation = !empty($config['simulation']);
    } else {
      $this->simulation = $this->handler->simulation = true;
    }
    
    $simu_row = $this->simulation ? 'simu' : 'rows';
    
    $deleted_array = array();
    
    $where = '';
    
    if (!$mode) {
      return $this->tool->logs;
    }
    
    if ($mode == 'missing' && !empty($this->session->data['obui_no_delete'])) {
      $where = " WHERE " . $type . "_id NOT IN ('" . implode("','", array_map('addslashes', $this->session->data['obui_no_delete'])) . "')";
    } else if ($mode == 'batch') {
      $where = " WHERE `import_batch` = '" . $this->db->escape($config['delete_batch']) . "'";
    } else if ($mode == 'missing_brand') {
      if (!empty($this->session->data['obui_no_delete']) && !empty($this->session->data['obui_delete_brand'])) {
        $where = " WHERE " . $type . "_id NOT IN ('" . implode("','", array_map('addslashes', $this->session->data['obui_no_delete'])) . "') AND manufacturer_id IN ('" . implode("','", $this->session->data['obui_delete_brand']) . "')";
      } else {
        // no brands? do not run delete
        return $this->tool->logs;
      }
    }
    
    if (!empty($config['delete_batch'])) {
      $where .= $where ? ' AND ' : ' WHERE ';
      
      if ($config['delete_batch'] == 'defined') {
        $where .= "`import_batch` <> ''";
      } else if ($config['delete_batch'] == 'empty') {
        $where .= "`import_batch` = ''";
      } else {
        $where .= "`import_batch` = '" . $this->db->escape($config['delete_batch']) . "'";
      }
    }
    
    if (!empty($config['delete_condition']['db_field'])) {
      $func_values = $config['delete_condition'];
      
      $where .= $where ? ' AND ' : ' WHERE ';

      if ($func_values['comparator'] == 'is_equal') {
        $where .= "`".$this->db->escape($func_values['db_field'])."` = '".$this->db->escape($func_values['value'])."'";
      } else if ($func_values['comparator'] == 'is_equal_list') {
        $not_in = array();
        
        foreach (explode('|', $func_values['value']) as $v) {
          $not_in[] = $this->db->escape($v);
        }
        
        $where .= "`".$this->db->escape($func_values['db_field'])."` IN ('".implode( "', '" , array_map('addslashes', $not_in))."')";
      } else if ($func_values['comparator'] == 'is_not_equal_list') {
        $not_in = array();
        
        foreach (explode('|', $func_values['value']) as $v) {
          $not_in[] = $this->db->escape($v);
        }
        
        $where .= "`".$this->db->escape($func_values['db_field'])."` NOT IN ('".implode( "', '" , array_map('addslashes', $not_in))."')";
      } else if ($func_values['comparator'] == 'is_not_equal') {
        $where .= "`".$this->db->escape($func_values['db_field'])."` <> '".$this->db->escape($func_values['value'])."'";
      } else if ($func_values['comparator'] == 'is_greater') {
        $where .= "`".$this->db->escape($func_values['db_field'])."` > '".$this->db->escape($func_values['value'])."'";
      } else if ($func_values['comparator'] == 'is_lower') {
        $where .= "`".$this->db->escape($func_values['db_field'])."` < '".$this->db->escape($func_values['value'])."'";
      } else if ($func_values['comparator'] == 'contain') {
        $where .= "`".$this->db->escape($func_values['db_field'])."` LIKE '%".$this->db->escape($func_values['value'])."%'";
      } else if ($func_values['comparator'] == 'not_contain') {
        $where .= "`".$this->db->escape($func_values['db_field'])."` NOT LIKE '%".$this->db->escape($func_values['value'])."%'";
      }
    }
    
    if (!empty($config['no_delete_skipped']) && isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'skip_db') {
            if ($func_values['fieldval'] !== '') continue;
            
            $where .= $where ? ' AND ' : ' WHERE ';
      
            if ($func_values['comparator'] == 'is_equal') {
              $where .= "`".$this->db->escape($func_values['db_field'])."` <> '".$this->db->escape($func_values['value'])."'";
            } else if ($func_values['comparator'] == 'is_equal_list') {
              $not_in = array();
              
              foreach (explode('|', $func_values['value']) as $v) {
                $not_in[] = $this->db->escape($v);
              }
              
              $where .= "`".$this->db->escape($func_values['db_field'])."` NOT IN ('".implode( "', '" , array_map('addslashes', $not_in))."')";
            } else if ($func_values['comparator'] == 'is_not_equal') {
              $where .= "`".$this->db->escape($func_values['db_field'])."` = '".$this->db->escape($func_values['value'])."'";
            } else if ($func_values['comparator'] == 'is_greater') {
              $where .= "`".$this->db->escape($func_values['db_field'])."` < '".$this->db->escape($func_values['value'])."'";
            } else if ($func_values['comparator'] == 'is_lower') {
              $where .= "`".$this->db->escape($func_values['db_field'])."` > '".$this->db->escape($func_values['value'])."'";
            } else if ($func_values['comparator'] == 'contain') {
              $where .= "`".$this->db->escape($func_values['db_field'])."` NOT LIKE '%".$this->db->escape($func_values['value'])."%'";
            } else if ($func_values['comparator'] == 'not_contain') {
              $where .= "`".$this->db->escape($func_values['db_field'])."` LIKE '%".$this->db->escape($func_values['value'])."%'";
            }
          }
        }
      }
    }
    
    // if simulation count total
    /*
    if ($this->simulation) {
      $count = $this->db->query("SELECT COUNT(" . $type . "_id) AS total FROM " . DB_PREFIX . $type . $where)->row;
      
      if ($action == 'delete') {
        $this->session->data['obui_processed']['deleted'] = $count['total'];
      } else {
        $this->session->data['obui_processed']['disabled'] = $count['total'];
      }
    } 
    */
    
    if (!$this->simulation) {
      if (!isset($this->session->data['obui_processed'])) {
        $this->session->data['obui_processed'] = array();
      }
      
      if (!in_array($type, array('product', 'category', 'information', 'manufacturer', 'customer', 'filter', 'attribute'))) {
        //die('Invalid type');
      }
      
      if (in_array($type, array('product', 'product_update', 'category', 'information', 'manufacturer', 'filter', 'review', 'attribute'))) {
        $this->load->model('catalog/'.str_replace('_update', '', $type));
        $model = 'model_catalog_'.$type;
      } else if ($type == 'customer') {
        $this->load->model((version_compare(VERSION, '2.1', '>=') ? 'customer':'sale').'/customer');
        $model = 'model_'.(version_compare(VERSION, '2.1', '>=') ? 'customer':'sale').'_customer';
      }
    }

    $to_delete = $this->db->query("SELECT " . $type . "_id FROM " . DB_PREFIX . $type . $where)->rows;
    
    if ($action == 'delete') {
      foreach ($to_delete as $del) {
        if (!$this->simulation) {
          $this->{$model}->{'delete'.ucfirst($type)}($del[$type.'_id']);
        }
        
        $deleted_array[] = $del[$type.'_id'];
      }
      
      $this->session->data['obui_processed']['deleted'] += count($deleted_array);
    } else if ($action == 'disable') {
      foreach ($to_delete as $del) {
        if (!$this->simulation) {
          $this->db->query("UPDATE " . DB_PREFIX . $type . " SET status = 0 WHERE " . $type . "_id = '" . (int) $del[$type.'_id'] . "'");
        }
        
        $deleted_array[] = $del[$type.'_id'];
      }
      
      $this->session->data['obui_processed']['disabled'] += count($deleted_array);
    } else if ($action == 'zero') {
      foreach ($to_delete as $del) {
        if (!$this->simulation) {
          $this->db->query("UPDATE " . DB_PREFIX . $type . " SET quantity = 0 WHERE " . $type . "_id = '" . (int) $del[$type.'_id'] . "'");
        }
        
        $deleted_array[] = $del[$type.'_id'];
      }
      
      $this->session->data['obui_processed']['disabled'] += count($deleted_array);
    }
    
    $deleted_ids = '';
    foreach ($deleted_array as $deleted) {
      if (defined('GKD_CRON')) {
        $deleted_ids .= $deleted.', ';
      } else {
        $deleted_ids .= '<a target="_blank" href="'.$this->url->link('catalog/'.$type.'/edit', $type.'_id='.$deleted.'&' . $this->token, 'SSL').'">'.$deleted.'</a>, ';
      }
    }
    
    $deleted_ids = rtrim($deleted_ids, ', ');
    
    if (!$deleted_ids) {
      $deleted_ids = $this->language->get('text_nothing_deleted');
    }
    
    if ($action == 'delete') {
      $this->tool->log(array(
        'row' => '',
        'status' => 'deleted',
        'title' => $this->language->get('text_'.$simu_row.'_deleted'),
        'msg' => ($mode == 'all' && empty($config['delete_batch'])) ? $this->language->get('text_delete_all') : $deleted_ids,
      ));
    } else if ($action == 'zero') {
      $this->tool->log(array(
        'row' => '',
        'status' => 'disabled',
        'title' => $this->language->get('text_'.$simu_row.'_qtyzero'),
        'msg' => ($mode == 'all' && empty($config['delete_batch'])) ? $this->language->get('text_delete_all') : $deleted_ids,
      ));
    } else {
      $this->tool->log(array(
        'row' => '',
        'status' => 'disabled',
        'title' => $this->language->get('text_'.$simu_row.'_disabled'),
        'msg' => ($mode == 'all' && empty($config['delete_batch'])) ? $this->language->get('text_delete_all') : $deleted_ids,
      ));
    }
    
    return $this->tool->logs;
  }
  
  public function process_restore($config, $line) {
    if (!$this->simulation) {
      $line = trim($line);

			if ($line) {
				$res = $this->db->query($line);
			}
    }
    
    $data['row_status'] = 'inserted';
    //$data['row_msg'] = $line;
    $data['query'] = $line;
    
    $this->session->data['obui_processed']['inserted']++;
    $this->session->data['obui_processed']['processed']++;
    return $data;
  }
  
  public function process_product($config, $line) {
    $this->pre_process($config);
    
    if (empty($this->filetype)) {
      $this->filetype = !empty($config['import_filetype']) ? $config['import_filetype'] : strtolower(pathinfo($config['import_file'], PATHINFO_EXTENSION));
      $this->handler->filetype = $this->filetype;
    }
    
    $config['columns_bindings'] = $config['columns'];
    
    // get default value if array is empty
    $config['columns']['product_category'] = array_filter($config['columns']['product_category'], array($this, 'array_filter_column'));
    
    if (empty($config['columns']['product_category'])) {
      $config['columns']['product_category'] = '';
    }
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    // get default name for identifier
    if (in_array($config['item_identifier'], array('name', 'description'))) {
      $defaultLangDesc = reset($data['product_description']);
      $data[$config['item_identifier']] = $defaultLangDesc[$config['item_identifier']];
      unset($defaultLangDesc);
    } else if ($config['item_identifier'] == 'image') {
      $data['image'] = $this->handler->imageHandler('image', $config, false, false, true);
    }
    
    $item_id = $this->itemExists($config['import_type'], $config, $data);
    
    if ($item_id && $config['item_exists'] == 'option') {
      $is_option = true;
    } else if ($config['item_exists'] == 'update_option') {
      $saveIdentifier = true;
      if (empty($config['option_insert_type'])) {
        $config['option_insert_type'] = 'update'; // important to keep product_option_id
      }
      $config['reset_options'] = true;
      
      if ($this->itemHasProcessed($config['import_type'], $config['item_identifier'], $data)) {
        $is_option = true;
        $config['item_exists'] = 'option';
      } else {
        $config['item_exists'] = 'soft_update';
      }
    }
    
    // handle extra function before populated values (mainly for skip before inserting data)
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
             if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                  $data = array();
                  
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else if (isset($func_values['action']) && $func_values['action'] == 'zero') {
                  $data = array();
                  
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_product->deleteProduct($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          } else if ($func_name == 'skip_db') {
            if (!isset($item_data)) {
              $item_data = $this->model_catalog_product->getProduct($item_id);
            }
            
            if (!isset($item_data[$func_values['db_field']])) continue;
            
            if (($func_values['comparator'] == 'is_equal' && $item_data[$func_values['db_field']] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $item_data[$func_values['db_field']] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($item_data[$func_values['db_field']], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $item_data[$func_values['db_field']] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $item_data[$func_values['db_field']] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . strtolower($this->language->get('entry_'.$func_values['db_field'])) . ' ('.$item_data[$func_values['db_field']].') ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    // product will be updated, prepare values
    if (($item_id && $config['item_exists'] == 'soft_update') || ($item_id && $config['item_exists'] == 'update') || ($item_id && $config['item_exists'] == 'option') || (!$item_id && $config['item_not_exists'] == 'insert')) {
      
      if ($item_id && $config['item_exists'] == 'soft_update') {
        $config['columns'] = $this->recursive_array_intersect_key($config['columns'], $this->walk_recursive_remove($config['columns_bindings']));
        
        $data = &$config['columns'];

        if ($config['item_identifier'] == 'image') {
          $data['image'] = $this->handler->imageHandler('image', $config, false, false, true);
        }
      } else {
        if (isset($data['discountByCustomerGroup'])) {
          $data['discountByCustomerGroup'] = $this->walk_recursive_remove($data['discountByCustomerGroup']);
        }
        
        if (isset($data['specialByCustomerGroup'])) {
          $data['specialByCustomerGroup'] = $this->walk_recursive_remove($data['specialByCustomerGroup']);
        }
      }
      
      /*
      if ($config['item_exists'] == 'soft_update') {
        $data['gkd_extra_fields'] = !empty($config['extra']) ? $config['extra'] : array();
        $data['gkd_extra_desc_fields'] = !empty($config['extraml']) ? $config['extraml'] : array();
      } else {
        $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
        $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      }
      */
      
      $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
      $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      
      $data['import_batch'] = !empty($config['import_label']) ? $config['import_label'] : $this->config->get('gkd_impexp_default_label');
      $data['import_batch'] = str_replace(array('[profile]', '[day]', '[month]', '[year]'), array(!empty($config['profile']) ? $config['profile'] : 'Import', date('d'), date('m'), date('Y')), $data['import_batch']);
      
      // data formatters
      try {
        if (isset($data['product_store']))        $data['product_store'] = $this->handler->storeHandler('product_store', $config); // @todo: detect by name
        if (isset($data['product_category']))     $data['product_category'] = $this->handler->categoryHandler('product_category', $config, $item_id);
        
        $orig_image = '';
        
        if (isset($data['image']) && $config['item_identifier'] != 'image') {
          if (empty($config['image_insert_type']) || (!empty($config['image_insert_type']) && $config['image_insert_type'] == 'rm_add' && !(in_array($item_id, $this->session->data['obui_processed_ids']) || $this->identifierExists($item_id)))) {
            $orig_image = $data['image'];
            $data['image'] = $this->handler->imageHandler('image', $config, false, $item_id);
          } else {
            unset($data['image']);
          }
        }
        
        if (isset($data['product_image']))        $data['product_image'] = $this->handler->imageHandler('product_image', $config, true, $item_id, false, $orig_image);
        
        if (isset($data['seo_canonical']))        $data['seo_canonical'] = $this->handler->parentCategoryHandler('seo_canonical', $config);
        if (isset($data['price']))                $data['price'] = $this->tool->floatValue($data['price']);
        if (isset($data['tax_class_id']))         $data['tax_class_id'] = $this->handler->taxClassHandler('tax_class_id', $config);
        if (isset($data['weight']))               $data['weight'] = $this->tool->floatValue($data['weight']);
        if (isset($data['width']))                $data['width'] = $this->tool->floatValue($data['width']);
        if (isset($data['height']))               $data['height'] = $this->tool->floatValue($data['height']);
        if (isset($data['manufacturer_id']))      $data['manufacturer_id'] = $this->handler->manufacturerHandler($config);
        if (isset($data['stock_status_id']))      $data['stock_status_id'] = $this->handler->stockHandler('stock_status_id', $config);
        if (isset($data['product_related']))      $data['product_related'] = $this->handler->relatedHandler('product_related', $config); // @todo: detect by name
        if (isset($data['product_option']) ||
          !empty($config['option_fields']))       $data['product_option'] = $this->handler->optionHandler('product_option', $config, $line, $item_id); // 
        if (isset($data['product_attribute']) ||
          !empty($config['attribute_fields']))    $data['product_attribute'] = $this->handler->attributeHandler('product_attribute', $config, $line); // header > value | <attribute>:<text> | <attribute_group>:<attribute>:<text>
        if (isset($data['product_filter']))       $data['product_filter'] = $this->handler->filterHandler('product_filter', $config); // must be after option and attribute handlers
        if (isset($data['product_discount']) ||
          !empty($data['discountByCustomerGroup']))     $data['product_discount'] = $this->handler->discountHandler('product_discount', $config); // <customer_group_id>:<quantity>:<priority>:<price>:<date_start>:<date_end>
        if (isset($data['product_special']) ||
          !empty($data['specialByCustomerGroup']))      $data['product_special'] = $this->handler->specialHandler('product_special', $config); // <customer_group_id>:<priority>:<price>:<date_start>:<date_end>
        if (isset($data['product_download']))     $data['product_download'] = $this->handler->downloadHandler('product_download', $config);
        if (isset($data['product_public_download']))     $data['product_public_download'] = $this->handler->publicDownloadHandler('product_public_download', $config);
        if (isset($data['product_layout']))       $data['product_layout'] = $this->handler->layoutHandler('product_layout', $config); // detect by name or ID
        if (isset($data['status']))               $data['status'] = $this->handler->booleanHandler('status', $config); // enabled/disabled, on/off, true/false, 1/0
        
        foreach (array('model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 'length', 'width', 'height') as $fieldType) {
          if (isset($data[$fieldType])) {
            $data[$fieldType] = $this->handler->forceString($fieldType, $data[$fieldType]);
          }
        }
      } catch (GkdSkipException $e) {
        $data['row_msg'] = $e->getMessage();
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['processed']++;
        $this->session->data['obui_processed']['skipped']++;
        return $data;
      }
      
      if (isset($data['product_special']) && $data['product_special'] == 'reset') {
        $data['product_special'] = array();
        $resetSpecial = true;
      }
      
      // unset unnecessary data
      unset($data['sub_product_category'], $data['discountByCustomerGroup'], $data['specialByCustomerGroup']);
      
      // data for product model
      $data['uiep_filter_to_category'] = !empty($config['filter_to_category']);
      
      // set default values for custom module compatibility
      if ($config['item_exists'] != 'soft_update') {
        $setDefault = array(
          'keyword', // seo modules
          'best',
          'priority',
          'frequency',
          'update_seo_url',
          // 'adwords_grouping',
          // 'gpf_status',
          // 'gtin',
          // 'google_product_category',
        );
        
        foreach ($setDefault as $v) {
          if (!isset($data[$v])) {
            $data[$v] = '';
          }
        }
      }

      // integrity checks
      /*
      if (empty($data['manufacturer_id'])) {
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        return $data;
      }
      if (empty($data['manufacturer_id'])) {
        throw new Exception('[skip] Manufacturer not defined for part model "' . $data['model'] . '" at line ' . $this->session->data['obui_current_line']);
      }
      */
      
      // unset if empty - only in replace mode
      if ($config['item_exists'] != 'soft_update') {
        foreach (array('product_store', 'product_option', 'product_discount', 'product_special', 'product_image', 'product_download', 'product_attribute',
                       'product_category', 'product_filter', 'product_related', 'product_reward', 'product_layout', 'product_recurrings') as $key) {
          if (empty($data[$key])) {
            unset($data[$key]);
          }
        }
      }
      
      if (!empty($resetSpecial)) {
        $data['product_special'] = array();
      }
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
             if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                    $data=array();
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else if (isset($func_values['action']) && $func_values['action'] == 'zero') {
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_product->deleteProduct($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          } else if ($func_name == 'skip_db') {
            if (!isset($item_data)) {
              $item_data = $this->model_catalog_product->getProduct($item_id);
            }
            
            if (!isset($item_data[$func_values['db_field']])) continue;
            
            if (($func_values['comparator'] == 'is_equal' && $item_data[$func_values['db_field']] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $item_data[$func_values['db_field']] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($item_data[$func_values['db_field']], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $item_data[$func_values['db_field']] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $item_data[$func_values['db_field']] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . strtolower($this->language->get('entry_'.$func_values['db_field'])) . ' ('.$item_data[$func_values['db_field']].') ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          } else if ($func_name == 'option') {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $is_option = true;
            }
          }
        }
      }
    }
    
    // sum option qty to product qty
    if ($config['item_exists'] == 'update' && !empty($config['option_qty_mode']) && !empty($data['product_option'])) {
      $data['quantity'] = !empty($data['quantity']) ? $data['quantity'] : 0;
      
      foreach ($data['product_option'] as $opt) {
        if (!empty($opt['product_option_value'])) {
          foreach ($opt['product_option_value'] as $optVal) {
            if (!empty($optVal['quantity'])) {
              $data['quantity'] += $optVal['quantity'];
            }
          }
        }
      }
    }
    
    // row is product option only
    if (!empty($is_option)) {
      if (isset($data['product_option'])) {
        $data = array(
          $config['item_identifier'] => $data[$config['item_identifier']],
          'product_option' => $data['product_option']
        );
      } else {
        $data = array(
          $config['item_identifier'] => $data[$config['item_identifier']]
        );
      }
      
      //if (($item_id || ($this->simulation && in_array($data[$config['item_identifier']], $this->session->data['obui_identifiers']))) && !empty($data['product_option'])) {
      if (($item_id || ($this->simulation && $this->itemHasProcessed($config['import_type'], $config['item_identifier'], $data))) && !empty($data['product_option'])) {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->load->model('gkd_import/product');
          
          $data['option_insert_type'] = isset($config['option_insert_type']) ? $config['option_insert_type'] : '';
          
          $this->model_gkd_import_product->addProductOption($item_id, $data);
          
          // recalculate product total quantity
          if (!empty($config['auto_qty'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".($config['auto_qty'] == 1 ? 0 : 'quantity')." + IFNULL((SELECT SUM(quantity) FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$item_id . "'), 0) WHERE product_id = '" . (int)$item_id . "'");
          }
        }
        
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
        $message = $this->language->get('text_insert_option');
      } else {
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        if (!$item_id) {
          $message = $this->language->get('text_skip_option_no_product');
        } else {
          $message = $this->language->get('text_skip_option_no_option');
        }
      }
      
      goto process_product_end;
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->model_catalog_product->editProduct($item_id, $data);
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else if ($config['item_exists'] == 'soft_update') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->load->model('gkd_import/product');
          $this->model_gkd_import_product->editProduct($item_id, $data, $config);
          
          // recalculate product total quantity
          if (!empty($config['auto_qty'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".($config['auto_qty'] == 1 ? 0 : 'quantity')." + IFNULL((SELECT SUM(quantity) FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$item_id . "'), 0) WHERE product_id = '" . (int)$item_id . "'");
          }
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
      
      if ($this->simulation || !empty($saveIdentifier)) {
        //$this->session->data['obui_identifiers'][] = isset($data[$config['item_identifier']]) ? $data[$config['item_identifier']] : $item_id;
        
        if (in_array($config['item_identifier'], array('name', 'title', 'description')) && isset($data['product_description']) && $prodDesc = reset($data['product_description'])) {
          $this->addProcessedIdentifier($prodDesc[$config['item_identifier']]);
        } else {
          $this->addProcessedIdentifier((isset($data[$config['item_identifier']]) ? $data[$config['item_identifier']] : $item_id));
        }
        //$this->addProcessedIdentifier($item_id);
      }
    } else {
      if ($config['item_not_exists'] == 'insert' && empty($item_to_delete)) {
        // save item identifier for simu
        if ($this->simulation || !empty($saveIdentifier)) {
          //$this->session->data['obui_identifiers'][] = $data[$config['item_identifier']];
          if (in_array($config['item_identifier'], array('name', 'title', 'description')) && isset($data['product_description']) && $prodDesc = reset($data['product_description'])) {
            $this->addProcessedIdentifier($prodDesc[$config['item_identifier']]);
          } else {
            $this->addProcessedIdentifier((isset($data[$config['item_identifier']]) ? $data[$config['item_identifier']] : $item_id));
          }
        }
        
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $item_id = $this->model_catalog_product->addProduct($data);
          
          // recalculate product total quantity
          if (!empty($config['auto_qty'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".($config['auto_qty'] == 1 ? 0 : 'quantity')." + IFNULL((SELECT SUM(quantity) FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$item_id . "'), 0) WHERE product_id = '" . (int)$item_id . "'");
          }
        }
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    process_product_end:
    
    // save processed item id for further use - only if necessary
    if ($item_id && (
          (!empty($config['category_insert_type']) && $config['category_insert_type'] == 'rm_add') ||
          (!empty($config['related_insert_type']) && $config['related_insert_type'] == 'rm_add') ||
          (!empty($config['image_insert_type']) && $config['image_insert_type'] == 'rm_add') ||
          (!empty($config['option_insert_type']) && $config['option_insert_type'] == 'rm_add') 
        ) && !in_array($item_id, $this->session->data['obui_processed_ids'])) {
      $this->session->data['obui_processed_ids'][] = $item_id;
    }
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if (!empty($data['product_description'][$this->config->get('config_language_id')]['name'])) {
      $item_name = $data['product_description'][$this->config->get('config_language_id')]['name'];
    } else if (!empty($data[$config['item_identifier']])) {
      $item_name = $data[$config['item_identifier']];
    } else if ($item_id) {
      $item_name = 'Product ID '.$item_id;
    } else {
      $item_name = '';
    }
    
    if (is_array($item_name)) {
      $item_name = '';
    }
    
    if ($item_id) {
      if (defined('GKD_CRON') || !is_numeric($item_id)) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $item_name);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '.$message;
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_name.'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $item_name;
    }
    
    return $data;
  }
  
  public function process_product_update($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    if (empty($config['option_identifier'])) {
      $data['import_batch'] = !empty($config['import_label']) ? $config['import_label'] : $this->config->get('gkd_impexp_default_label');
      $data['import_batch'] = str_replace(array('[profile]', '[day]', '[month]', '[year]'), array(!empty($config['profile']) ? $config['profile'] : 'Import', date('d'), date('m'), date('Y')), $data['import_batch']);
    }
    
    // unset if empty
    foreach (array('price', 'quantity', 'status') as $key) {
      if ($data[$key] === '') {
        unset($data[$key]);
      }
    }

    $data = $this->request->clean($data);
    
    $data = $this->walk_recursive_remove($data);
    
    $item_id = $this->itemExists('product', $config, $data, null, true);
    
    if (isset($data['status'])) $data['status'] = $this->handler->booleanHandler('status', $config); // enabled/disabled, on/off, true/false, 1/0
    if (isset($data['stock_status_id']))      $data['stock_status_id'] = $this->handler->stockHandler('stock_status_id', $config);
    if (isset($data['product_discount']) ||
      !empty($data['discountByCustomerGroup']))     $data['product_discount'] = $this->handler->discountHandler('product_discount', $config);
    if (isset($data['product_special']) ||
      !empty($data['specialByCustomerGroup']))      $data['product_special'] = $this->handler->specialHandler('product_special', $config); // <customer_group_id>:<priority>:<price>:<date_start>:<date_end>
    //$data['product_group'] = $this->handler->productGroupHandler('product_group', $config);
    
    // unset unnecessary data
    unset($data['discountByCustomerGroup'], $data['specialByCustomerGroup']);
    
    $update_values = $update_desc_values = array();
    
    foreach ($data as $field => $value) {
      if ($field == $config['item_identifier']) {
        continue;
      } else if (in_array($field, array('product_id', 'product_discount', 'product_special', 'product_description', 'option_identifier_value'))) {
        continue;
      } else if (!empty($config['option_identifier']) && !in_array($field, array('quantity', 'price'))) {
        continue;
      } else if (in_array($field, array('price', 'retail', 'map'))) {
        if ($field == 'price') {
          if (substr($value, 0, 1) == '=') {
            $price_prefix = '=';
            $value = str_replace('=', '', $value);
          } else {
            $price_prefix = !empty($value) && ($value < 0) ? '-' : '+';
          }
          $value = abs((float) $value);
        }
        
        $update_values[] = $this->db->escape($field) . " = '" . (float) $value . "' ";
      } else if (in_array($field, array('quantity'))) {
        if (empty($config['quantity_modifier'])) {
          $update_values[] = $this->db->escape($field) . " = '" . (int) $value . "' ";
        } else if ($config['quantity_modifier'] == '+') {
          $update_values[] = $this->db->escape($field) . " = " . $this->db->escape($field) . " + '" . (int) $value . "' ";
        } else if ($config['quantity_modifier'] == '-') {
          $update_values[] = $this->db->escape($field) . " = " . $this->db->escape($field) . " - '" . (int) $value . "' ";
        }
      } else {
        if (is_scalar($value)) {
          $update_values[] = $this->db->escape($field) . " = '" . $this->db->escape($value) . "' ";
        }
      }
    }
    
    $update_values = implode(', ', $update_values);
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                    $data=array();
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else if (isset($func_values['action']) && $func_values['action'] == 'zero') {
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_product->deleteProduct($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          } else if ($func_name == 'skip_db') {
            if (!isset($item_data)) {
              if (is_array($item_id) && isset($item_id[0])) {
                $item_data = $this->model_catalog_product->getProduct($item_id[0]);
              } else {
                $item_data = $this->model_catalog_product->getProduct($item_id);
              }
            }
            
            if (!isset($item_data[$func_values['db_field']])) continue;
            
            if (($func_values['comparator'] == 'is_equal' && $item_data[$func_values['db_field']] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $item_data[$func_values['db_field']] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($item_data[$func_values['db_field']], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $item_data[$func_values['db_field']] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $item_data[$func_values['db_field']] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . strtolower($this->language->get('entry_'.$func_values['db_field'])) . ' ('.$item_data[$func_values['db_field']].') ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    $message = '';
    $messageArray = array();
    $itemsArray = (array) $item_id;
    
    if (!empty($config['option_identifier']) && substr($config['option_identifier'], 0, 1) == '_' && isset($data['option_identifier_value'])) {
      if (!empty($config['multiple_separator'])) {
        $values_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $data['option_identifier_value']);
      } else {
        $values_array = (array) $data['option_identifier_value'];
      }
      
      if (isset($data['quantity']) && !empty($config['multiple_separator'])) {
        $quantity_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $data['quantity']);
      } else {
        $quantity_array = false;
      }
      
      $skipped = true;
      
      foreach ($values_array as $k => $optIdVal) {
        $opt_query = $this->db->query("SELECT pov.`product_option_value_id`, pov.`quantity`, pov.`product_id` FROM `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (pov.`option_value_id` = ovd.`option_value_id`) WHERE pov.`" . $this->db->escape(substr($config['option_identifier'], 1)) . "` = '" . $this->db->escape($optIdVal) . "'")->row;
        
        if (empty($opt_query)) {
          // $data['row_status'] = 'skipped';
          // $data['row_msg'] = $this->language->get('text_skip_option_not_found');
          // $this->session->data['obui_processed']['skipped']++;
          // $this->session->data['obui_processed']['processed']++;
          // return $data;
          continue;
        }
       
        $item_id = $opt_query['product_id'];
        
        $skipped = false;
         
        if (isset($price_prefix)) {
          $update_values .= ", price_prefix = '".$price_prefix."'";
        }

        if (!$this->simulation) {
          if ($update_values) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET " . $update_values . " WHERE product_option_value_id = '" . (int)$opt_query['product_option_value_id'] . "'");
          }
          
          if (isset($quantity_array[$k])) {
            $update_quantity = array();
            
            if (empty($config['quantity_modifier'])) {
              $update_quantity = "quantity = '" . (int) $quantity_array[$k] . "' ";
            } else if ($config['quantity_modifier'] == '+') {
              $update_quantity = "quantity = quantity + '" . (int) $quantity_array[$k] . "' ";
            } else if ($config['quantity_modifier'] == '-') {
              $update_quantity = "quantity = quantity - '" . (int) $quantity_array[$k] . "' ";
            }
            
            $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET " . $update_quantity . " WHERE product_option_value_id = '" . (int)$opt_query['product_option_value_id'] . "'");
          }
          
          // recalculate product total quantity
          if (!empty($config['auto_qty'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".($config['auto_qty'] == 1 ? 0 : 'quantity')." + IFNULL((SELECT SUM(quantity) FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$item_id . "'), 0) WHERE product_id = '" . (int)$item_id . "'");
          }
        }
      }
      
      if ($skipped) {
        $data['row_status'] = 'skipped';
        $data['row_msg'] = $this->language->get('text_skip_option_not_found') . ' (' . implode(', ', $values_array). ')';
        $this->session->data['obui_processed']['skipped']++;
      } else {
        $data['row_status'] = 'updated';
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '. implode(', ', $values_array);
        $this->session->data['obui_processed']['updated']++;
      }
      
      $this->session->data['obui_processed']['processed']++;
      return $data;
    }
    
    if ($item_id) {
      foreach ($itemsArray as $item_id) {
        // Option update
        if (empty($config['option_identifier']) && !empty($data['option_identifier_value'])) {
          if (!empty($config['multiple_separator'])) {
            $values_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $data['option_identifier_value']);
          } else {
            $values_array = (array) $data['option_identifier_value'];
          }
          
          if (isset($data['quantity']) && !empty($config['multiple_separator'])) {
            $quantity_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $data['quantity']);
          } else {
            $quantity_array = false;
          }
          
          $skipped = true;
          
          foreach ($values_array as $k => $optIdVal) {
            $opt_query = $this->db->query("SELECT pov.`product_option_value_id`, pov.`quantity` FROM `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (pov.`option_value_id` = ovd.`option_value_id`) WHERE pov.`product_id` = '" . (int) $item_id . "' AND ovd.`name` = '" . $this->db->escape($optIdVal) . "'")->row;
            
            if (empty($opt_query)) {
              // $data['row_status'] = 'skipped';
              // $data['row_msg'] = $this->language->get('text_skip_option_not_found');
              // $this->session->data['obui_processed']['skipped']++;
              // $this->session->data['obui_processed']['processed']++;
              // return $data;
              continue;
            }
            
            $skipped = false;
            
            if (isset($price_prefix)) {
              $update_values .= ", price_prefix = '".$price_prefix."'";
            }

            if (!$this->simulation && $update_values) {
              $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET " . $update_values . " WHERE product_option_value_id = '" . (int)$opt_query['product_option_value_id'] . "'");
              
              if (isset($quantity_array[$k])) {
                $update_quantity = array();
                
                if (empty($config['quantity_modifier'])) {
                  $update_quantity = "quantity = '" . (int) $quantity_array[$k] . "' ";
                } else if ($config['quantity_modifier'] == '+') {
                  $update_quantity = "quantity = quantity + '" . (int) $quantity_array[$k] . "' ";
                } else if ($config['quantity_modifier'] == '-') {
                  $update_quantity = "quantity = quantity - '" . (int) $quantity_array[$k] . "' ";
                }
                
                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET " . $update_quantity . " WHERE product_option_value_id = '" . (int)$opt_query['product_option_value_id'] . "'");
              }
              
              // recalculate product total quantity
              if (!empty($config['auto_qty'])) {
                $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".($config['auto_qty'] == 1 ? 0 : 'quantity')." + IFNULL((SELECT SUM(quantity) FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$item_id . "'), 0) WHERE product_id = '" . (int)$item_id . "'");
              }
            }
          }
          
          if ($skipped) {
            $data['row_status'] = 'skipped';
            $data['row_msg'] = $this->language->get('text_skip_option_not_found') . ' (' . implode(', ', $values_array). ')';
            $this->session->data['obui_processed']['skipped']++;
          } else {
            $data['row_status'] = 'updated';
            $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '. implode(', ', $values_array);
            $this->session->data['obui_processed']['updated']++;
          }
          
          $this->session->data['obui_processed']['processed']++;
          
          if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
            $this->session->data['obui_no_delete'][] = $item_id;
          }
        
          return $data;
        } else if (!empty($data['option_identifier_value'])) {
          if (!empty($config['multiple_separator'])) {
            $values_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $data['option_identifier_value']);
          } else {
            $values_array = (array) $data['option_identifier_value'];
          }
          
          if (isset($data['quantity']) && !empty($config['multiple_separator'])) {
            $quantity_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $data['quantity']);
          } else {
            $quantity_array = false;
          }
          
          $skipped = true;
          
          foreach ($values_array as $optIdVal) {
            $opt_query = $this->db->query("SELECT pov.`product_option_value_id`, pov.`quantity` FROM `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (pov.`option_value_id` = ovd.`option_value_id`) WHERE pov.`product_id` = '" . (int) $item_id . "' AND pov.`option_id` = '" . (int) $config['option_identifier'] . "' AND ovd.`name` = '" . $this->db->escape($optIdVal) . "'")->row;
           
            if (empty($opt_query)) {
              // $data['row_status'] = 'skipped';
              // $data['row_msg'] = $this->language->get('text_skip_option_not_found');
              // $this->session->data['obui_processed']['skipped']++;
              // $this->session->data['obui_processed']['processed']++;
              // return $data;
              continue;
            }
           
            $skipped = false;
             
            if (isset($price_prefix)) {
              $update_values .= ", price_prefix = '".$price_prefix."'";
            }
   
            if (!$this->simulation) {
              if ($update_values) {
                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET " . $update_values . " WHERE product_option_value_id = '" . (int)$opt_query['product_option_value_id'] . "'");
              }
              
              if (isset($k) && isset($quantity_array[$k])) {
                $update_quantity = array();
                
                if (empty($config['quantity_modifier'])) {
                  $update_quantity = "quantity = '" . (int) $quantity_array[$k] . "' ";
                } else if ($config['quantity_modifier'] == '+') {
                  $update_quantity = "quantity = quantity + '" . (int) $quantity_array[$k] . "' ";
                } else if ($config['quantity_modifier'] == '-') {
                  $update_quantity = "quantity = quantity - '" . (int) $quantity_array[$k] . "' ";
                }
                
                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET " . $update_quantity . " WHERE product_option_value_id = '" . (int)$opt_query['product_option_value_id'] . "'");
              }
              
              // recalculate product total quantity
              if (!empty($config['auto_qty'])) {
                $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".($config['auto_qty'] == 1 ? 0 : 'quantity')." + IFNULL((SELECT SUM(quantity) FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$item_id . "'), 0) WHERE product_id = '" . (int)$item_id . "'");
              }
            }
          }
          
          if ($skipped) {
            $data['row_status'] = 'skipped';
            $data['row_msg'] = $this->language->get('text_skip_option_not_found') . ' (' . implode(', ', $values_array). ')';
            $this->session->data['obui_processed']['skipped']++;
          } else {
            $data['row_status'] = 'updated';
            $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '. implode(', ', $values_array);
            $this->session->data['obui_processed']['updated']++;
          }
          
          $this->session->data['obui_processed']['processed']++;
          
          if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
            $this->session->data['obui_no_delete'][] = $item_id;
          }
        
          return $data;
        } else {
          unset($data['option_identifier_value']);
        }
        
        // Product update
        if (!$this->simulation) {
          if (!in_array('date_modified', $data) && $update_values) {
            $update_values .= ", `date_modified` = NOW() ";
          }
          
          if ($update_values) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET " . $update_values . " WHERE product_id = '" . (int)$item_id . "'");
          }
          
          if (isset($data['product_discount'])) {
            if (empty($config['discount_insert_type'])) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$item_id . "'");
            }
            
            foreach ($data['product_discount'] as $product_discount) {
              if (isset($config['discount_insert_type']) && $config['discount_insert_type'] == 'update') {
                $this->db->query("UPDATE " . DB_PREFIX . "product_discount SET quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "' WHERE product_id = '" . (int)$item_id . "' AND customer_group_id = '" . (int)$product_discount['customer_group_id'] . "'");
              } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$item_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
              }
            }
          }
          
          if (isset($data['product_special'])) {
            if (empty($config['special_insert_type']) || (!empty($config['special_insert_type']) && $config['special_insert_type'] == 'rm_add' && !in_array($item_id, $this->session->data['obui_processed_ids']))) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$item_id . "'");
            }

            foreach ($data['product_special'] as $product_special) {
              if ((float)$product_special['price'] != 0) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$item_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
              }
            }
          }
          
          if (isset($data['product_store'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$item_id . "'");
            
            if (isset($data['product_store'])) {
              foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$item_id . "', store_id = '" . (int)$store_id . "'");
              }
            }
          }
          
          // Compatibility Mega filter
          if ($this->config->get( 'mfilter_plus_version' )) {
            require_once DIR_SYSTEM . 'library/mfilter_plus.php';
            Mfilter_Plus::getInstance($this)->updateProduct($item_id);
          }		
        }
        
        if (!empty($data['product_description'])) {
          foreach ($data['product_description'] as $language_id => $item_desc) {
            foreach ($item_desc as $field => $value) {
              $update_desc_values[] = $this->db->escape($field) . " = '" . $this->db->escape($value) . "'";
            }
            
            $desc_values_query = implode(', ', $update_desc_values);
            
            if (!$this->simulation) {
              $this->db->query("UPDATE " . DB_PREFIX . "product_description SET " . $desc_values_query . " WHERE product_id = '" . (int)$item_id . "' AND language_id = '" . (int)$language_id . "' ");
            }
          }
        }
        
        if (!empty($data[$config['item_identifier']])) {
          $message = $data[$config['item_identifier']];
          $messageArray[] = $data[$config['item_identifier']];
        }
      }
      
      $data['row_status'] = 'updated';
      $this->session->data['obui_processed']['updated']++;
    } else {
      if (false) {
        // quick insert
        if (!$this->simulation) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product SET " . $update_values);
          
          $item_id = $this->db->getLastId();
          
          if (isset($data['product_discount'])) {
            if (empty($config['discount_insert_type'])) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$item_id . "'");
            }
            
            foreach ($data['product_discount'] as $product_discount) {
              if (isset($config['discount_insert_type']) && $config['discount_insert_type'] == 'update') {
                $this->db->query("UPDATE " . DB_PREFIX . "product_discount SET quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "' WHERE product_id = '" . (int)$item_id . "' AND customer_group_id = '" . (int)$product_discount['customer_group_id'] . "'");
              } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$item_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
              }
            }
          }


          if (isset($data['product_special'])) {
            if (empty($config['special_insert_type']) || (!empty($config['special_insert_type']) && $config['special_insert_type'] == 'rm_add' && !in_array($item_id, $this->session->data['obui_processed_ids']))) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$item_id . "'");
            }

            foreach ($data['product_special'] as $product_special) {
              if ((float)$product_special['price'] != 0) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$item_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
              }
            }
          }
        }
        
        if (!empty($data['product_description'])) {
          foreach ($data['product_description'] as $language_id => $item_desc) {
            foreach ($item_desc as $field => $value) {
              $update_desc_values[] = $this->db->escape($field) . " = '" . $this->db->escape($value) . "'";
            }
            
            $desc_values_query = implode(', ', $update_desc_values);
            
            if (!$this->simulation) {
              $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET " . $desc_values_query . ", product_id = '" . (int)$item_id . "', language_id = '" . (int)$language_id . "' ");
            }
          }
        }
        
        if (!empty($data[$config['item_identifier']])) {
          $message = $data[$config['item_identifier']];
        }
        
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
        
      } else {
        $message = $this->language->get('text_skip_quick_update');
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
      }
    }
    
    if ($item_id && (
          (!empty($config['special_insert_type']) && $config['special_insert_type'] == 'rm_add') 
        )) {
      $this->session->data['obui_processed_ids'][] = $item_id;
    }
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    $data['row_msg'] = '';
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$this->language->get('text_type_product') . ' ' . $item_id.'] ' . (!empty($message) ? $message : '');
      } else if (!empty($message)) {
        foreach ($itemsArray as $k => $item_id) {
          $data['row_msg'] .= ($data['row_msg'] ? '<br/>' : '') . '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">' . $item_id . '</a>] ' . (isset($messageArray[$k]) ? $messageArray[$k] : $message);
        }
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$item_id.'&' . $this->token, 'SSL').'">' . $this->language->get('text_type_product') . ' ' . $item_id . '</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $this->language->get('text_type_product') . ' ' . $item_id;
    }
    
    return $data;
  }
  
  public function process_order_info($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    // unset if empty
    /*
    foreach (array('price', 'quantity') as $key) {
      if (empty($data[$key])) {
        unset($data[$key]);
      }
    }
    */
    
    $data = $this->request->clean($data);
    
    $item_id = $this->itemExists('order', $config, $data);
    
    $data['order_status_id'] = $this->handler->orderStatusHandler('order_status_id', $config);
    
    if (!isset($data['store_id'])) {
      $data['store_id'] = 0;
    }
    
    if (!isset($this->storeIdToName[$data['store_id']])) {
      if (!$data['store_id']) {
        $this->storeIdToName[0] = $this->config->get('config_name');
      } else {
        $this->load->model('setting/store');
        $store = $this->model_setting_store->getStore($data['store_id']);
        $this->storeIdToName[$store['store_id']] = $store['name'];
      }
    }
    
    if (empty($this->currencyCodeToId)) {
      $this->load->model('localisation/currency');
      $currencies = $this->model_localisation_currency->getCurrencies();
      
      foreach ($currencies as $currency) {
        $this->currencyCodeToId[$currency['code']] = $currency['currency_id'];
      }
    }
    
    $data['store_name'] = $this->storeIdToName[$data['store_id']];
    
    // get customer email if not exists and customer id exists
    if (empty($data['email']) && !empty($data['customer_id'])) {
      $customer_query = $this->db->query("SELECT DISTINCT email FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$data['customer_id'] . "'")->row;
      
      if (isset($customer_query['email'])) {
        $data['email'] = $customer_query['email'];
      }
    }
    
    if (!$this->simulation) {
      $data['currency_id'] = $this->currencyCodeToId[$data['currency_code']];
      $data['language_id'] = $this->config->get('config_language_id');
    }

    foreach (array('payment', 'shipping') as $address_type) {
      if (!$this->simulation) {
        $data[$address_type.'_country_id'] = $this->handler->countryHandler($data[$address_type.'_country'], $config);
        $data[$address_type.'_zone_id'] = $this->handler->zoneHandler($data[$address_type.'_zone'], $config, $data[$address_type.'_country_id']);
      }
      $data[$address_type.'_country'] = $this->handler->countryHandler($data[$address_type.'_country'], $config, 'get_name');
      $data[$address_type.'_zone'] = $this->handler->zoneHandler($data[$address_type.'_zone'], $config, (isset($data[$address_type.'_country_id']) ? $data[$address_type.'_country_id'] : ''), 'get_name');
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update') {
        if (!$this->simulation) {
          $this->model_gkd_import_order->editOrder($item_id, $data, !empty($config['create_order_total']));
        }
        
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
    } else {
      if ($config['item_not_exists'] == 'insert') {
        if (!$this->simulation) {
          $this->model_gkd_import_order->addOrder($data, !empty($config['create_order_total']));
        }
        
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    /*
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    */
    
    $this->session->data['obui_processed']['processed']++;

    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = $this->language->get('text_type_order').' #'.$item_id;
      } else {
        $data['row_msg'] = '<a target="_blank" href="'.$this->url->link('sale/order/info', 'order_id='.$item_id.'&' . $this->token, 'SSL').'">#'.$item_id.'</a>';
      }
    }
    
    return $data;
  }
  
  public function process_order_item($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    $data = $this->request->clean($data);
    
    $item_id = $this->itemExists('order', $config, $data);
    
    //$data['product_id'] = '';

    if (!empty($data['model']) || !empty($data['product_id'])) {
      if (!$this->simulation) {
        $this->load->model('gkd_import/product');
        $data['product_id'] = $this->model_gkd_import_product->findProductId($data);
      }
      unset($data['title'], $data['value'], $data['code']);
    } else {
      unset($data['name'], $data['model'], $data['quantity'], $data['price'], $data['tax'], $data['total'], $data['reward']);
    }
    
    if ($item_id) {
      if (!$this->simulation) {
        $this->model_gkd_import_order->addOrderProductOrTotal($item_id, $data);
      }
      
      $data['row_status'] = 'updated';
      $this->session->data['obui_processed']['updated']++;
    } else {
      $message = $this->language->get('text_skip_quick_update');
      $data['row_status'] = 'skipped';
      $this->session->data['obui_processed']['skipped']++;
    }
    
    $this->session->data['obui_processed']['processed']++;

    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = $this->language->get('text_type_order').' #'.$item_id;
      } else {
        $data['row_msg'] = '<a target="_blank" href="'.$this->url->link('sale/order/info', 'order_id='.$item_id.'&' . $this->token, 'SSL').'">#'.$item_id.'</a>';
      }
    }
    
    return $data;
  }
  
  public function process_order_status_update($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    // unset if empty
    /*
    foreach (array('price', 'quantity') as $key) {
      if (empty($data[$key])) {
        unset($data[$key]);
      }
    }
    */
    
    $data = $this->request->clean($data);
    $item_id = $this->itemExists('order', $config, $data);
    
    // handle extra function before populated values (mainly for skip before inserting data)
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
             if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                  $data = array();
                  
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else if (isset($func_values['action']) && $func_values['action'] == 'zero') {
                  $data = array();
                  
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = 0 WHERE product_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_qtyzero') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_product->deleteProduct($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          } else if ($func_name == 'skip_db') {
            if (!isset($item_data)) {
              $this->load->model('gkd_import/category');
              $item_data = $this->model_sale_order->getOrder($item_id);
            }
            
            if (!isset($item_data[$func_values['db_field']])) continue;
            
            if ($func_values['db_field'] == 'total') {
              $item_data[$func_values['db_field']] = round($item_data[$func_values['db_field']], 2);
            }
            
            if (($func_values['comparator'] == 'is_equal' && $item_data[$func_values['db_field']] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($item_data[$func_values['db_field']], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $item_data[$func_values['db_field']] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($item_data[$func_values['db_field']], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $item_data[$func_values['db_field']] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $item_data[$func_values['db_field']] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($item_data[$func_values['db_field']], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . strtolower($this->language->get('entry_'.$func_values['db_field'])) . ' ('.$item_data[$func_values['db_field']].') ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    $data['order_status_id'] = $this->handler->orderStatusHandler('order_status_id', $config);
    
    if (isset($data['notify'])) $data['notify'] = $this->handler->booleanHandler('notify', $config); // enabled/disabled, on/off, true/false, 1/0
    //$data['product_group'] = $this->handler->productGroupHandler('product_group', $config);
    
    /*
    $update_values = array();
    
    foreach ($data as $field => $value) {
      if (in_array($field, array('product_id', 'product_description'))) {
        continue;
      } else if (in_array($field, array('price', 'retail', 'map'))) {
        $update_values[] = $this->db->escape($field) . " = '" . (float) $value . "' ";
      } else if (in_array($field, array('quantity'))) {
        $update_values[] = $this->db->escape($field) . " = '" . (int) $value . "' ";
      } else {
        $update_values[] = $this->db->escape($field) . " = '" . $this->db->escape($value) . "' ";
      }
    }
    
    $update_values = implode(', ', $update_values);
    */
    
    if ($item_id) {
      if (!$this->simulation) {
        $this->model_gkd_import_order->addOrderHistory($item_id, $data['order_status_id'], $data['comment'], $data['notify'], false, $data);
      }
      
      $data['row_status'] = 'updated';
      $this->session->data['obui_processed']['updated']++;
    } else {
      $data['row_status'] = 'skipped';
      $this->session->data['obui_processed']['skipped']++;
    }
    
    /*
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    */
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($this->simulation) {
      $data['comment'] = array_shift($data['comment']);
    }
    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = $this->language->get('text_type_order').' #'.$item_id;
      } else {
        $data['row_msg'] = '<a target="_blank" href="'.$this->url->link('sale/order/info', 'order_id='.$item_id.'&' . $this->token, 'SSL').'">#'.$item_id.'</a>';
      }
    }
    
    return $data;
  }
  
  public function process_category($config, $line) {
    $this->pre_process($config);
    
    $config['columns_bindings'] = $config['columns'];
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    $ml_parent = array();
    /*
    // Detect parent in category name
    foreach ($data['category_description'] as $language_id => $desc) {
      if (!empty($config['subcategory_separator']) && strpos($desc['name'], @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'))) {
        $categories = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $desc['name']);
        $data['category_description'][$language_id]['name'] = trim(array_pop($categories));
        $ml_parent[$language_id] = implode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $categories);
      }
    }
    
    $data['parent_id'] = $this->handler->parentCategoryHandler('parent_id', $config, $ml_parent);
    */
    
    // Detect parent in category name
    if (!empty($data['category_description'])) {
      foreach ($data['category_description'] as $language_id => $desc) {
        if (!empty($config['subcategory_separator']) && strpos($desc['name'], @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'))) {
          $categories = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $desc['name']);
          $data['category_description'][$language_id]['name'] = trim(array_pop($categories));
          $ml_parent['name'][$language_id] = implode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $categories);
        }
        
        if (!empty($config['subcategory_separator']) && isset($desc['seo_keyword']) && strpos($desc['seo_keyword'], @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'))) {
          $categories = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $desc['seo_keyword']);
          $data['category_description'][$language_id]['seo_keyword'] = trim(array_pop($categories));
          $ml_parent['seo_keyword'][$language_id] = implode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $categories);
        }
      }
    }
    
    if (in_array($config['item_identifier'], array('name', 'description'))) {
      $defaultLangDesc = reset($data['category_description']);
      $data[$config['item_identifier']] = $defaultLangDesc[$config['item_identifier']];
      unset($defaultLangDesc);
    }
    
    //$item_id = $this->categoryExists($data['category_description'], $data['parent_id']);
    $item_id = $this->itemExists($config['import_type'], $config, $data);
    
    // item will be processed, prepare values
    if (($item_id && $config['item_exists'] == 'soft_update') || ($item_id && $config['item_exists'] == 'update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
      
      /*
      if ($item_id && $config['item_exists'] == 'soft_update') {
        //$config = $save_config;
        $config['columns'] = $config['columns_bindings'];
        
        $config['columns'] = $this->walk_recursive_remove($config['columns']);
        
        $this->populate_fields($config, $line);
        $this->populate_extra_func($config, $line);

        $data = &$config['columns'];
      }
      */
      
      if ($item_id && $config['item_exists'] == 'soft_update') {
        $config['columns'] = $this->recursive_array_intersect_key($config['columns'], $this->walk_recursive_remove($config['columns_bindings']));
        
        $data = &$config['columns'];
      }
      
      if (isset($data['parent_id']))      $data['parent_id'] = $this->handler->parentCategoryHandler('parent_id', $config, $ml_parent);
      
      $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
      $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      
      // data formatters
      if (isset($data['image']))              $data['image'] = $this->handler->imageHandler('image', $config);
      if (isset($data['category_store']))     $data['category_store'] = $this->handler->storeHandler('category_store', $config); 
      if (isset($data['category_filter']))    $data['category_filter'] = $this->handler->filterHandler('category_filter', $config);
      if (isset($data['category_layout']))    $data['category_layout'] = $this->handler->layoutHandler('category_layout', $config); // detect by name or ID
      if (isset($data['status']))             $data['status'] = $this->handler->booleanHandler('status', $config); // enabled/disabled, on/off, true/false, 1/0
      
      //$data['banner_image'] = $this->handler->imageHandler('banner_image', $config);
      
      // set default values for custom module compatibility
      if ($config['item_exists'] != 'soft_update') {
        $setDefault = array(
          'keyword', // seo modules
        );
        
        foreach ($setDefault as $v) {
          if (!isset($data[$v])) {
            $data[$v] = '';
          }
        }
      }
      
      // unset if empty
      foreach (array('category_store', 'category_filter', 'category_layout') as $key) {
        if (empty($data[$key])) {
          unset($data[$key]);
        }
      }
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                    $data=array();
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "category SET status = 0 WHERE category_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_category->deleteCategory($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->model_catalog_category->editCategory($item_id, $data);
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else if ($config['item_exists'] == 'soft_update') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->load->model('gkd_import/category');
          $this->model_gkd_import_category->editCategory($item_id, $data);
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
    } else {
      if ($config['item_not_exists'] == 'insert') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $item_id = $this->model_catalog_category->addCategory($data);
        }
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
        
        if ($this->simulation) {
          //$this->addProcessedIdentifier($data[$config['item_identifier']]);
          if (in_array($config['item_identifier'], array('name', 'title', 'description')) && isset($data['category_description']) && $prodDesc = reset($data['category_description'])) {
            $this->addProcessedIdentifier($prodDesc[$config['item_identifier']]);
          } else {
            $this->addProcessedIdentifier((isset($data[$config['item_identifier']]) ? $data[$config['item_identifier']] : $item_id));
          }
        }
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    // add children
    if (isset($data['children'])) {
      $data['children'] = $this->handler->categoryHandler('children', $config, false, $item_id, 'children_id');
      unset($data['children_id']);
    }
    
    if ($config['item_identifier'] == 'name') {
      unset($data[$config['item_identifier']]);
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    if (!empty($data['category_description'][$this->config->get('config_language_id')]['name'])) {
      $item_name = $data['category_description'][$this->config->get('config_language_id')]['name'];
    } else if ($item_id) {
      $item_name = 'Category ID '.$item_id;
    } else {
      $item_name = '';
    }
    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $item_name);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/category/edit', 'category_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>]'.$message;
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/category/edit', 'category_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_name.'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $item_name;
    }
    
    return $data;
  }
  
  public function process_information($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    $item_id = $this->itemExists($config['import_type'], $config, $data);
    
    // product will be updated, prepare values
    if (($item_id && $config['item_exists'] == 'update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
      
      $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
      $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      
      // data formatters
      $data['information_store'] = $this->handler->storeHandler('information_store', $config); // @todo: detect by name
      if (isset($data['information_layout']))       $data['information_layout'] = $this->handler->layoutHandler('information_layout', $config); // detect by name or ID
      
      // set default values for custom module compatibility
      if ($config['item_exists'] != 'soft_update') {
        $setDefault = array(
          'keyword', // seo modules
        );
        
        foreach ($setDefault as $v) {
          if (!isset($data[$v])) {
            $data[$v] = '';
          }
        }
      }
      
      // unset if empty
      foreach (array('information_store') as $key) {
        if (empty($data[$key])) {
          unset($data[$key]);
        }
      }
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                    $data=array();
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "information SET status = 0 WHERE information_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_information->deleteInformation($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update') {
        
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->model_catalog_information->editInformation($item_id, $data);
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
    } else {
      if ($config['item_not_exists'] == 'insert') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->model_catalog_information->addInformation($data);
        }
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    if (!empty($data['information_description'][$this->config->get('config_language_id')]['title'])) {
      $item_name = $data['information_description'][$this->config->get('config_language_id')]['title'];
    } else if ($item_id) {
      $item_name = 'Information ID '.$item_id;
    } else {
      $item_name = '';
    }
    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $item_name);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/information/edit', 'information_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>]'.$message;
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/information/edit', 'information_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_name.'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $item_name;
    }
    
    return $data;
  }
  
  public function process_manufacturer($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    $item_id = $this->itemExists($config['import_type'], $config, $data);
    
    // product will be updated, prepare values
    if (($item_id && $config['item_exists'] == 'update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
      
      $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
      $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      
      // data formatters
      $data['manufacturer_store'] = $this->handler->storeHandler('manufacturer_store', $config); // @todo: detect by name
      $data['image'] = $this->handler->imageHandler('image', $config);
      
      // set default values for custom module compatibility
      if ($config['item_exists'] != 'soft_update') {
        $setDefault = array(
          'keyword', // seo modules
        );
        
        foreach ($setDefault as $v) {
          if (!isset($data[$v])) {
            $data[$v] = '';
          }
        }
      }
      
      // unset if empty
      foreach (array('manufacturer_store') as $key) {
        if (empty($data[$key])) {
          unset($data[$key]);
        }
      }
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                  if (!$this->simulation) {
                    $this->model_catalog_manufacturer->deleteManufacturer($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                
                $data['row_status'] = 'deleted';
                $this->session->data['obui_processed']['deleted']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update') {
        
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->model_catalog_manufacturer->editManufacturer($item_id, $data);
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
    } else {
      if ($config['item_not_exists'] == 'insert') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $item_id = $this->model_catalog_manufacturer->addManufacturer($data);
        }
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    if (!empty($data['name'])) {
      $item_name = $data['name'];
    } else if ($item_id) {
      $item_name = 'Manufacturer ID '.$item_id;
    } else {
      $item_name = '';
    }
    
    if ($item_id) {
      if (defined('GKD_CRON') || !is_numeric($item_id)) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $item_name);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/manufacturer/edit', 'manufacturer_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '.$message;
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/manufacturer/edit', 'manufacturer_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_name.'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $item_name;
    }
    
    return $data;
  }
  
  public function process_attribute($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    $data = $this->request->clean($data);
    
    if (!empty($config['attr_insert_type']) && $config['attr_insert_type'] == 'update') {
      $data['attribute_description'] = $this->array_filter_recursive($data['attribute_description']);
      
      foreach ($data['attribute_description'] as $language_id => &$attribute_description) {
        if (!isset($attribute_description['group'])) {
          $attribute_description['group'] = 'Default';
        }
      }
    }
    
    $mainValues = $this->array_filter_recursive($data['attribute_description']);
    $mainValues = reset($mainValues);
    
    $product_ids = array();
    $attrArray_ids = array();
    
    //$item_id = $this->itemExists($config['import_type'], $config, $data);
    $item_id = false;
    
    // direct attribute
    if (in_array($config['item_identifier'], array('attribute_id', 'attribute_name'))) {
      if (empty($data['attribute_id']) && !empty($data['attribute_group_id'])) {
        $mode = 'attribute_group';
        
        $attr = $this->db->query("SELECT ad.attribute_group_id FROM " . DB_PREFIX . "attribute_group_description ad WHERE ad.attribute_group_id = '" . (int) $data['attribute_group_id'] . "'")->row;
      } else {
        $mode = 'attribute';
      
        if ($config['item_identifier'] == 'attribute_name') {
          if (is_array($mainValues['name'])) {
            $attrArray = array();
            foreach ($mainValues['name'] as $currName) {
              $attrArray[] = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute a ON (a.attribute_id = ad.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (a.attribute_group_id = agd.attribute_group_id) WHERE ad.name = '" . $this->db->escape($currName) . "' AND agd.name = '" . $this->db->escape(isset($mainValues['group']) ? $mainValues['group'] : '') . "'")->row;
            }
          } else {
            $attr = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute a ON (a.attribute_id = ad.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (a.attribute_group_id = agd.attribute_group_id) WHERE ad.name = '" . $this->db->escape($mainValues['name']) . "' AND agd.name = '" . $this->db->escape(isset($mainValues['group']) ? $mainValues['group'] : '') . "'")->row;
          }
        } else if (is_array($data['attribute_id'])) {
            $attrArray = array();
            foreach ($data['attribute_id'] as $currName) {
              $attrArray[] = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad WHERE ad.attribute_id = '" . (int) $currName . "'")->row;
            }
        } else {
          $attr = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad WHERE ad.attribute_id = '" . (int) $data['attribute_id'] . "'")->row;
        }
      }
    // product attribute
    } else {
      if (!empty($data['attribute_id'])) {
        $attr = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad WHERE ad.attribute_id = '" . (int) $data['attribute_id'] . "'")->row;
      } else if (!empty($mainValues['group'])) {
        $attr = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute a ON (a.attribute_id = ad.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (a.attribute_group_id = agd.attribute_group_id) WHERE ad.name = '" . $this->db->escape($mainValues['name']) . "' AND agd.name = '" . $this->db->escape($mainValues['group']) . "'")->row;
      } else {
        $attr = $this->db->query("SELECT ad.attribute_id FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute a ON (a.attribute_id = ad.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (a.attribute_group_id = agd.attribute_group_id) WHERE ad.name = '" . $this->db->escape($mainValues['name']) . "'")->row;
      }
      
      $mode = 'product';
      
      if (isset($data[$config['item_identifier']]) && is_array($data[$config['item_identifier']])) {
        foreach ($data[$config['item_identifier']] as $key => $prod_identifier) {
          $product_ids[] = $this->itemExists('product', $config, $data, $key);
        }
      } else {
        $product_ids[] = $this->itemExists('product', $config, $data);
      }
    }
    
    // attribute exists ?
    if ($mode == 'attribute_group' && !empty($attr['attribute_group_id'])) {
      $item_id = $attr['attribute_group_id'];
    } else if (isset($attrArray)) {
      foreach ($attrArray as $attrRes) {
        $attrArray_ids[] = !empty($attrRes['attribute_id']) ? $attrRes['attribute_id'] : false;
      }
    } else if (!empty($attr['attribute_id'])) {
      $item_id = $attr['attribute_id'];
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item' && $mode != 'product')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                  if (!$this->simulation) {
                    $this->model_catalog_manufacturer->deleteManufacturer($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                
                $data['row_status'] = 'deleted';
                $this->session->data['obui_processed']['deleted']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if ($mode == 'attribute_group') {
      if (($item_id && $config['item_exists'] == 'update') || ($item_id && $config['item_exists'] == 'soft_update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
        if (!$this->simulation) {
          $this->load->model('localisation/language');
          $languages = $this->model_localisation_language->getLanguages();
          
          $attr_group_data = array();
          $attr_group_data['sort_order'] = 1;
          
          if (!empty($data['attribute_group_id'])) {
            $attr_group_data['attribute_group_id'] = $data['attribute_group_id'];
          }
          
          foreach ($languages as $language) {
            $attr_group_data['attribute_group_description'][$language['language_id']]['name'] = !empty($data['attribute_description'][$language['language_id']]['group']) ? $data['attribute_description'][$language['language_id']]['group'] : (($item_id && $config['item_exists'] == 'soft_update') ? '' : 'Default');
          }
        }
      }
      
      if ($item_id) {
        if ($config['item_exists'] == 'update') {
          
          if (!$this->simulation) {
            $this->load->model('gkd_import/attribute');
            $this->model_gkd_import_attribute->editAttributeGroup($item_id, $attr_group_data);
          }
          $data['row_status'] = 'updated';
          $this->session->data['obui_processed']['updated']++;
        } else if ($config['item_exists'] == 'soft_update') {
          
          if (!$this->simulation) {
            $this->load->model('gkd_import/attribute');
            $this->model_gkd_import_attribute->updateAttributeGroup($item_id, $attr_group_data);
          }
          $data['row_status'] = 'updated';
          $this->session->data['obui_processed']['updated']++;
        } else {
          // skip item - log
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = $this->language->get('text_skip_update');
        }
      } else {
        if ($config['item_not_exists'] == 'insert') {
          if (!$this->simulation) {
            $this->load->model('gkd_import/attribute');
            $item_id = $this->model_gkd_import_attribute->addAttributeGroup($attr_group_data);
          }
          $data['row_status'] = 'inserted';
          $this->session->data['obui_processed']['inserted']++;
        } else {
          // skip item - log
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = $this->language->get('text_skip_insert');
        }
      }
    } else {
      if (($item_id && $config['item_exists'] == 'update') || ($item_id && $config['item_exists'] == 'soft_update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
        if (!$this->simulation) {
          $this->load->model('localisation/language');
          $languages = $this->model_localisation_language->getLanguages();
          
          $attr_data = array();
          $attr_data['sort_order'] = 1;
          
          if (!empty($config['extra'])) {
            foreach ($config['extra'] as $extra) {
              $attr_data[$extra] = $data[$extra];
            }
          }
          
          if (!empty($data['attribute_group_id'])) {
            $attr_group = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . $this->db->escape($data['attribute_group_id']) . "'")->row;
          } else {
            $attr_group = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name = '" . $this->db->escape($mainValues['group']) . "'")->row;
          }
          
          if (!empty($data['attribute_id'])) {
            $attr_data['attribute_id'] = $data['attribute_id'];
          }
        
          // group exists - get id
          if (!empty($attr_group['attribute_group_id'])) {
            $attr_data['attribute_group_id'] = $attr_group['attribute_group_id'];
            $data['attribute_group_id'] = $attr_group['attribute_group_id'];
          } 
          //  group not exists - create
          else {
            $attr_group_data = array();
            $attr_group_data['sort_order'] = 1;
            
            foreach ($languages as $language) {
              $attr_group_data['attribute_group_description'][$language['language_id']]['name'] = !empty($data['attribute_description'][$language['language_id']]['group']) ? $data['attribute_description'][$language['language_id']]['group'] : (($item_id && $config['item_exists'] == 'soft_update') ? '' : 'Default');
            }
            
            $this->load->model('catalog/attribute_group');
            $attr_data['attribute_group_id'] = $this->model_catalog_attribute_group->addAttributeGroup($attr_group_data);
            $data['attribute_group_id'] = $attr_data['attribute_group_id'];
          }
          
          if (isset($attrArray_ids)) {
            $attr_data_arr = array();
            foreach ($attrArray_ids as $k => $current_item_id) {
              foreach ($languages as $language) {
                $attr_data_arr[$k]['attribute_description'][$language['language_id']]['name'] = !empty($data['attribute_description'][$language['language_id']]['name'][$k]) ? $data['attribute_description'][$language['language_id']]['name'][$k] : (($current_item_id && $config['item_exists'] == 'soft_update') ? '' : 'Default');
              }
            }
          } else {
            // create attribute
            foreach ($languages as $language) {
              $attr_data['attribute_description'][$language['language_id']]['name'] = !empty($data['attribute_description'][$language['language_id']]['name']) ? $data['attribute_description'][$language['language_id']]['name'] : (($item_id && $config['item_exists'] == 'soft_update') ? '' : 'Default');
            }
          }
          
          $this->load->model('catalog/attribute');
        }
      }
      
      if (!isset($attrArray_ids)) {
        $attrArray_ids = array($item_id);
      }
      
      foreach ($attrArray_ids as $k => $current_item_id) {
        $attrArr_data = $data;
        if (is_array($data['attribute_id'])) {
          $attrArr_data['attribute_id'] = !empty($data['attribute_id'][$k]) ? $data['attribute_id'][$k] : $data['attribute_id'];
        }
          
        foreach ($data['attribute_description'] as $language_id => $language) {
          if (is_array($data['attribute_description'][$language_id]['name'])) {
            $attrArr_data['attribute_description'][$language_id]['name'] = !empty($data['attribute_description'][$language_id]['name'][$k]) ? $data['attribute_description'][$language_id]['name'][$k] : (($current_item_id && $config['item_exists'] == 'soft_update') ? '' : 'Default');
          }
        }
        
        if ($current_item_id) {
          if ($config['item_exists'] == 'update') {
            
            if (!$this->simulation) {
              $this->model_catalog_attribute->editAttribute($current_item_id, $attrArr_data);
            }
            $data['row_status'] = 'updated';
            $this->session->data['obui_processed']['updated']++;
          } else if ($config['item_exists'] == 'soft_update') {
            
            if (!$this->simulation) {
              //$this->model_catalog_attribute->updateAttribute($item_id, $attr_data);
              $this->load->model('gkd_import/attribute');
              $this->model_gkd_import_attribute->updateAttribute($current_item_id, $attrArr_data);
            }
            $data['row_status'] = 'updated';
            $this->session->data['obui_processed']['updated']++;
          } else {
            // skip item - log
            $data['row_status'] = 'skipped';
            $this->session->data['obui_processed']['skipped']++;
            $message = $this->language->get('text_skip_update');
          }
        } else {
          if ($config['item_not_exists'] == 'insert') {
            if (!$this->simulation) {
              $this->load->model('gkd_import/attribute');
              $item_id = $this->model_gkd_import_attribute->addAttribute($attrArr_data);
              $attrArray_ids[$k] = $item_id;
            }
            $data['row_status'] = 'inserted';
            $this->session->data['obui_processed']['inserted']++;
          } else {
            // skip item - log
            $data['row_status'] = 'skipped';
            $this->session->data['obui_processed']['skipped']++;
            $message = $this->language->get('text_skip_insert');
          }
        }
      }
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($mode == 'product') {
      if (!isset($this->session->data['obui_deleted_ids'])) {
        $this->session->data['obui_deleted_ids'] = array();
      }
      
      foreach ($product_ids as $product_id) {
        /*
        if (!empty($config['delete_attributes'])) {
          if (!$this->simulation && !in_array($product_id, $this->session->data['obui_deleted_ids'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
            $this->session->data['obui_deleted_ids'][] = $product_id;
          }
        }
        */
        
        if (empty($config['attr_insert_type']) || (!empty($config['attr_insert_type']) && $config['attr_insert_type'] == 'rm_add' && !in_array($product_id, $this->session->data['obui_processed_ids']))) {
          if (!$this->simulation) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
          }
          
          $this->session->data['obui_deleted_ids'][] = $product_id;
        }
        
        if ($product_id) {
          if ($data['row_status'] != 'skipped') {
            if (!$this->simulation) {
              foreach ($data['attribute_description'] as $language_id => $attribute_description) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$item_id . "' AND language_id = '" . (int)$language_id . "'");

                $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$item_id . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($attribute_description['value']) . "'");
              }
            }
          }
        } else {
          $data['row_status'] = 'skipped';
          $message = $this->language->get('text_skip_product_not_found');
        }
      }
    } else {
      if ($mode != 'attribute_group') {
        if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
          $this->session->data['obui_no_delete'][] = $item_id;
        }
      }
    }
    
    if ($mode == 'product' && $product_id) {
      // save processed item id for further use - only if necessary
      if (!empty($config['attr_insert_type']) && $config['attr_insert_type'] == 'rm_add') {
        $this->session->data['obui_processed_ids'][] = $product_id;
      }
    
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$product_id.'] ' . (!empty($message) ? $message : $mainValues['name']);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$product_id.'&' . $this->token, 'SSL').'">'.$product_id.'</a>] '.$message;
      } else {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$product_id.'&' . $this->token, 'SSL').'">'.$product_id.'</a>] '.$mainValues['name'].' : '.$mainValues['value'].'</a>';
      }
    } else if ($item_id && $mode != 'product') {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $mainValues['name']);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/attribute/edit', 'attribute_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '.$message;
      } else {
        if (!empty($attrArray_ids)) {
          $data['row_msg'] = '';
          foreach ($attrArray_ids as $k => $id) {
            $data['row_msg'] .= '['.$id.'] <a target="_blank" href="'.$this->url->link('catalog/attribute/edit', 'attribute_id='.$id.'&' . $this->token, 'SSL').'">'.$mainValues['name'][$k].'</a><br/>';
          }
        } else {
          $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/attribute/edit', 'attribute_id='.$item_id.'&' . $this->token, 'SSL').'">'.$mainValues['name'].'</a>';
        }
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $mainValues['name'];
    }
    
    return $data;
  }
  
  
  public function process_filter($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    $data = $this->request->clean($data);
    
    $product_ids = array();
    
    $mainValues = reset($data['filter_description']);
    
    $multiData = array();
    
    if (!empty($config['multiple_separator']) && strpos($mainValues['name'], $config['multiple_separator'])) {
      $values_array = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $mainValues['name']);
      foreach ($values_array as $k => $val) {
        $multiData[$k] = $data;
        
        foreach ($data['filter_description'] as $lang_id => $values) {
          $currentValues = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $values['name']);
          if (isset($currentValues[$k])) {
            $multiData[$k]['filter_description'][$lang_id]['name'] = $currentValues[$k];
          } else {
            $multiData[$k]['filter_description'][$lang_id]['name'] = $currentValues[0];
          }
        }
      }
    } else {
      $multiData[] = $data;
    }
    
    foreach ($multiData as $data) {
      $mainValues = reset($data['filter_description']);
      
      //$item_id = $this->itemExists($config['import_type'], $config, $data);
      $item_id = false;
      
      // direct filter
      if (in_array($config['item_identifier'], array('filter_id', 'filter_name'))) {
        $mode = 'filter';
        
        if ($config['item_identifier'] == 'filter_name') {
          $attr = $this->db->query("SELECT ad.filter_id FROM " . DB_PREFIX . "filter_description ad LEFT JOIN " . DB_PREFIX . "filter a ON (a.filter_id = ad.filter_id) LEFT JOIN " . DB_PREFIX . "filter_group_description agd ON (a.filter_group_id = agd.filter_group_id) WHERE ad.name = '" . $this->db->escape($mainValues['name']) . "' AND agd.name = '" . $this->db->escape($mainValues['group']) . "'")->row;
        } else {
          $attr = $this->db->query("SELECT ad.filter_id FROM " . DB_PREFIX . "filter_description ad WHERE ad.filter_id = '" . (int) $data['filter_id'] . "'")->row;
        }
      // product filter
      } else if (substr($config['item_identifier'], 0, 5) == '@cat_') {
        $config['item_identifier'] = str_replace('@cat_', '', $config['item_identifier']);
        
        $attr = $this->db->query("SELECT ad.filter_id FROM " . DB_PREFIX . "filter_description ad LEFT JOIN " . DB_PREFIX . "filter a ON (a.filter_id = ad.filter_id) LEFT JOIN " . DB_PREFIX . "filter_group_description agd ON (a.filter_group_id = agd.filter_group_id) WHERE ad.name = '" . $this->db->escape($mainValues['name']) . "' AND agd.name = '" . $this->db->escape($mainValues['group']) . "'")->row;
        $mode = 'category';
        
        if (isset($data[$config['item_identifier']]) && is_array($data[$config['item_identifier']])) {
          foreach ($data[$config['item_identifier']] as $key => $prod_identifier) {
            $category_ids[] = $this->itemExists('category', $config, $data, $key);
          }
        } else {
          $category_ids[] = $this->itemExists('category', $config, $data);
        }
      } else {
        $attr = $this->db->query("SELECT ad.filter_id FROM " . DB_PREFIX . "filter_description ad LEFT JOIN " . DB_PREFIX . "filter a ON (a.filter_id = ad.filter_id) LEFT JOIN " . DB_PREFIX . "filter_group_description agd ON (a.filter_group_id = agd.filter_group_id) WHERE ad.name = '" . $this->db->escape($mainValues['name']) . "' AND agd.name = '" . $this->db->escape($mainValues['group']) . "'")->row;
        $mode = 'product';
        
        if (isset($data[$config['item_identifier']]) && is_array($data[$config['item_identifier']])) {
          foreach ($data[$config['item_identifier']] as $key => $prod_identifier) {
            $product_ids[] = $this->itemExists('product', $config, $data, $key);
          }
        } else {
          $product_ids[] = $this->itemExists('product', $config, $data);
        }
      }
      
      // filter exists ?
      if (!empty($attr['filter_id'])) {
        $item_id = $attr['filter_id'];
      }
        
      // handle extra function with populated values
      if (isset($config['extra_func'])) {
        foreach ($config['extra_func'] as $extra_funcs) {
          foreach ($extra_funcs as $func_name => $func_values) {
            if ($func_name == 'delete_item' && $mode != 'product')  {
              if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
                ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
                ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
                ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
                ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
                ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
                ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
                ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
                if ($item_id) {
                    if (!$this->simulation) {
                      $this->model_catalog_manufacturer->deleteManufacturer($item_id);
                      $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                    } else {
                      $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                    }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                  
                  if (empty($hasProcessed)) {
                    $this->session->data['obui_processed']['processed']++;
                  }
                  
                  $hasProcessed = true;
                  
                  return $data;
                } else {
                  $data['row_status'] = 'skipped';
                  $data['row_msg'] = $this->language->get('text_skip_delete');
                  $this->session->data['obui_processed']['skipped']++;
                  if (empty($hasProcessed)) {
                    $this->session->data['obui_processed']['processed']++;
                  }
                  
                  $hasProcessed = true;
                  return $data;
                }
              }
            } else if ($func_name == 'skip')  {
              if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
                ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
                ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
                ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
                ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
                ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
                ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
                ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
                $data['row_status'] = 'skipped';
                $this->session->data['obui_processed']['skipped']++;
                $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                if (empty($hasProcessed)) {
                  $this->session->data['obui_processed']['processed']++;
                }
                
                $hasProcessed = true;
                
                // put item on no delete list
                if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                  $this->session->data['obui_no_delete'][] = $item_id;
                }
                
                return $data;
              }
            }
          }
        }
      }
      
      $group_id = 'new';
      
      if (($item_id && $config['item_exists'] == 'update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        
        $attr_data = array();

        if (!empty($data['filter_id'])) {
          $attr_data['filter_id'] = $data['filter_id'];
        }
        $attr_data['sort_order'] = 1;
        
        $attr_group = $this->db->query("SELECT filter_group_id FROM " . DB_PREFIX . "filter_group_description WHERE name = '" . $this->db->escape($mainValues['group']) . "'")->row;
        
        $this->load->model('gkd_import/filter');
        
        // group exists - get id
        if (!empty($attr_group['filter_group_id'])) {
          $group_id = $attr_data['filter_group_id'] = $attr_group['filter_group_id'];
        } 
        //  group not exists - create
        else {
          $attr_group_data = array();
          $attr_group_data['sort_order'] = 1;
          
          foreach ($languages as $language) {
            $attr_group_data['filter_group_description'][$language['language_id']]['name'] = !empty($data['filter_description'][$language['language_id']]['group']) ? $data['filter_description'][$language['language_id']]['group'] : 'Default';
          }
          
          if (!$this->simulation) {
            $group_id = $attr_data['filter_group_id'] = $this->model_gkd_import_filter->addFilterGroup($attr_group_data);
          }
        }
        
        // create filter
        foreach ($languages as $language) {
          $attr_data['filter_description'][$language['language_id']]['name'] = !empty($data['filter_description'][$language['language_id']]['name']) ? $data['filter_description'][$language['language_id']]['name'] : 'Default';
        }
      }
      
      if ($item_id) {
        if ($config['item_exists'] == 'update') {
          
          if (!$this->simulation) {
            $this->model_gkd_import_filter->editFilter($item_id, $attr_data);
          }
          $data['row_status'] = 'updated';
          $this->session->data['obui_processed']['updated']++;
        } else {
          // skip item - log
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = $this->language->get('text_skip_update');
        }
      } else {
        if ($config['item_not_exists'] == 'insert') {
          if (!$this->simulation) {
            $item_id = $this->model_gkd_import_filter->addFilter($attr_data);
          }
          $data['row_status'] = 'inserted';
          $this->session->data['obui_processed']['inserted']++;
        } else {
          // skip item - log
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = $this->language->get('text_skip_insert');
        }
      }
      
      if (empty($hasProcessed)) {
        $this->session->data['obui_processed']['processed']++;
      }
      
      $hasProcessed = true;
      
      if ($mode == 'product') {
        if (!isset($this->session->data['obui_deleted_ids'])) {
          $this->session->data['obui_deleted_ids'] = array();
        }
        
        foreach ($product_ids as $product_id) {
          if (!empty($config['delete_filters'])) {
            if (!$this->simulation && !in_array($product_id, $this->session->data['obui_deleted_ids'])) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
              $this->session->data['obui_deleted_ids'][] = $product_id;
            }
          }
          
          if ($product_id) {
            if ($data['row_status'] != 'skipped') {
              if (!$this->simulation) {
                foreach ($data['filter_description'] as $language_id => $filter_description) {
                  $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$item_id . "'");
                }
              }
            }
          } else {
            $data['row_status'] = 'skipped';
            $message = $this->language->get('text_skip_product_not_found');
          }
        }
      } else if ($mode == 'category') {
        if (!isset($this->session->data['obui_deleted_ids'])) {
          $this->session->data['obui_deleted_ids'] = array();
        }
        
        foreach ($category_ids as $category_id) {
          if (!empty($config['delete_filters'])) {
            if (!$this->simulation && !in_array($product_id, $this->session->data['obui_deleted_ids'])) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
              $this->session->data['obui_deleted_ids'][] = $product_id;
            }
          }
          
          if ($category_id) {
            if ($data['row_status'] != 'skipped') {
              if (!$this->simulation) {
                foreach ($data['filter_description'] as $language_id => $filter_description) {
                  $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$item_id . "'");
                }
              }
            }
          } else {
            $data['row_status'] = 'skipped';
            $message = $this->language->get('text_skip_product_not_found');
          }
        }
      } else {
        if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
          $this->session->data['obui_no_delete'][] = $item_id;
        }
      }
      
      if ($mode == 'product' && $product_id) {
        if (defined('GKD_CRON')) {
          $data['row_msg'] = '['.$product_id.'] ' . (!empty($message) ? $message : $mainValues['name']);
        } else if (!empty($message)) {
          $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$product_id.'&' . $this->token, 'SSL').'">'.$product_id.'</a>] '.$message;
        } else {
          $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$product_id.'&' . $this->token, 'SSL').'">'.$product_id.'</a>] '.$mainValues['group'].' : '.$mainValues['name'].'</a>';
        }
      } else if ($group_id && $mode != 'product' && $group_id != 'new') {
        if (defined('GKD_CRON')) {
          $data['row_msg'] = '['.$group_id.'] ' . (!empty($message) ? $message : $mainValues['name']);
        } else if (!empty($message)) {
          $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/filter/edit', 'filter_group_id='.$group_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '.$message;
        } else {
          $data['row_msg'] = '['.$group_id.'] <a target="_blank" href="'.$this->url->link('catalog/filter/edit', 'filter_group_id='.$group_id.'&' . $this->token, 'SSL').'">'.$mainValues['name'].'</a>';
        }
      } else {
        $data['row_msg'] = !empty($message) ? $message : $mainValues['name'];
      }
    
      $arrayData[] = $data;
    }
    
    $arrayData['gkdIsResArray'] = true;
    
    return $arrayData;
  }
  
  public function process_review($config, $line) {
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    
    if (in_array($config['item_identifier'], array('review_id'))) {
      $item_id = $this->itemExists($config['import_type'], $config, $data);
    } else {
      $data['product_id'] = $this->itemExists('product', $config, $data);
      $item_id = false;
    }
    
    // product will be updated, prepare values
    if (($item_id && $config['item_exists'] == 'update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
      
      $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
      $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      
      // data formatters
      if (isset($data['status']))             $data['status'] = $this->handler->booleanHandler('status', $config); // enabled/disabled, on/off, true/false, 1/0
      
      // set default values for custom module compatibility
      if ($config['item_exists'] != 'soft_update') {
        $setDefault = array(
          //'keyword',
        );
        
        foreach ($setDefault as $v) {
          if (!isset($data[$v])) {
            $data[$v] = '';
          }
        }
      }
      
      // unset if empty
      foreach (array() as $key) {
        if (empty($data[$key])) {
          unset($data[$key]);
        }
      }
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                    $data=array();
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "information SET status = 0 WHERE information_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->model_catalog_information->deleteInformation($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update') {
        
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->load->model('catalog/review');
          $this->model_catalog_review->editReview($item_id, $data);
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
    } else {
      if ($config['item_not_exists'] == 'insert') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $this->load->model('catalog/review');
          $this->model_catalog_review->addReview($data);
        }
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    if (!empty($data[$config['item_identifier']])) {
      $item_name = $data[$config['item_identifier']];
    } else if ($item_id) {
      $item_name = 'Product ID '.$item_id;
    } else {
      $item_name = '';
    }
    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $item_name);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/information/edit', 'information_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>]'.$message;
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/information/edit', 'information_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_name.'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $item_name;
    }
    
    return $data;
  }
  
  public function process_customer($config, $line) {
    $this->pre_process($config);
    
    $config['columns_bindings'] = $config['columns'];
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $customer_model = 'model_'.(version_compare(VERSION, '2.1', '>=') ? 'customer':'sale').'_customer';
    
    $data = &$config['columns'];
    
    $item_id = $this->itemExists($config['import_type'], $config, $data);
    
    // item will be processed, prepare values
    $data['affiliate'] = '';
    
    if (($item_id && $config['item_exists'] == 'soft_update') || ($item_id && $config['item_exists'] == 'update') || (!$item_id && $config['item_not_exists'] == 'insert')) {
      
      
      if ($item_id && $config['item_exists'] == 'soft_update') {
        $config['columns'] = $this->recursive_array_intersect_key($config['columns'], $this->walk_recursive_remove($config['columns_bindings']));
        
        $data = &$config['columns'];
      }
      
      $data['gkd_extra_fields'] = !empty($config['extra_forced']) ? $config['extra_forced'] : array();
      $data['gkd_extra_desc_fields'] = !empty($config['extraml_forced']) ? $config['extraml_forced'] : array();
      
      // data formatters
      if (isset($data['customer_group_id']))  $data['customer_group_id'] = $this->handler->customerGroupHandler($data['customer_group_id'], $config);
      if (isset($data['status']))             $data['status'] = $this->handler->booleanHandler('status', $config); // enabled/disabled, on/off, true/false, 1/0
      if (isset($data['newsletter']))         $data['newsletter'] = $this->handler->booleanHandler('newsletter', $config); // enabled/disabled, on/off, true/false, 1/0
      if (isset($data['approved']))           $data['approved'] = $this->handler->booleanHandler('approved', $config); // enabled/disabled, on/off, true/false, 1/0
      if (isset($data['safe']))               $data['safe'] = $this->handler->booleanHandler('safe', $config); // enabled/disabled, on/off, true/false, 1/0
      
      //$data['image'] = $this->handler->imageHandler('image', $config);
      //$data['address'] = $this->handler->dataArrayHandler('address', $config);
      
      if (isset($data['address'])) {
        $hasDefault = false;
        
        foreach ($data['address'] as $key => $address) {
          if (isset($data['address'][$key]['country_id'])) {
            $data['address'][$key]['country_id'] = $this->handler->countryHandler($data['address'][$key]['country_id'], $config);
          }
          
          if (isset($data['address'][$key]['zone_id'])) {
            $data['address'][$key]['zone_id'] = $this->handler->zoneHandler($data['address'][$key]['zone_id'], $config, $data['address'][$key]['country_id']);
          }
          
          if (isset($data['address'][$key]['default']) && !$this->handler->isBoolean($data['address'][$key]['default'])) {
            unset($data['address'][$key]['default']);
          } else {
            $hasDefault = true;
          }
          
          if (!empty($data['address'][$key]['custom_field'])) {
            foreach ($data['address'][$key]['custom_field'] as $cf_key => $custom_field) {
              //$data['address'][$key]['custom_field'][$cf_key] = $this->simpleArrayHandlerValue($data['address'][$key]['custom_field'][$cf_key], $config);
            }
          }
        }
        
        // filter empty address
        foreach ($data['address'] as $key => $address) {
          $address = $this->array_filter_recursive($address);
          
          if (empty($address)) {
            unset($data['address'][$key]);
          }
        }
        
        // set default
        if (!$hasDefault && !empty($data['address'])) {
          reset($data['address']);
          $data['address'][key($data['address'])]['default'] = 1;
        }
      }
      
      if (!empty($data['custom_field'])) {
        foreach ($data['custom_field'] as $key => $custom_field) {
          $data['custom_field'][$key] = $this->handler->customerCustomFieldsHandler($key, $data['custom_field'][$key], $config);
        }
      }
    }
    
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                if (isset($func_values['action']) && $func_values['action'] == 'disable') {
                    $data=array();
                  if (!$this->simulation) {
                    $this->db->query("UPDATE " . DB_PREFIX . "customer SET status = 0 WHERE customer_id = '" . (int) $item_id . "'");
                    $data['row_msg'] = $this->language->get('text_simu_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_disabled') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'disabled';
                  $this->session->data['obui_processed']['disabled']++;
                } else {
                  if (!$this->simulation) {
                    $this->{$customer_model}->deleteCustomer($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                  
                  $data['row_status'] = 'deleted';
                  $this->session->data['obui_processed']['deleted']++;
                }
                
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if (empty($config['pwd_hash'])) {
      unset($data['salt']);
    }
    
    if ($item_id) {
      if ($config['item_exists'] == 'update' || $config['item_exists'] == 'soft_update') {
        if ($config['item_exists'] == 'update') {
          if (!$this->simulation) {
            $data = $this->request->clean($data);
            // use remove and add because of address_id that is required in edit and we don't have this data when importing
            $this->{$customer_model}->deleteCustomer($item_id);
            $this->{$customer_model}->addCustomer($data);
            //$this->{$customer_model}->editCustomer($item_id, $data);
          }
        } else if ($config['item_exists'] == 'soft_update') {
          if (!$this->simulation) {
            $data = $this->request->clean($data);
            
            $this->load->model('gkd_import/customer');
            $this->model_gkd_import_customer->editCustomer($item_id, $data);
          }
        }
        $data['row_status'] = 'updated';
        $this->session->data['obui_processed']['updated']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_update');
      }
    } else {
      if ($config['item_not_exists'] == 'insert') {
        if (!$this->simulation) {
          $data = $this->request->clean($data);
          $item_id = $this->{$customer_model}->addCustomer($data);
        }
        $data['row_status'] = 'inserted';
        $this->session->data['obui_processed']['inserted']++;
      } else {
        // skip item - log
        $data['row_status'] = 'skipped';
        $this->session->data['obui_processed']['skipped']++;
        $message = $this->language->get('text_skip_insert');
      }
    }
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
      $this->session->data['obui_no_delete'][] = $item_id;
    }
    
    if (version_compare(VERSION, '2.1', '>=')) {
      $edit_url = 'customer/customer/edit';
    } else {
      $edit_url = 'sale/customer/edit';
    }
    
    if ($item_id) {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = $item_id . ' - ' . (!empty($message) ? $message : $data['email']);
      } else if (!empty($message)) {
        $data['row_msg'] = '<a target="_blank" href="'.$this->url->link($edit_url, 'customer_id='.$item_id.'&' . $this->token, 'SSL').'">'.$data['email'].'</a> - ' . $message;
      } else {
        $data['row_msg'] = '<a target="_blank" href="'.$this->url->link($edit_url, 'customer_id='.$item_id.'&' . $this->token, 'SSL').'">'.$data['email'].'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $data['email'];
    }
    
    return $data;
  }
  
  public function process_car_shop($config, $line) {
    $this->load->model('tool/car_shop');
    $this->pre_process($config);
    
    $this->populate_fields($config, $line);
    $this->populate_extra_func($config, $line);
    
    $data = &$config['columns'];
    $data = $this->request->clean($data);
    
    $product_ids = array();
    
    //$item_id = $this->itemExists($config['import_type'], $config, $data);
    $item_id = false;
    
    // car parts list
    if (in_array($config['item_identifier'], array('carshop_list_id'))) {
      $mode = 'list';
      
    // product car parts
    } else {
      $mode = 'product';
      
      if (isset($data[$config['item_identifier']]) && is_array($data[$config['item_identifier']])) {
        foreach ($data[$config['item_identifier']] as $key => $prod_identifier) {
          $product_ids[] = $this->itemExists('product', $config, $data, $key);
        }
      } else {
        $product_ids[] = $this->itemExists('product', $config, $data);
      }
    }
    
    $newData = array();
    
    foreach ($data as $k => $v) {
      $newData[str_replace('cs_', '', $k)] = $v;
    }
    
    $data = $newData;
    
    // attribute exists ?
    if ($mode == 'list' && !empty($attr['carshop_list_id'])) {
      $itemExists = $this->model_tool_car_shop->getCarShopList($attr['carshop_list_id']);
      $item_id = !empty($itemExists['carshop_list_id']) ? $itemExists['carshop_list_id'] : false;
    }
      
    // handle extra function with populated values
    if (isset($config['extra_func'])) {
      foreach ($config['extra_func'] as $extra_funcs) {
        foreach ($extra_funcs as $func_name => $func_values) {
          if ($func_name == 'delete_item' && $mode != 'product')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              if ($item_id) {
                  if (!$this->simulation) {
                    $this->model_catalog_manufacturer->deleteManufacturer($item_id);
                    $data['row_msg'] = $this->language->get('text_simu_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  } else {
                    $data['row_msg'] = $this->language->get('text_rows_deleted') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
                  }
                
                $data['row_status'] = 'deleted';
                $this->session->data['obui_processed']['deleted']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              } else {
                $data['row_status'] = 'skipped';
                $data['row_msg'] = $this->language->get('text_skip_delete');
                $this->session->data['obui_processed']['skipped']++;
                $this->session->data['obui_processed']['processed']++;
                return $data;
              }
            }
          } else if ($func_name == 'skip')  {
            if (($func_values['comparator'] == 'is_equal' && $func_values['field'] == $func_values['value']) ||
              ($func_values['comparator'] == 'is_equal_list' && in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_not_equal' && $func_values['field'] != $func_values['value']) ||
              ($func_values['comparator'] == 'is_not_equal_list' && !in_array($func_values['field'], explode('|', $func_values['value']))) ||
              ($func_values['comparator'] == 'is_greater' && $func_values['field'] > $func_values['value']) ||
              ($func_values['comparator'] == 'is_lower' && $func_values['field'] < $func_values['value']) ||
              ($func_values['comparator'] == 'contain' && strpos($func_values['field'], $func_values['value']) !== false) ||
              ($func_values['comparator'] == 'not_contain' && strpos($func_values['field'], $func_values['value']) === false)) {
              $data['row_status'] = 'skipped';
              $this->session->data['obui_processed']['skipped']++;
              $data['row_msg'] = $this->language->get('text_rows_skipped') . ' - ' . (isset($func_values['orig_field']) ? $func_values['orig_field'] : $func_values['field']) . ' ' . strtolower($this->language->get('xfn_'.$func_values['comparator'])) . ' ' . (isset($func_values['orig_fieldval']) ? $func_values['orig_fieldval'] : $func_values['value']);
              $this->session->data['obui_processed']['processed']++;
              
              // put item on no delete list
              if ($item_id && !empty($config['delete']) && $config['delete'] != 'all' && !empty($config['no_delete_skipped'])) {
                $this->session->data['obui_no_delete'][] = $item_id;
              }
              
              return $data;
            }
          }
        }
      }
    }
    
    if ($mode == 'list') {
      if ($item_id) {
        if ($config['item_exists'] == 'update') {
          if (!$this->simulation) {
            $this->model_tool_car_shop->addEditCarShopList($data, $item_id);
          }
          
          $data['row_status'] = 'updated';
          $this->session->data['obui_processed']['updated']++;
        } else {
          // skip item - log
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = $this->language->get('text_skip_update');
        }
      } else {
        if ($config['item_not_exists'] == 'insert') {
          if (!$this->simulation) {
            $this->model_tool_car_shop->addEditCarShopList($data);
          }
          
          $data['row_status'] = 'inserted';
          $this->session->data['obui_processed']['inserted']++;
        } else {
          // skip item - log
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = $this->language->get('text_skip_insert');
        }
      }
    }
    
    if ($mode == 'product') {
      if (!isset($this->session->data['obui_deleted_ids'])) {
        $this->session->data['obui_deleted_ids'] = array();
      }
      
      foreach ($product_ids as $product_id) {
        if (!$product_id) {
          $data['row_status'] = 'skipped';
          $this->session->data['obui_processed']['skipped']++;
          $message = 'Product not found';
          
          continue;
        }
        /*
        if (!empty($config['delete_attributes'])) {
          if (!$this->simulation && !in_array($product_id, $this->session->data['obui_deleted_ids'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
            $this->session->data['obui_deleted_ids'][] = $product_id;
          }
        }
        */
        if (empty($config['attr_insert_type']) || (!empty($config['attr_insert_type']) && $config['attr_insert_type'] == 'rm_add' && !in_array($product_id, $this->session->data['obui_processed_ids']))) {
          if (!$this->simulation) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "carshop_part WHERE product_id = '" . (int)$product_id . "'");
          }
          
          $this->session->data['obui_deleted_ids'][] = $product_id;
        }
        
        $data['product_id'] = $product_id;
        
        if (!empty($data['carshop_list_id'])) {
          $carshop_list_id = $data['carshop_list_id'];
          $data = $this->model_tool_car_shop->getCarShopList($carshop_list_id);
          
          if (!empty($data)) {
            $data['product_id'] = $product_id;
          } else {
            $this->tool->log(array(
              'row' => $this->session->data['obui_current_line'],
              'status' => 'error',
              'title' => $this->language->get('warning'),
              'msg' => 'The Car Part source entry does not exists for ID: '.$carshop_list_id,
            ));
            
            $data['row_status'] = 'error';
            $this->session->data['obui_processed']['error']++;
            
            continue;
          }
        }
        
        if ($item_id) {
          if ($config['item_exists'] == 'update') {
            if (!$this->simulation) {
              $carShopModels = (array) $data['model'];
              $carShopEngines = (array) $data['engine'];
              foreach ($carShopModels as $carShopModel) {
                foreach ($carShopEngines as $carShopEngine) {
                  $data['model'] = trim($carShopModel);
                  $data['engine'] = trim($carShopEngine);
                  
                  $this->model_tool_car_shop->addEditCarShopPart($data, $item_id);
                }
              }
            }
            
            $data['row_status'] = 'updated';
            $this->session->data['obui_processed']['updated']++;
          } else {
            // skip item - log
            $data['row_status'] = 'skipped';
            $this->session->data['obui_processed']['skipped']++;
            $message = $this->language->get('text_skip_update');
          }
        } else {
          if ($config['item_not_exists'] == 'insert') {
            if (!$this->simulation) {
              $carShopModels = (array) $data['model'];
              $carShopEngines = (array) $data['engine'];
              foreach ($carShopModels as $carShopModel) {
                foreach ($carShopEngines as $carShopEngine) {
                  $data['model'] = trim($carShopModel);
                  $data['engine'] = trim($carShopEngine);
                  
                  $this->model_tool_car_shop->addEditCarShopPart($data);
                }
              }
            }
            
            $data['row_status'] = 'inserted';
            $this->session->data['obui_processed']['inserted']++;
          } else {
            // skip item - log
            $data['row_status'] = 'skipped';
            $this->session->data['obui_processed']['skipped']++;
            $message = $this->language->get('text_skip_insert');
          }
        }
      }
    } /*else {
      if ($mode != 'attribute_group') {
        if ($item_id && !empty($config['delete']) && $config['delete'] != 'all') {
          $this->session->data['obui_no_delete'][] = $item_id;
        }
      }
    }*/
    
    $this->session->data['obui_processed']['processed']++;
    
    if ($mode == 'product' && $product_id) {
      // save processed item id for further use - only if necessary
      if (!empty($config['attr_insert_type']) && $config['attr_insert_type'] == 'rm_add') {
        $this->session->data['obui_processed_ids'][$product_id] = $product_id;
      }
    
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$product_id.'] ' . (!empty($message) ? $message : $item_id);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$product_id.'&' . $this->token, 'SSL').'">'.$product_id.'</a>] '.$message;
      } else {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/product/edit', 'product_id='.$product_id.'&' . $this->token, 'SSL').'">'.$product_id.'</a>] '.$item_id.'</a>';
      }
    } else if ($item_id && $mode != 'product') {
      if (defined('GKD_CRON')) {
        $data['row_msg'] = '['.$item_id.'] ' . (!empty($message) ? $message : $item_id);
      } else if (!empty($message)) {
        $data['row_msg'] = '[<a target="_blank" href="'.$this->url->link('catalog/attribute/edit', 'attribute_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>] '.$message;
      } else {
        $data['row_msg'] = '['.$item_id.'] <a target="_blank" href="'.$this->url->link('catalog/attribute/edit', 'attribute_id='.$item_id.'&' . $this->token, 'SSL').'">'.$item_id.'</a>';
      }
    } else {
      $data['row_msg'] = !empty($message) ? $message : $item_id;
    }
    
    return $data;
  }
  private function array_filter_recursive($input) {
    foreach ($input as &$value)
    {
      if (is_array($value)) {
        $value = $this->array_filter_recursive($value);
      }
    }
   
    return array_filter($input);
  } 
  
  
  /*
  protected function optionHandler($field, &$config) {
    $return_values = $values_array = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    foreach ((array) $config['columns'][$field] as $value) {
      if (!$value) {
        continue;
      } else if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    if ($this->simulation) {
      return $values_array;
    }
    
    foreach ($values_array as &$value) {
      $values = explode(':', $value);
      $value = array(
        'field_name' => $values[0],
        'field_value' => $values[1],
      );
    }
    
    return $values_array;
  }
  */
  protected $customer_groups = array();
  
  protected function loadCustomerGroups() {
    if (!$this->customer_groups) {
      $query = $this->db->query("SELECT customer_group_id FROM " . DB_PREFIX . "customer_group")->rows;
      
      foreach ($query as $cg) {
        $this->customer_groups[] = $cg['customer_group_id'];
      }
    }
  }
  
  protected function populate_extra_func(&$config, &$line) {
    if (!empty($config['extra_func'])) {
      foreach ($config['extra_func'] as &$extra_funcs) {
        foreach ($extra_funcs as $func_type => &$func) {
          if (in_array($func_type, array('skip', 'skip_db', 'delete_item')) && isset($func['fieldval']) && isset($line[$func['fieldval']])) {
            if (isset($func['fieldval']) && $func['fieldval'] !== '' && isset($line[$func['fieldval']])) {
              $column_headers = (array) json_decode(base64_decode($config['column_headers']));
              if (isset($column_headers[$func['fieldval']])) {
                $func['orig_fieldval'] = $column_headers[$func['fieldval']] . ' (' . $line[$func['fieldval']] . ')';
              }
              $func['value'] = $line[$func['fieldval']];
            }
          }
        }
      }
    }
  }
  
  protected function populate_fields(&$config, &$line) {
    // populate extra functions
    if (!empty($config['extra_func'])) {
      foreach ($config['extra_func'] as &$extra_funcs) {
        foreach ($extra_funcs as $func_type => &$func) {
          // if multiple values set defaulf field as first one
          if (isset($func['fields'][0]) && $func['fields'][0] !== '') {
            $func['field'] = $func['fields'][0];
          }
          
          if (!isset($func['field']) || $func['field'] !== '') {
            if (isset($func['field']) && isset($line[$func['field']])) {
              $init_value = $line[$func['field']];
            } else if (!empty($func['field'])) {
              $init_value = $this->model_gkd_import_handler->getArrayPath($line, $func['field']);
            } else {
              $init_value = '';
            }
            
            if (is_string($init_value)) {
              $init_value = htmlspecialchars_decode($init_value);
            }
            
            if (!empty($func['target'])) {
              $target = $func['target'];
            } else if (isset($func['field'])){
              $target = $func['field'];
            } else {
              $target = false;
            }
            
            if ($target !== false && !isset($line[$target])) {
              $line[$target] = '';
            }
            
            if (isset($func['fieldval']) && $func['fieldval'] !== '' && isset($line[$func['fieldval']])) {
              $value = $line[$func['fieldval']];
            } else {
              $value = isset($func['value']) ?  htmlspecialchars_decode($func['value']) : '';
            }
            
            if (isset($func['field2']) && $func['field2'] !== '' && isset($line[$func['field2']])) {
              $value2 = $line[$func['field2']];
            } else {
              $value2 = isset($func['field2_val']) ?  htmlspecialchars_decode($func['field2_val']) : '';
            }
            
            // save original field name for further information
            if (in_array($func_type, array('skip', 'delete_item'))) {
              if (!isset($column_headers)) {
                $column_headers = (array) json_decode(base64_decode($config['column_headers']));
              }
              
              if (isset($column_headers[$func['field']])) {
                $func['orig_field'] = $column_headers[$func['field']];
                
                if (is_scalar($init_value)) {
                  $func['orig_field'] .= ' (' . $init_value . ')';
                }
              }
            }
            
            // Math
            if ($func_type == 'add') {
              $line[$target] = (float) $value + (float) $init_value;
            } else if ($func_type == 'subtract') {
              $line[$target] = (float) $init_value - (float) $value;
            } else if ($func_type == 'multiply') {
              $line[$target] = (float) $value * (float) $init_value;
            } else if ($func_type == 'percentage') {
              $line[$target] = (1 + (float) $value / 100) * (float) $init_value;
            } else if ($func_type == 'divide') {
              if ((float) $value > 0) {
                $line[$target] = (float) $init_value / (float) $value;
              }
            } else if ($func_type == 'round') {
              $line[$target] = round((float) $init_value, (int) $value);
            } else if ($func_type == 'random') {
              $min = (float) $value;
              $max = isset($func['value2']) ?  $func['value2'] : 1;
              
              $decimals = max(strlen(substr(strrchr($min+'', '.'), 1)), strlen(substr(strrchr($max+'', '.'), 1)));
              $factor = pow(10, $decimals);
              $line[$target] = rand($min*$factor, $max*$factor) / $factor;
            } else if ($func_type == 'to_float') {
              $dotPos = strrpos($init_value, '.');
              $commaPos = strrpos($init_value, ',');
              $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
                  ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
                  
              if (!$sep) {
                $line[$target] = floatval(preg_replace("/[^0-9]/", "", $init_value));
              } else {
                $line[$target] = floatval(
                    preg_replace("/[^0-9]/", "", substr($init_value, 0, $sep)) . '.' .
                    preg_replace("/[^0-9]/", "", substr($init_value, $sep+1, strlen($init_value)))
                );
              }
            } else if ($func_type == 'wholesale') {
              $processWholesale = true;
              
              if (strpos($value, '[') !== false) {
                $processWholesale = false;
                
                $wholesaleTableCats = explode('[', $value);
                
                foreach ($wholesaleTableCats as $wholesaleTableCats) {
                  if (!isset($wholesaleTableCats[1])) continue;
                  
                  list($catName, $catTable) = explode(']', $wholesaleTableCats);
                  
                  if (isset($config['columns']['product_category'][0]) && isset($line[$config['columns']['product_category'][0]])) {
                    if ($line[$config['columns']['product_category'][0]] == $catName || $catName == '*') {
                      $value = $catTable;
                      $processWholesale = true;
                      break;
                    }
                  }
                }
              }
              
              if ($processWholesale) {
                $wholesaleTable = explode("\n", $value);
                natsort($wholesaleTable);
                
                foreach ($wholesaleTable as $row) {
                  if (!$row) continue;
                  $wholesale = explode(':', $row);
                  
                  if (!isset($wholesale[1])) continue;
                  
                  if ($init_value < $wholesale[0]) {
                    if (strpos($wholesale[1], '%')) {
                      $line[$target] = (1 + (float) $wholesale[1] / 100) * (float) $init_value;
                    } else if (strpos($wholesale[1], '+') !== false || strpos($wholesale[1], '-') !== false) {
                      $line[$target] = (float) $init_value + (float) $wholesale[1];
                    } else if (strpos($wholesale[1], '/') !== false) {
                      if (str_replace('/', '', $wholesale[1]) != 0) {
                        $line[$target] = (float) $init_value / (float) str_replace('/', '', $wholesale[1]);
                      } else {
                        $line[$target] = (float) $init_value;
                      }
                    } else if (strpos($wholesale[1], '*') !== false) {
                      $line[$target] = (float) $init_value * (float) str_replace('*', '', $wholesale[1]);
                    } else {
                      $line[$target] = (float) $init_value * (float) $wholesale[1];
                    }
                    
                    if ($func['round'] !== '') {
                      $line[$target] = round((float) $line[$target], (int) $func['round']);
                    }
                    
                    if ($this->simulation) {
                      //$line[$target] .= ' ('.trim($wholesale[1]).')';
                    }
                    break;
                  }
                }
              } else {
                $line[$target] = (float) $init_value;
                
                if ($this->simulation) {
                  //$line[$target] .= ' (no change, category not found)';
                }
              }
              
            // String
            } else if ($func_type == 'uppercase') {
              $line[$target] = mb_strtoupper($init_value);
            } else if ($func_type == 'lowercase') {
              $line[$target] = mb_strtolower($init_value);
            } else if ($func_type == 'ucfirst') {
              $line[$target] = ucfirst(mb_strtolower($init_value));
            } else if ($func_type == 'ucwords') {
              $line[$target] = ucwords(mb_strtolower($init_value));
            } else if ($func_type == 'prepend') {
              $line[$target] = $init_value;
              
              if (is_array($line[$target])) {
                foreach ($line[$target] as &$arrValue) {
                  if (is_scalar($arrValue)) {
                    $arrValue = $value . $arrValue;
                  }
                }
              } else if (is_scalar($line[$target])) {
                $line[$target] = $value . $init_value;
              }
            } else if ($func_type == 'append') {
              $line[$target] = $init_value;
              
              if (is_array($line[$target])) {
                foreach ($line[$target] as &$arrValue) {
                  if (is_scalar($arrValue)) {
                    $arrValue = $arrValue . $value;
                  }
                }
              } else if (is_scalar($line[$target])) {
                if (is_scalar($value)) {
                  $line[$target] = $init_value . $value;
                }
              }
            } else if ($func_type == 'tag') {
              $line[$target] = $init_value;
              
              $replace = array(
                '[current_row]' => $this->session->data['obui_current_line'],
                '[unique_id]' => uniqid(),
                '[date]' => date('Y-m-d'),
              );
              
              if (is_array($line[$target])) {
                foreach ($line[$target] as &$arrValue) {
                  $arrValue = str_replace(array_keys($replace), $replace, $value);
                }
              } else if (is_scalar($line[$target])) {
                $line[$target] = str_replace(array_keys($replace), $replace, $value);
              }
            } else if ($func_type == 'combine') {
              $implodeArray = array();
              foreach($func['fields'] as $f) {
                $val = $this->model_gkd_import_handler->getArrayPath($line, $f, false);
                
                // if the value is an array get the inside values
                if (isset($line[$f]) && is_array($line[$f])) {
                  foreach($line[$f] as $v) {
                    if (is_scalar($v) && trim($v) !== '') {
                      $implodeArray[] = $v;
                    }
                  }
                
                // try to get value with getArrayPath if not found
                } else if (!isset($line[$f]) && strpos($f, '/')) {
                  $getVal = $this->model_gkd_import_handler->getArrayPath($line, $f, false);
                  
                  if (is_scalar($getVal) && trim($getVal) !== '') {
                    $implodeArray[] = $getVal;
                  } else if (is_array($getVal)) {
                    foreach($getVal as $v) {
                      if (is_scalar($v) && trim($v) !== '') {
                        $implodeArray[] = $v;
                      }
                    }
                  }
                  
                // get value directly if it exists
                } else if (isset($line[$f]) && is_scalar($line[$f]) && trim($line[$f]) !== '') {
                  $implodeArray[] = $line[$f];
                }
              }
              
              $line[$target] = implode($value, $implodeArray);
            } else if ($func_type == 'field_replace')  {
              if (($func['comparator'] == 'is_equal' && $init_value == $value) ||
                ($func['comparator'] == 'is_equal_list' && in_array($init_value, explode('|', $value))) ||
                ($func['comparator'] == 'is_not_equal' && $init_value != $value) ||
                ($func['comparator'] == 'is_not_equal_list' && !in_array($init_value, explode('|', $value))) ||
                ($func['comparator'] == 'is_greater' && $init_value > $value) ||
                ($func['comparator'] == 'is_lower' && $init_value < $value) ||
                ($func['comparator'] == 'contain' && strpos($init_value, $value) !== false) ||
                ($func['comparator'] == 'not_contain' && strpos($init_value, $value) === false)) {
                $line[$target] = $value2;
              }
            } else if ($func_type == 'replace') {
              $line[$target] = $init_value;
              
              if (is_array($line[$target])) {
                foreach ($line[$target] as &$arrValue) {
                  if (is_scalar($arrValue)) {
                    $arrValue = str_replace($value, htmlspecialchars_decode($value2), htmlspecialchars_decode($arrValue));
                  }
                }
              } else if (is_scalar($line[$target])) {
                $line[$target] = str_replace($value, htmlspecialchars_decode($value2), htmlspecialchars_decode($init_value));
              }
            } else if ($func_type == 'remove') {
              $line[$target] = $init_value;
              
              if (is_array($line[$target])) {
                foreach ($line[$target] as &$arrValue) {
                  if (is_scalar($arrValue)) {
                    $arrValue = str_replace($value, '', $arrValue);
                  }
                }
              } else if (is_scalar($line[$target])) {
                $line[$target] = str_replace($value, '', $init_value);
              }
            } else if ($func_type == 'strlen') {
              $line[$target] = $init_value;
              
              if (is_scalar($init_value)) {
                $line[$target] = mb_strlen($init_value);
              } else if (is_array($init_value)) {
                $line[$target] = count($init_value);
              } else {
                $line[$target] = 0;
              }
            } else if ($func_type == 'substr') {
              $line[$target] = mb_substr($init_value, 0, (int)$value);
            } else if ($func_type == 'urlify') {
               if (!empty($func['ascii'])) {
                 $line[$target] = $this->tool->urlify($init_value, $func['ascii']);
               } else {
                 $line[$target] = $this->tool->urlify($init_value);
               }
            } else if ($func_type == 'if_table') {
              $ifTable = explode("\n", $value);
              
              foreach ($ifTable as $row) {
                //preg_match('/^(.+)\((.*)\):(.*)$/', $row, $result);
                preg_match('/^(.+?):(.*)$/', $row, $result);
                
                if (!isset($result[1])) {
                  $this->tool->log(array(
                    'row' => $this->session->data['obui_current_line'],
                    'status' => 'error',
                    'title' => $this->language->get('warning'),
                    'msg' => 'Incorrect format in Conditional values function.<br>The format on each line of each line should be like: <b>[condition_sign](value):final_value</b><br>The line that failed is: <b>'.$row.'</b>',
                  ));
                  continue;
                }
                
                preg_match_all('/(?:(=|!=|~|!~|>|<)\((.*?)\))+/', $result[1], $conditions, PREG_SET_ORDER);
                
                // process array of values
                if (is_array($init_value)) {
                  $condOk = array();
                  $line[$target] = array();
                  foreach ($init_value as $init_key => $init_val) {
                    $condOk[$init_key] = false;
                  
                    // loop through conditions, all must validate if multiple separated by &
                    foreach ($conditions as $cond) {
                      $replaces = array(
                        '{current_date}' => date('Y-m-d'),
                      );
                      
                      $cond[2] = str_replace(array_keys($replaces), array_values($replaces), $cond[2]);
                      
                      if ($this->model_gkd_import_tool->validateCondition($cond[1], $init_val, $cond[2])) {
                        $condOk[$init_key] = true;
                      } else {
                        $condOk[$init_key] = false;
                        break;
                      }
                    }
                    
                    if (isset($result[2])) {
                      if ($condOk[$init_key]) {
                        $line[$target][$init_key] = $result[2];
                        $line[$target][$init_key] = str_replace('{current}', $init_val, $line[$target][$init_key]);
                      } else {
                        $line[$target][$init_key] = $init_val;
                      }
                      
                       $init_value[$init_key] = $line[$target][$init_key];
                    }
                  }
                  
                  // if path mode update also original data
                  if (strpos($target, '/')) {
                    $line = $this->model_gkd_import_tool->updateArray($line, $target, $line[$target]);
                  }
                  
                // process single value
                } else {
                  $condOk = false;
                  
                  // loop through conditions, all must validate if multiple separated by &
                  foreach ($conditions as $cond) {
                    $replaces = array(
                      '{current_date}' => date('Y-m-d'),
                    );
                    
                    $cond[2] = str_replace(array_keys($replaces), array_values($replaces), $cond[2]);
                    
                    if ($this->model_gkd_import_tool->validateCondition($cond[1], $init_value, $cond[2])) {
                      $condOk = true;
                    } else {
                      $condOk = false;
                      break;
                    }
                  }
                  
                  if (isset($result[2])) {
                    if ($condOk) {
                      $line[$target] = $result[2];
                      $line[$target] = str_replace('{current}', $init_value, $line[$target]);
                      break;
                    } else {
                      $line[$target] = $init_value;
                    }
                  }
                }
              }
              
            // Currency
            } else if ($func_type == 'oc_currency') {
              if (empty($this->ocCurrencyRates[$func['from']])) {
                $this->load->model('localisation/currency');
                $currency = $this->model_localisation_currency->getCurrencyByCode($func['from']);
                
                $this->ocCurrencyRates[$func['from']] = $currency['value'];
                
                if (empty($this->ocCurrencyRates[$func['from']])) {
                  $this->tool->log(array(
                    'row' => $this->session->data['obui_current_line'],
                    'status' => 'error',
                    'title' => $this->language->get('text_simu_error'),
                    'msg' => 'Unable to calculate currency',
                  ));
                }
              }
              
              $line[$target] = floatval($init_value) / floatval($this->ocCurrencyRates[$func['from']]);
              
              if ($func['round'] !== '') {
                $line[$target] = round((float) $line[$target], (int) $func['round']);
              }
            
            // Regex
            } else if ($func_type == 'regex') {
              preg_match('/'.$value.'/ms', $init_value, $matches);
              $line[$target] = isset($matches[1]) ? $matches[1] : '';
            } else if ($func_type == 'regex_remove') {
              $line[$target] = preg_replace('/'.$value.'/ms', '', $init_value);
            } else if ($func_type == 'regex_replace') {
              $line[$target] = preg_replace('/'.$value.'/ms', (isset($func['value2']) ?  htmlspecialchars_decode($func['value2']) : ''), $init_value);
            
            // Web
            } else if ($func_type == 'remote_content') {
              if ($init_value) {
                $line[$target] = file_get_contents($init_value);
              }
            } else if ($func_type == 'remote_api') {
              foreach ($line as $k => $v) {
                if (is_scalar($v)) {
                  $func['url'] = str_replace('{'.$k.'}', $v, $func['url']);
                }
              }
              
              foreach ($config['columns_bindings'] as $k => $v) {
                if (is_scalar($v) && isset($line[$v]) && is_scalar($line[$v])) {
                  $func['url'] = str_replace('{'.$k.'}', $line[$v], $func['url']);
                }
              }
              
              try {
                $debug = false;
                $res = $this->model_gkd_import_tool->callAPI($func['url'], $func['auth'], $debug, false, $func['path']);
              } catch (Exception $e) {
                $res = '';
                
                $this->tool->log(array(
                  'row' => $this->session->data['obui_current_line'],
                  'status' => 'warning',
                  'title' => $this->language->get('text_simu_error'),
                  'msg' => $e->getMessage(),
                ));
              }
              
              if (!empty($func['transformer'])) {
                list($transformClass, $transformMethod) = explode('/', $func['transformer']);
                
                $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
                $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
                
                $this->load->model('gkd_import/transformer/'.$transformClass);
                $res = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($res);
              }
              
              $line[$target] = $res;
              
            // HTML
            } else if ($func_type == 'nl2br') {
              $line[$target] = nl2br($init_value);
            } else if ($func_type == 'strip_tags') {
              $line[$target] = strip_tags($init_value);
            } else if ($func_type == 'html_fix') {
              $line[$target] =  @html_entity_decode(@htmlspecialchars($init_value, ENT_QUOTES | ENT_IGNORE, 'UTF-8'), ENT_QUOTES | ENT_IGNORE, 'UTF-8');
            } else if ($func_type == 'html_encode') {
              $line[$target] = @htmlspecialchars($init_value, ENT_QUOTES | ENT_IGNORE, 'UTF-8');
            } else if ($func_type == 'html_decode') {
              $line[$target] = @html_entity_decode($init_value, ENT_QUOTES | ENT_IGNORE, 'UTF-8');
            } else if ($func_type == 'urlencode') {
              $line[$target] = urlencode($init_value);
            } else if ($func_type == 'urldecode') {
              $line[$target] = urldecode($init_value);
              
              
            // Extra
            } else if ($func_type == 'date_convert') {
              $date_format = !empty($func['to']) ? $func['to'] : 'Y-m-d H:i:s';
              if ($func['format'] == 'sql') {
                $line[$target] = date($date_format, strtotime($init_value));
              } else if ($func['format'] == 'us') {
                $line[$target] = date($date_format, strtotime(str_replace('-', '/', $init_value)));
              } else if ($func['format'] == 'xls') {
                $line[$target] = date($date_format, ((int) $init_value-25569)*86400);
              } else {
                $line[$target] = date($date_format, strtotime(str_replace('/', '-', $init_value)));
              }
            }
            
            #custom_extra_function_handler#
            
            /* do not clean, it will be done in process
            if (isset($line[$target]) && is_string($line[$target])) {
              $line[$target] = $this->request->clean($line[$target]);
            }
            */
            
            // save value for use in process
            if (isset($func['field'])) {
              $func['field'] = isset($line[$func['field']]) ? $line[$func['field']] : '';
            }
          }
        }
      }
    }

    // recursive populate
    array_walk_recursive($config['columns'], array($this,'array_walk_populate'), $line);
    
    // assign default values
    if (!empty($config['defaults'])) {
      foreach ($config['defaults'] as $key => &$val) {
        //if (((!isset($config['item_exists']) || (isset($config['item_exists']) && $config['item_exists'] != 'soft_update')) && !isset($config['columns'][$key])) || (isset($config['columns'][$key]) && $config['columns'][$key] === '')) {
        //if ((!isset($config['columns'][$key])) || (isset($config['columns'][$key]) && $config['columns'][$key] === '')) {
        // set default value only if the field exists
        
        if (isset($config['columns'][$key]) && $config['columns'][$key] === '') {
          $config['columns'][$key] = is_string($val) ? trim($val) : $val;
        } else if (isset($config['columns'][$key]) && isset($config['columns'][$key][0]) && $config['columns'][$key][0] === '') {
          $config['columns'][$key] = is_string($val) ? trim($val) : $val;
        }
      }
    }
  }
  
  protected function categoryExists($values, $parent_id) {
    foreach ((array) $values as $value) {
      if (empty($value['name'])) {
        continue;
      }
      
      if (strpos($parent_id, ']') !== false) {
        $parent_id = str_replace('[', '', strstr($parent_id, ']', true));
      }
      
      $cat_exists = $this->db->query("SELECT cd.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($value['name']))) . "' AND c.parent_id = '" . (int) $parent_id . "'")->row;
      
      if (!empty($cat_exists['category_id'])) {
        return $cat_exists['category_id'];
      }
    }

    return false;
  }
  
  // Do the item exists? consider in database or updated during this import
  protected function itemExists($type, &$config, &$data, $key = NULL, $multiple = false) {
    $field = $config['item_identifier'];
    $containsMode = !empty($config['identifier_mode']) ? true : false;
    
    $values = array();
    $desc_field = '';
    
    if (in_array($field, array('name', 'title', 'description')) && !in_array($type, array('manufacturer', 'order'))) {
      if (isset($data[$type . '_description'])) {
        foreach ($data[$type . '_description'] as $lang) {
          if (!empty($lang[$field])) {
            if (is_null($key)) {
              $values[] = $lang[$field];
            } else {
              $values[] = $lang[$field][$key];
            }
          }
        }
      } else if (isset($data[$field])) {
        $values[] = $data[$field];
      }
      
      $desc_field = '_description';
    } else {
      if (!empty($data[$field])) {
        if (is_null($key)) {
          $values[] = $data[$field];
        } else {
          $values[] = $data[$field][$key];
        }
      }
    }
    
    if (empty($values)) {
      return false;
    }
    
    foreach ((array) $values as $value) {
      if (is_array($value)) {
        if (isset($value[0])) {
          $value = $value[0];
        } else {
          continue;
        }
      }
      
      //if ($this->simulation && !empty($this->session->data['obui_identifiers']) && in_array($value, $this->session->data['obui_identifiers'])) {
      if ($this->simulation && $this->identifierExists($value)) {
        return $value;
      }
      
      if ($containsMode) {
        $query = $this->db->query("SELECT DISTINCT `".$this->db->escape($type)."_id` FROM `" . DB_PREFIX . $this->db->escape($type) . $desc_field . "` WHERE `" . $this->db->escape($field) . "` LIKE '%" . $this->db->escape(trim($this->request->clean($value))) . "%'")->rows;
        
        // allow multiple match
        if ($config['import_type'] == 'product_update') {
          if (count($query) > 1) {
            $idArray = array();
            
            foreach ($query as $row) {
              if (!empty($row[$type.'_id'])) {
                $idArray[] = $row[$type.'_id'];
              }
            }
            
            return $idArray;
          } else {
            if (isset($query[0])) {
              $query = $query[0];
            }
          }
        } else {
          if (isset($query[0])) {
            $query = $query[0];
          }
        }
      } else {
        if ($multiple) {
          $query = $this->db->query("SELECT DISTINCT `".$this->db->escape($type)."_id` FROM `" . DB_PREFIX . $this->db->escape($type) . $desc_field . "` WHERE `" . $this->db->escape($field) . "` = '" . $this->db->escape(trim($this->request->clean($value))) . "'")->rows;
          
          $idArray = array();
          
          foreach ($query as $row) {
            $idArray[] = $row[$type.'_id'];
          }
          
          return $idArray;
        } else {
          if (is_array($value)) {
            $this->tool->log(array(
              'row' => $this->session->data['obui_current_line'],
              'status' => 'skipped',
              'title' => $this->language->get('text_simu_error'),
              'msg' =>  'The identifier is an array, make sure it is a string value',
            ));
          } else {
            $query = $this->db->query("SELECT DISTINCT `".$this->db->escape($type)."_id` FROM `" . DB_PREFIX . $this->db->escape($type) . $desc_field . "` WHERE `" . $this->db->escape($field) . "` = '" . $this->db->escape(trim($this->request->clean($value))) . "'")->row;
          }
        }
      }
      
      if (!empty($query[$type.'_id'])) {
        return $query[$type.'_id'];
      }
    }

    return false;
	}
  
   // Do the item has been already processed during this import?
  protected function itemHasProcessed($type, $field, &$data, $key = NULL) {
    $values = array();
    $desc_field = '';
    
    if (in_array($field, array('name', 'title', 'description')) && !in_array($type, array('manufacturer', 'order'))) {
      foreach ($data[$type . '_description'] as $lang) {
        if (!empty($lang[$field])) {
          if (is_null($key)) {
            $values[] = $lang[$field];
          } else {
            $values[] = $lang[$field][$key];
          }
        }
      }
      
      $desc_field = '_description';
    } else {
      if (!empty($data[$field])) {
        if (is_null($key)) {
          $values[] = $data[$field];
        } else {
          $values[] = $data[$field][$key];
        }
      }
    }
    
    if (empty($values)) {
      return false;
    }
    
    foreach ((array) $values as $value) {
      //if (!empty($this->session->data['obui_identifiers']) && in_array($value, $this->session->data['obui_identifiers'])) {
      if ($this->identifierExists($value)) {
        return $value;
      }
    }

    return false;
	}
  
  protected function addProcessedIdentifier($value) {
    if (is_array($this->processedIdArray)) {
      // file mode - cannot use session for that
      if (!in_array($value, $this->processedIdArray)) {
        $this->processedIdArray[] = $value;
        file_put_contents($this->processedIdFile, $value."\n", FILE_APPEND | LOCK_EX);
      }
    } else {
      // data verification mode (10 rows)
      $this->session->data['obui_identifiers'][] = $value;
    }
  }
  
  protected function identifierExists($value) {
    if (!empty($this->processedIdArray)) {
      if (in_array($value, $this->processedIdArray)) {
        return true;
      }
    } else if (!empty($this->session->data['obui_identifiers']) && in_array($value, $this->session->data['obui_identifiers'])) {
      return true;
    }
    
    return false;
  }
  
  protected function loadIdentifierFile() {
    if (file_exists($this->processedIdFile)) {
      $this->processedIdArray = file($this->processedIdFile, FILE_IGNORE_NEW_LINES);
    } else {
      $this->processedIdArray = array();
    }
  }
  
  protected function deleteIdentifierFile() {
    if (file_exists($this->processedIdFile)) {
      unlink($this->processedIdFile);
    }
  }
  
  protected function walk_recursive_remove(array $array) {
    foreach ($array as $k => $v) {
      //if (in_array($k, array('discountByCustomerGroup'))) continue; // values to not filter
      
      if (is_array($v)) {
        $array[$k] = self::walk_recursive_remove($v);
      } else if ($v === '') {
        unset($array[$k]);
      }
    }
    
    return array_filter($array, array($this, 'filterEmptyArrays'));
  }
  
  protected function filterEmptyArrays($val) {
    return is_numeric($val) || (is_array($val) && !empty($val)) || !empty($val);
  }
  
  protected function array_walk_populate(&$val, &$key, $line) {
    if ($val !== '') {
      if (isset($line[$val])) {
        if (is_string($line[$val]) && !empty($this->xfn_multiple_separator[$val])) {
          $val = explode($this->xfn_multiple_separator[$val], htmlspecialchars_decode(trim($line[$val])));
        } else if (is_string($line[$val])) {
          $val = htmlspecialchars_decode(trim($line[$val]));
        } else if (is_float($line[$val]) || is_int($line[$val])) {
          $val = $line[$val];
        } else if (is_array($line[$val]) || is_bool($line[$val])) {
          $val = $line[$val];
        }
      // get a path in xml mode
      } else if (strpos($val, '/')) {
        $arrItems = explode('/', $val);
        //$arrItems = explode('/', str_replace('[0]/', '/0/', $val));
        //$initKey = array_shift($arrItems);
      if (is_array($line)) {
        $val = $line;
        
          foreach ($arrItems as $arrKey => $arrItem) {
            array_shift($arrItems);
            
            if (!empty($skipNextIteration)) {
              $skipNextIteration = false;
              continue;
            }
            
            if (!is_null($arrItem) && is_array($val) && array_key_exists($arrItem, $val)) {
              $val = $val[$arrItem];
            } else if (!is_null($arrItem) && is_array($val) && $arrItem == '*') {
              $arrayVal = array();
              
              foreach($val as $val) {
                if (count($arrItems) === 0) {
                  $arrayVal = $val;
                } else {
                  foreach ($arrItems as $k => $arrItem) {
                    if (!is_null($arrItem) && is_array($val) && $arrItem == '*') {
                      $arrayVal = $arrayVal + $val;
                    } else if (!is_null($arrItem) && is_scalar($val) && $arrItem == '*') {
                      $arrayVal = $arrayVal + array($val);
                    } else if (!is_null($arrItem) && is_array($val) && array_key_exists($arrItem, $val)) {
                      // if last item then save value else change val for loop
                      if ($k+1 == count($arrItems)) {
                        $arrayVal[] = $val[$arrItem];
                      } else {
                        $val = $val[$arrItem];
                      }
                    } else if (isset($val[0]) && is_array($val[0]) && !is_null($arrItem) && array_key_exists($arrItem, $val[0])) {
                      // if last item then save value else change val for loop
                      if ($k+1 == count($arrItems)) {
                        $arrayVal[] = $val[0][$arrItem];
                      } else {
                        $val = $val[0][$arrItem];
                      }
                      
                    }
                  }
                }
              }
              
              $val = $arrayVal;
              return;
              /* old method
              foreach ($val as $v) {
                if (isset($arrItems[$arrKey+1])) {
                  $subArrItem = $arrItems[$arrKey+1];
                } else {
                  continue;
                }
                
                if (isset($v[$subArrItem])) {
                  $arrayVal[] = $v[$subArrItem];
                }
              }
              
              $val = $arrayVal;
              
              $skipNextIteration = true;
              */
            } else if (isset($val[0]) && is_array($val[0]) && !is_null($arrItem) && array_key_exists($arrItem, $val[0])) {
              $val = $val[0][$arrItem];
            } else {
              $val = '';
            }
          }
        } else {
          $val = '';
        }
      } else {
        $val = '';
      }
    }
  }
  
  protected function array_filter_column(&$val) {
    return ($val !== '');
  }
  
  public function loadFile(&$file, $filetype = '', $currentSheet = 0, &$config) {
    $extension = !empty($filetype) ? $filetype : strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($extension, array('csv_', 'sql'))) {
      $fh = fopen($file, 'r');
    } else if ($extension == 'xml') {
      $fh = new XMLReader;
      if (!empty($config['encoding']) && $config['encoding'] != 'pass') {
        $fh->open($file, $config['encoding']);
      } else {
        $fh->open($file);
      }
      
      // XMLIterator method
      $nodeName = html_entity_decode($config['xml_node'], ENT_QUOTES, 'UTF-8');
      
      if (substr($nodeName, 0, 1) !== '>' && (!empty($config['xml_proc']) && $config['xml_proc'] == 'xri')) {
        if (substr($nodeName, 0, 1) != '/') {
          $nodeName = '//'.$nodeName;
        }
        
        require_once DIR_SYSTEM.'library/gkdXmlReaderIterators.php';
        
        $xmlIterator = new XMLElementIterator($fh);
        $fh = new XMLElementXpathFilter($xmlIterator, $nodeName);
      }
    } else if (in_array($extension, array('csv', 'txt', 'tsv', 'ods', 'xlsx'))) {
      // Spout 3
      if (version_compare(phpversion(), '7.1', '>')) {
        require_once DIR_SYSTEM.'library/Spout3/Autoloader/autoload.php';
        
        libxml_disable_entity_loader(false);
      
        if ($extension == 'xlsx') {
          $fh = ReaderEntityFactory::createXLSXReader();
        } else if ($extension == 'ods') {
          $fh = ReaderEntityFactory::createODSReader();
        } else if (in_array($extension, array('csv', 'txt', 'tsv'))) {
          $fh = ReaderEntityFactory::createCSVReader();
          
          $separator = !empty($config['csv_separator']) ? $config['csv_separator'] : ',';
          $enclosure = !empty($config['csv_enclosure']) ? $config['csv_enclosure'] : '"';
          
          if ($separator == 'tab' || $extension == 'tsv') {
            $separator = "\t";
          }
        
          $fh->setFieldDelimiter($separator);
          $fh->setFieldEnclosure($enclosure);
          //$reader->setEndOfLineCharacter("\r");
        }
      
      // Spout 2
      } else {
        require_once DIR_SYSTEM.'library/Spout/Autoloader/autoload.php';
        
        libxml_disable_entity_loader(false);
      
        if ($extension == 'xlsx') {
          $fh = ReaderFactory::create(Type::XLSX);
        } else if ($extension == 'ods') {
          $fh = ReaderFactory::create(Type::ODS);
        } else if (in_array($extension, array('csv', 'txt', 'tsv'))) {
          $fh = ReaderFactory::create(Type::CSV);
          
          $separator = !empty($config['csv_separator']) ? $config['csv_separator'] : ',';
          $enclosure = !empty($config['csv_enclosure']) ? $config['csv_enclosure'] : '"';
          
          if ($separator == 'tab' || $extension == 'tsv') {
            $separator = "\t";
          }
        
          $fh->setFieldDelimiter($separator);
          $fh->setFieldEnclosure($enclosure);
          //$reader->setEndOfLineCharacter("\r");
        }
      }
      
      $fh->setShouldFormatDates(true);
      
      if (!empty($config['encoding']) && $config['encoding'] != 'pass') {
        $fh->setEncoding($config['encoding']);
      }
      
      $fh->open($file);
      foreach ($fh->getSheetIterator() as $sheet) {
        if ($sheet->getIndex() === (int) $currentSheet) {
          break;
        }
      }
      
      $this->fileHandle = $fh;
      
      $fh = $sheet->getRowIterator();
    } else if ($extension == 'json') {
      require_once DIR_SYSTEM.'library/JsonMachine/Loader.php';
      
      $fh = \JsonMachine\JsonMachine::fromFile($file);
    } else if ($extension == 'xls') {
      // PHPExcel
      require_once(DIR_SYSTEM.'library/PHPExcel/PHPExcel.php');
      $fh = PHPExcel_IOFactory::load($file);
      //$fh->getSheet($currentSheet);
    }
    
    return $fh;
  }
  
  public function initFilePosition(&$file, &$config) {
    if (!isset($this->session->data['obui_current_line'])) {
      $this->session->data['obui_current_line'] = 0;
    }
    
    if (in_array($this->filetype, array('csv_', 'sql'))) {
      if (!empty($this->session->data['obui_last_position'])) {
        fseek($file, $this->session->data['obui_last_position']);
      } else if (!empty($config['last_position'])) {
        fseek($file, $config['last_position']);
      } else if (!empty($config['row_start'])) {
        if (!isset($this->session->data['obui_current_line'])) {
          $this->session->data['obui_current_line'] = 0;
        }
        
        while ($this->session->data['obui_current_line'] < $config['row_start'] -1) {
          if (!$this->getNextRow($file, $config)) break;
        }
        
        if (!empty($config['csv_header'])) {
          $this->session->data['obui_processed']['processed'] = $this->session->data['obui_current_line']-1;
        } else {
          $this->session->data['obui_processed']['processed'] = $this->session->data['obui_current_line'];
        }
      } else {
        return !empty($config['csv_header']);
      }
    
      return false;
    } else if ($this->filetype == 'xml') {
      libxml_use_internal_errors(true);
      
      $nodeName = html_entity_decode($config['xml_node'], ENT_QUOTES, 'UTF-8');
      
      if (!empty($this->session->data['obui_current_line'])) {
        $i = 1;
        
        // XMLIterator method
        if (substr($nodeName, 0, 1) !== '>' && (!empty($config['xml_proc']) && $config['xml_proc'] == 'xri')) {
          while ($i <= $this->session->data['obui_current_line']) {
            $i++;
            $file->next();
          }
          /*
          foreach ($file as $xmlRow) {
            if ($i++ >= $this->session->data['obui_current_line']) {
              break; 
            }
          }
          */
        } else {
          if (substr($nodeName, 0, 1) == '>') {
            $nodeName = substr($nodeName, 1);
          }
          
          // search for node
          while ($file->read() && $file->name !== $nodeName);
          
          // and forward to current
          while ($file->name === $nodeName && $i <= $this->session->data['obui_current_line']) {
            $i++;
            $file->next($nodeName);
          }
        }
      } else if (!empty($config['row_start'])) {
        if (!isset($this->session->data['obui_current_line'])) {
          $this->session->data['obui_current_line'] = 0;
        }
        
        // XMLIterator method
        if (substr($nodeName, 0, 1) !== '>' && (!empty($config['xml_proc']) && $config['xml_proc'] == 'xri')) {
          while ($this->session->data['obui_current_line'] < $config['row_start']-1) {
            if (!$this->getNextRow($file, $config)) break;
          }
        } else {
          if (substr($nodeName, 0, 1) == '>') {
            $nodeName = substr($nodeName, 1);
          }
          
          while ($file->read() && $file->name !== $nodeName);
          
          while ($this->session->data['obui_current_line'] < $config['row_start']-1) {
            if (!$this->getNextRow($file, $config)) break;
          }
        }
        
        $this->session->data['obui_processed']['processed'] = $this->session->data['obui_current_line'];
      } else {
        // XMLIterator method
        if (substr($nodeName, 0, 1) !== '>' && (!empty($config['xml_proc']) && $config['xml_proc'] == 'xri')) {
        } else {
          if (substr($nodeName, 0, 1) == '>') {
            $nodeName = substr($nodeName, 1);
          }
          
          while ($file->read() && $file->name !== $nodeName);
        }
      }
      
      return false;
    } else if (in_array($this->filetype, array('csv', 'txt', 'tsv', 'ods', 'xlsx'))) {
      // Spout
      if (!empty($this->session->data['obui_current_line'])) {
        $file->rewind();
        
        for ($i = 1; $i <= $this->session->data['obui_current_line']; $i++) {
          if ($file->valid()) {
            $file->next();
          } else {
            return false;
          }
        }
        /*foreach ($file as $i => $line) {if ($i > $this->session->data['obui_current_line']) {break;}}*/
      } else if (!empty($config['row_start'])) {
        if (!empty($config['csv_header']) && $config['row_start']-1 == 0) {
          $this->session->data['obui_current_line'] = 1;
        } else {
          $this->session->data['obui_current_line'] = $config['row_start']-1;
        }
        
        $file->rewind();
        
        for ($i = 1; $i <= $this->session->data['obui_current_line']; $i++) {
          if ($file->valid()) {
            $file->next();
          } else {
            return false;
          }
        }
        
        if (!empty($config['csv_header'])) {
          $this->session->data['obui_processed']['processed'] = $this->session->data['obui_current_line']-1;
        } else {
          $this->session->data['obui_processed']['processed'] = $this->session->data['obui_current_line'];
        }
        
        return false;
      } else {
        $file->rewind();
        return !empty($config['csv_header']);
      }
      
      return false;
    } else if ($this->filetype == 'json') {
      /*
      foreach ($file as $json) {
          if (++$i >= $this->session->data['obui_current_line']+2) {
            break;
          }
        }
      */
    } else if ($this->filetype == 'xls') {
      // PHPExcel
      if (empty($this->session->data['obui_current_line'])) {
        return !empty($config['csv_header']);
      }
    }
  }
  
  public function getNextRow(&$file, &$config) {
    $this->session->data['obui_current_line']++;
    
    if ($this->filetype == 'csv_') {
      if (!feof($file) && $line = fgets($file)) {
        $this->session->data['obui_last_position'] = ftell($file);
        
        if (!empty($this->encoding)) {
          $line = mb_convert_encoding($line, 'UTF-8', $this->encoding);
        }
        
        if (!trim($line)) {
          $this->session->data['obui_processed']['processed']++;
          return false;
        }
        
        return str_getcsv($line, $this->csv_separator);
      } else {
        return false;
      }
    } else if ($this->filetype == 'sql') {
      if (!feof($file) && $line = fgets($file)) {
        $this->session->data['obui_last_position'] = ftell($file);
        
        if (!trim($line)) {
          //$this->session->data['obui_processed']['processed']++;
          return '';
        }
        
        return $line;
      } else {
        return false;
      }
    } else if ($this->filetype == 'xml') {
      $nodeName = html_entity_decode($config['xml_node'], ENT_QUOTES, 'UTF-8');
      
      // XMLIterator method
      if (substr($nodeName, 0, 1) !== '>' && (!empty($config['xml_proc']) && $config['xml_proc'] == 'xri')) {
        $file->next();
        
        $rawXml = $file->readOuterXML();
        
        if (!$rawXml) {
          return false;
        }
        
        if (!empty($config['import_transformer'])) {
          list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
          
          $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
          $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
          
          if (substr($transformMethod, 0, 4) == 'row_') {
            $this->load->model('gkd_import/transformer/'.$transformClass);
            $rawXml = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($rawXml);
          }
        }
        
        $node = new SimpleXMLElement($rawXml);
        
        return $this->XML2Array($node) + $this->getAllXpath((isset($config['extra_xpath']) ? $config['extra_xpath'] : ''), $node);
      } else {
        if (substr($nodeName, 0, 1) == '>') {
          $nodeName = substr($nodeName, 1);
        }
        
        if ($file->name === $nodeName) {
          $rawXml = $file->readOuterXML();
          
          if (!empty($config['import_transformer'])) {
            list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
            
            $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
            $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
            
            if (substr($transformMethod, 0, 4) == 'row_') {
              $this->load->model('gkd_import/transformer/'.$transformClass);
              $rawXml = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($rawXml);
            }
          }
          
          $node = new SimpleXMLElement($rawXml); 

          $file->next($nodeName);
          
          return $this->XML2Array($node) + $this->getAllXpath((isset($config['extra_xpath']) ? $config['extra_xpath'] : ''), $node);
        } else {
          return false;
        }
      }
    } else if (in_array($this->filetype, array('csv', 'txt', 'tsv', 'ods', 'xlsx'))) {
      // Spout
      if ($file->valid()) {
        $values = $file->current();
        if (version_compare(phpversion(), '7.1', '>')) {
          // Spout 3
          $values = $values->toArray();
        }
          
        if (!empty($config['import_transformer'])) {
          list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
          
          $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
          $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
          
          if (substr($transformMethod, 0, 4) == 'row_') {
            $this->load->model('gkd_import/transformer/'.$transformClass);
            $values = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($values);
          }
        }
        
        $file->next();
      } else {
        return false;
      }
      
      return $values;
    } else if ($this->filetype == 'json') {
      $arrayFlatMaxLevels = 1;
      
      $i=0;
      $jsonArray = \JsonMachine\JsonMachine::fromFile($config['import_file']);
       
      $dataFoundInGivenPath = true;
      
      if (!empty($config['import_api_field'])) {
        $jsonPath = explode('/', $config['import_api_field']);
        
        $dataFoundInGivenPath = false;
        
        foreach ($jsonArray as $jsonKey => $jsonVal) {
          // level 1
          if ($jsonKey == $jsonPath[0] || $jsonPath[0] == '*') {
            // level 2
            if (isset($jsonPath[1])) {
              foreach ($jsonVal as $jsonKey1 => $jsonVal1) {
                if ($jsonKey1 == $jsonPath[1] || $jsonPath[1] == '*') {
                  // level 3
                  if (isset($jsonPath[2])) {
                    foreach ($jsonVal1 as $jsonKey2 => $jsonVal2) {
                      if ($jsonKey2 == $jsonPath[2] || $jsonPath[2] == '*') {
                        // level 4
                        if (isset($jsonPath[3])) {
                          foreach ($jsonVal2 as $jsonKey3 => $jsonVal3) {
                            if ($jsonKey3 == $jsonPath[3] || $jsonPath[3] == '*') {
                              // level 5
                              if (isset($jsonPath[4])) {
                                foreach ($jsonVal3 as $jsonKey4 => $jsonVal4) {
                                  if ($jsonKey4 == $jsonPath[4] || $jsonPath[4] == '*') {
                                    if (++$i < $this->session->data['obui_current_line']) { continue; }

                                    if (!empty($config['import_transformer'])) {
                                      $currentData = array();
                                      
                                      $currentData = $this->model_gkd_import_tool->arrayFlat($jsonVal4, '', $arrayFlatMaxLevels);
                                      
                                      list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
                                      
                                      $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
                                      $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
                                      
                                      if (substr($transformMethod, 0, 4) == 'row_') {
                                        $this->load->model('gkd_import/transformer/'.$transformClass);
                                        $currentData = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($currentData);
                                      }
                                      
                                      return $currentData;
                                    } else {
                                      return $this->model_gkd_import_tool->arrayFlat($jsonVal4, '', $arrayFlatMaxLevels);
                                    }
                                  }
                                }
                              } else {
                                if (++$i < $this->session->data['obui_current_line']) { continue; }

                                if (!empty($config['import_transformer'])) {
                                  $currentData = array();
                                  
                                  $currentData = $this->model_gkd_import_tool->arrayFlat($jsonVal3, '', $arrayFlatMaxLevels);
                                  
                                  list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
                                  
                                  $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
                                  $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
                                  
                                  if (substr($transformMethod, 0, 4) == 'row_') {
                                    $this->load->model('gkd_import/transformer/'.$transformClass);
                                    $currentData = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($currentData);
                                  }
                                  
                                  return $currentData;
                                } else {
                                  return $this->model_gkd_import_tool->arrayFlat($jsonVal3, '', $arrayFlatMaxLevels);
                                }
                              }
                            }
                          }
                        } else {
                          if (++$i < $this->session->data['obui_current_line']) { continue; }

                          if (!empty($config['import_transformer'])) {
                            $currentData = array();
                            
                            $currentData = $this->model_gkd_import_tool->arrayFlat($jsonVal2, '', $arrayFlatMaxLevels);
                            
                            list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
                            
                            $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
                            $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
                            
                            if (substr($transformMethod, 0, 4) == 'row_') {
                              $this->load->model('gkd_import/transformer/'.$transformClass);
                              $currentData = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($currentData);
                            }
                            
                            return $currentData;
                          } else {
                            return $this->model_gkd_import_tool->arrayFlat($jsonVal2, '', $arrayFlatMaxLevels);
                          }
                        }
                      }
                    }
                  } else {
                    if (++$i < $this->session->data['obui_current_line']) { continue; }
                    
                    if (!empty($config['import_transformer'])) {
                      $currentData = array();
                      
                      $currentData = $this->model_gkd_import_tool->arrayFlat($jsonVal1, '', $arrayFlatMaxLevels);
                      
                      list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
                      
                      $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
                      $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
                      
                      if (substr($transformMethod, 0, 4) == 'row_') {
                        $this->load->model('gkd_import/transformer/'.$transformClass);
                        $currentData = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($currentData);
                      }
                      
                      return $currentData;
                    } else {
                      return $this->model_gkd_import_tool->arrayFlat($jsonVal1, '', $arrayFlatMaxLevels);
                    }
                  }
                }
              }
            } else {
              if (++$i < $this->session->data['obui_current_line']) { continue; }
              
              if (!empty($config['import_transformer'])) {
                $currentData = array();
                
                $currentData = $this->model_gkd_import_tool->arrayFlat($jsonVal, '', $arrayFlatMaxLevels);
                
                list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
                
                $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
                $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
                
                if (substr($transformMethod, 0, 4) == 'row_') {
                  $this->load->model('gkd_import/transformer/'.$transformClass);
                  $currentData = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($currentData);
                }
                
                return $currentData;
              } else {
                return $this->model_gkd_import_tool->arrayFlat($jsonVal, '', $arrayFlatMaxLevels);
              }
            }
          }
        }
      } else {
        foreach ($jsonArray as $json) {
          if (++$i < $this->session->data['obui_current_line']) {
            continue;
          }
          
          if (!empty($config['import_transformer'])) {
            $currentData = array();
            
            $currentData = $this->model_gkd_import_tool->arrayFlat($json, '', $arrayFlatMaxLevels);
            
            list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
            
            $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
            $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
            
            if (substr($transformMethod, 0, 4) == 'row_') {
              $this->load->model('gkd_import/transformer/'.$transformClass);
              $currentData = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($currentData);
            }
            
            return $currentData;
          } else {
            return $this->model_gkd_import_tool->arrayFlat($json, '', $arrayFlatMaxLevels);
          }
        }
      }
      
      return false;
       /*
        foreach ($file as $json) {
          if (++$i < $this->session->data['obui_current_line']) {
            continue;
          }
          
          return $this->model_gkd_import_tool->arrayFlat($json);
        }
        
        return false;
        */
    } else if ($this->filetype == 'xls') {
      // PHPExcel
      //$sheet = $file->getSheet(0);
      $sheet = $file->getSheet(isset($config['sheet']) ? $config['sheet'] : 0);
      $highestRow = $sheet->getHighestRow();
      $highestColumn = $sheet->getHighestColumn();
      $row = $this->session->data['obui_current_line'];
      
      if ($row > $highestRow) {
        return false;
      }
      
      $resrow = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, false, false);
      
      $values = $resrow[0];
      
      return $values;
    }
  }
  
  public function getTotalRows($file, $has_header = false, $xml_node, $filetype = '', $currentSheet = 0, $config, $forceSpout = false) {
    if (self::benchmark) {
      $start_time = time();
    }
    
    $extension = !empty($filetype) ? $filetype : strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    $i = 0;
    
    if (in_array($extension, array('csv_', 'sql'))) {
      $fh = fopen($file, 'r');
      while (fgets($fh) !== false) $i++;
      
      fclose($fh);
    } else if ($extension == 'xml') {
      libxml_use_internal_errors(true);
      
      $xml = new XMLReader;
      $xml->open($file);
      
      $nodeName = html_entity_decode($config['xml_node'], ENT_QUOTES, 'UTF-8');
      
      // XMLIterator method
      if (substr($nodeName, 0, 1) !== '>' && (!empty($config['xml_proc']) && $config['xml_proc'] == 'xri')) {
        // convert node name to xpath
        if (substr($nodeName, 0, 1) != '/') {
          $xpath = '//'.$nodeName;
        }
        
        require_once DIR_SYSTEM.'library/gkdXmlReaderIterators.php';
        
        $xmlIterator = new XMLElementIterator($xml);
        $xmlRows = new XMLElementXpathFilter($xmlIterator, $xpath);
        
        foreach ($xmlRows as $xmlRow) {
          $i++;
        }
      } else {
        // if not working with ElementIterator try with direct detection
        if (substr($nodeName, 0, 1) == '>') {
          $nodeName = substr($nodeName, 1);
        }
        
        $xml->open($file, null, LIBXML_PARSEHUGE);
        
        // find the node name
        while ($xml->read() && $xml->name !== $nodeName);

        while ($xml->name === $nodeName) {
          $i++;
          $xml->next($nodeName);
        }
      }
    } else if (in_array($extension, array('csv', 'txt', 'tsv', 'ods', 'xlsx_')) || $forceSpout) { // use PHPExcel to count xlsx files as it is much faster
      // Spout
      if (version_compare(phpversion(), '7.1', '>')) {
        require_once DIR_SYSTEM.'library/Spout3/Autoloader/autoload.php';
        libxml_disable_entity_loader(false);
        
        if ($extension == 'xlsx') {
          $reader = ReaderEntityFactory::createXLSXReader();
        } else if ($extension == 'ods') {
          $reader = ReaderEntityFactory::createODSReader();
        } else if (in_array($extension, array('csv', 'txt', 'tsv'))) {
          $reader = ReaderEntityFactory::createCSVReader();
          
          $separator = !empty($config['csv_separator']) ? $config['csv_separator'] : ',';
          $enclosure = !empty($config['csv_enclosure']) ? $config['csv_enclosure'] : '"';
          
          if ($separator == 'tab' || $extension == 'tsv') {
            $separator = "\t";
          }
        
          $reader->setFieldDelimiter($separator);
          $reader->setFieldEnclosure($enclosure);
          //$reader->setEndOfLineCharacter("\r");
        }
      } else {
        require_once DIR_SYSTEM.'library/Spout/Autoloader/autoload.php';
        libxml_disable_entity_loader(false);
        
        if ($extension == 'xlsx') {
          $reader = ReaderFactory::create(Type::XLSX);
        } else if ($extension == 'ods') {
          $reader = ReaderFactory::create(Type::ODS);
        } else if (in_array($extension, array('csv', 'txt', 'tsv'))) {
          $reader = ReaderFactory::create(Type::CSV);
          
          $separator = !empty($config['csv_separator']) ? $config['csv_separator'] : ',';
          $enclosure = !empty($config['csv_enclosure']) ? $config['csv_enclosure'] : '"';
          
          if ($separator == 'tab' || $extension == 'tsv') {
            $separator = "\t";
          }
        
          $reader->setFieldDelimiter($separator);
          $reader->setFieldEnclosure($enclosure);
          //$reader->setEndOfLineCharacter("\r");
        }
      }
      
      $reader->open($file);

      foreach ($reader->getSheetIterator() as $sheet) {
        if ($sheet->getIndex() === (int) $currentSheet) {
          $i = iterator_count($sheet->getRowIterator());
        }
      }
      
      $reader->close();
    } else if ($extension == 'json') {
      require_once DIR_SYSTEM.'library/JsonMachine/Loader.php';
      
      $fh = \JsonMachine\JsonMachine::fromFile($file);
      
      $i = 0;
      
      if (!empty($config['import_api_field'])) {
        $jsonPath = explode('/', $config['import_api_field']);
        
        $dataFoundInGivenPath = false;
        
        foreach ($fh as $jsonKey => $jsonVal) {
          // level 1
          if ($jsonKey == $jsonPath[0] || $jsonPath[0] == '*') {
            // level 2
            if (isset($jsonPath[1])) {
              foreach ($jsonVal as $jsonKey1 => $jsonVal1) {
                if ($jsonKey1 == $jsonPath[1] || $jsonPath[1] == '*') {
                  // level 3
                  if (isset($jsonPath[2])) {
                    foreach ($jsonVal1 as $jsonKey2 => $jsonVal2) {
                      if ($jsonKey2 == $jsonPath[2] || $jsonPath[2] == '*') {
                        // level 4
                        if (isset($jsonPath[3])) {
                          foreach ($jsonVal2 as $jsonKey3 => $jsonVal3) {
                            if ($jsonKey3 == $jsonPath[3] || $jsonPath[3] == '*') {
                              $i++;
                            }
                          }
                        } else {
                          $i++;
                        }
                      }
                    }
                  } else {
                    $i++;
                  }
                }
              }
            } else {
              $i++;
            }
          }
        }
      } else {
        foreach ($fh as $d) {
          $i++;
        }
      }
      /*
      foreach ($fh as $d) {
        $i++;
      }
      */
    } else if ($extension == 'xls' || $extension == 'xlsx') {
      // PHPExcel
      require_once(DIR_SYSTEM.'library/PHPExcel/PHPExcel.php');
      
      if ($extension == 'xlsx') {
        $objPHPExcel = PHPExcel_IOFactory::createReader('Excel2007');
      } else {
        $objPHPExcel = PHPExcel_IOFactory::createReader("Excel5"); 
      }
      
      $worksheetData = $objPHPExcel->listWorksheetInfo($file);
      
      if (isset($worksheetData[$currentSheet]['totalRows'])) {
        $i = $worksheetData[$currentSheet]['totalRows'];
        $i = -1;
      } else {
        // failed to get row count from PHPExcel, try with Spout
        //$i = $this->getTotalRows($file, $has_header, $xml_node, $filetype, $currentSheet, $config, true);
        $i = -1;
      }
    }
    
    if (self::benchmark) {
      echo 'getTotalRows(): ' . (time() - $start_time . "<br/>");
    }
    
    return $has_header && is_int($i) ? $i-1 : $i;
  }
  
  public function getAllXpath($xpathes, $xml) {
    $array = array();
    
    foreach (explode("\n", $xpathes) as $xpath) {
      $array += $this->Xpath2Array(trim($xpath), $xml);
    }
    
    return $array;
  }
  
  public function Xpath2Array($xpath, $xml) {
    $array = array();
    
    if (!$xpath) return $array;
    
    $qry = @$xml->xpath($xpath);
    
    if ($qry === false) {
      $this->session->data['obui_warning'] = 'The following xpath query is invalid: <b>'.$xpath.'</b>';
      return array();
    }
    
    foreach ($qry as $k => $value) {
      $res = $this->XML2Array($value);
      
      foreach ($res as $key => $value) {
        if (isset($array['['.$xpath.']'][$k][$key])) {
          $array['['.$xpath.']'][$k][$key] = (array) $array['['.$xpath.']'][$k][$key];
          $array['['.$xpath.']'][$k][$key][] = $value;
        } else {
          $array['['.$xpath.']'][$k][$key] = $value;
        }
      }
      
    }
    
    return $array;
  }
  
  public function XML2Array($xml, $level = 0, $onlyAttr = false) {
    $array = array();
    $level++;
    $m = 0;
    
    // get current not value and params if any
    /*
    if ($level == 1 && is_array($xml)) {
      foreach ($xml  as $key => $value) {
        $array = $this->XML2Array($value, 0);
      }
    } else 
    */
    //if ($level == 1) {
    if (true) {
      if ($level == 1) {
        $key = $xml->getName();
      } else {
        $key = '';
      }
      
      if (trim($xml->__toString())) {
        $array[$key] = $xml->__toString();
      }
      
      foreach ($xml->attributes() as $at_key => $at_val) {
        if (isset($array[$key.'@'.$at_key])) {
          $array[$key.'@'.$at_key] = (array) $array[$key.'@'.$at_key];
          $array[$key.'@'.$at_key][] = (string) $at_val;
        } else {
          $array[$key.'@'.$at_key] = (string) $at_val;
        }
      }
    }
    
    $namespaces = array('' => '');
    
    if (is_object($xml) && strpos(get_class($xml), 'SimpleXML') !== false) {
      foreach ($xml->getNamespaces(true) as $ns => $fns) {
        if ($ns) {
          $namespaces[$ns] = $ns.':';
        }
      }
    }
    
    foreach ($namespaces as $ns => $nsKey) {
      // if there is an NS then get it
      if ($ns) {
        $xml2 = $xml->children($ns, true);
      } else {
        $xml2 = $xml;
      }
      
      // get subnodes values and params
      foreach ($xml2 as $key => $value) {
        if (is_object($value) && strpos(get_class($value), 'SimpleXML') !== false) {
          if ($value->count()) {
              if (isset($array[$nsKey.$key][$m])) $m++;

              if (!isset($array[$nsKey.$key]) || (isset($array[$nsKey.$key]) && is_array($array[$nsKey.$key]))) {
                $array[$nsKey.$key][$m] = $this->XML2Array($value, $level);
              }
              
              if ($level < 2 && is_array($array[$nsKey.$key][$m])) {
                $i = 1;
                foreach ($array[$nsKey.$key][$m] as $sub_key => $sub_val) {
                  if (isset($array[$nsKey.$key.'/'.$m.'/'.$sub_key])) {
                    $array[$nsKey.$key.'/'.$m.'/'.$sub_key.'/'.$i++.''] = $sub_val;
                  } else {
                    $array[$nsKey.$key.'/'.$m.'/'.$sub_key] = $sub_val;
                  }
                  
                  if (is_array($sub_val) && isset($sub_val[0])) {
                    foreach ($sub_val as $subsub_key => $subsub_val) {
                      $array[$nsKey.$key.'/'.$m.'/'.$sub_key.'/'.$subsub_key.''] = $subsub_val;
                    }
                  }
                }
              }
          } else {
            if (isset($array[$nsKey.$key])) {
              // transform to array because of multiple elements
              if (!is_array($array[$nsKey.$key])) {
                $array[$nsKey.$key] = (array) $array[$nsKey.$key];
                $array[$nsKey.$key.'/0'] = $array[$nsKey.$key][0];
              }
              
              $array[$nsKey.$key.'/'.count($array[$nsKey.$key])] = (string) $value;
              $array[$nsKey.$key][] = (string) $value;
            } else {
              $array[$nsKey.$key] = (string) $value;
            }
          }
          foreach ($value->attributes() as $at_key => $at_val) {
            if (isset($array[$nsKey.$key.'@'.$at_key])) {
              $array[$nsKey.$key.'@'.$at_key] = (array) $array[$nsKey.$key.'@'.$at_key];
              $array[$nsKey.$key.'@'.$at_key][] = (string) $at_val;
            } else {
              $array[$nsKey.$key.'@'.$at_key] = (string) $at_val;
            }
          }
        } else {
          $array[$nsKey.$key] = $value;
        }
        
      }
    }
    
    return $array;
  }
  
  public function removeExtraFunc(&$config, $funcArray) {
    if (!empty($config['extra_func'])) {
      foreach ($config['extra_func'] as $k => $func) {
        if (in_array(key($func), $funcArray)) {
          unset($config['extra_func'][$k]);
        }
      }
    }
  }
  
  public function getFeedCategories() {
    $rows = array();
    $i = 0;
    
    $cat_field = ($this->request->post['import_type'] == 'category')  ? 'parent_id' : 'product_category';
    
    // set profile
    if (!empty($this->request->post['profile'])) {
      $profile = include DIR_APPLICATION . 'view/universal_import/profiles/'. str_replace(array('/','\\'), '', $this->request->post['import_type']) .'/' . str_replace(array('/','\\'), '', $this->request->post['profile']) . '.cfg';
    }
    
    $config = $this->request->post;
    
    // disable remote api function here
    $this->removeExtraFunc($config, array('remote_api'));
    
    $extension = !empty($this->request->post['import_filetype']) ? $this->request->post['import_filetype'] : strtolower(pathinfo($this->request->post['import_file'], PATHINFO_EXTENSION));
    
    if (!empty($this->session->data['univimport_temp_file'])) {
      $import_file = $this->session->data['univimport_temp_file'];
    } else if ($this->request->post['import_source'] == 'upload') {
      $import_file = DIR_CACHE.'universal_import/'.str_replace(array('../', '..\\'), '', $this->request->post['import_file']);
    } else if ($this->request->post['import_source'] == 'ftp') {
      $import_file = $this->request->post['import_ftp'].$this->request->post['import_file'];
    } else {
      $import_file = $this->request->post['import_file'];
    }
    
    if ($extension == 'csv_') {
      $separator = !empty($this->request->post['csv_separator']) ? $this->request->post['csv_separator'] : ',';
      
      if ($separator == 'tab' || $extension == 'tsv') {
        $separator = "\t";
      }
      
      $file = fopen($import_file, 'r');
      
      if ($file) {
        if (!empty($this->request->post['csv_header'])) {
          fgets($file);
        }
        
        while (!feof($file)) {
          if ($line = trim(fgets($file))) {
            $config = $this->request->post;
            
            // disable remote api function here
            $this->removeExtraFunc($config, array('remote_api'));
    
            $row = str_getcsv($line, $separator);
            
            $this->populate_fields($config, $row);

            foreach ((array) $config['columns'][$cat_field] as $key => $cat) {
              // add subcategories
              if (!empty($config['columns']['sub_product_category'][$key]) && is_string($cat)) {
                foreach ($config['columns']['sub_product_category'][$key] as $subcat) {
                  $cat .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
                }
              }
              
              if (is_scalar($cat) && !isset($rows[$cat])) {
                $rows[$cat] = isset($profile['col_binding'][md5($cat)]) ? $profile['col_binding'][md5($cat)] : '';
              }
            }

            $i++;
          }
        }

        fclose($file);
      } else {
        // error opening the file.
      }
    } else if ($extension == 'xml') {
      libxml_use_internal_errors(true);
      
      $xml = new XMLReader;
      $xml->open($import_file);

      //$doc = new DOMDocument;
      
      $rows = array();
      $i = 0;
      
      $nodeName = html_entity_decode($this->request->post['xml_node'], ENT_QUOTES, 'UTF-8');
      
      // XMLIterator method
      if (substr($nodeName, 0, 1) !== '>' && (!empty($this->request->post['xml_proc']) && $this->request->post['xml_proc'] == 'xri')) {
        // convert node name to xpath
        if (substr($nodeName, 0, 1) != '/') {
          $nodeName = '//'.$nodeName;
        }
        
        require_once DIR_SYSTEM.'library/gkdXmlReaderIterators.php';
        
        $xmlIterator = new XMLElementIterator($xml);
        $xmlRows = new XMLElementXpathFilter($xmlIterator, $nodeName);
        
        foreach ($xmlRows as $xmlRow) {
          // get data
          $rawXml = $xmlRow->readOuterXML();
          
          if (!empty($config['import_transformer'])) {
            list($transformClass, $transformMethod) = explode('/', $config['import_transformer']);
            
            $transformClass = preg_replace('/[^a-z0-9_]+/', '', $transformClass);
            $transformMethod = preg_replace('/[^a-z0-9_]+/', '', $transformMethod);
            
            if (substr($transformMethod, 0, 4) == 'row_') {
              $this->load->model('gkd_import/transformer/'.$transformClass);
              $rawXml = $this->{'model_gkd_import_transformer_'.$transformClass}->$transformMethod($rawXml);
            }
          }
          
          $node = new SimpleXMLElement($rawXml);

          $config = $this->request->post;
          
          // disable remote api function here
          $this->removeExtraFunc($config, array('remote_api'));
          
          $row = $this->model_tool_universal_import->XML2Array($node) + $this->model_tool_universal_import->getAllXpath((isset($config['extra_xpath']) ? $config['extra_xpath'] : ''), $node);
          
          $this->populate_fields($config, $row);
          
          foreach ((array) $config['columns'][$cat_field] as $key => $cat) {
            // add subcategories
            if (!empty($config['columns']['sub_product_category'][$key]) && is_string($cat)) {
              foreach ($config['columns']['sub_product_category'][$key] as $subcat) {
                $cat .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
              }
            }
              
            if (is_scalar($cat) && !isset($rows[$cat])) {
              $rows[$cat] = isset($profile['col_binding'][md5($cat)]) ? $profile['col_binding'][md5($cat)] : '';
            }
          }
          
          // go to next node
          $i++;
        }
      } else {
        if (substr($nodeName, 0, 1) == '>') {
          $nodeName = substr($nodeName, 1);
        }
        
        // find the node name
        while ($xml->read() && $xml->name !== $nodeName);

        // now that we're at the right depth, hop to the next <product/> until the end of the tree
        while ($xml->name === $nodeName) {
            $node = new SimpleXMLElement($xml->readOuterXML()); // other method to get data
            //$node = simplexml_import_dom($doc->importNode($xml->expand(), true));
            
            $config = $this->request->post;
            
            // disable remote api function here
            $this->removeExtraFunc($config, array('remote_api'));
            
            $row = $this->model_tool_universal_import->XML2Array($node) + $this->model_tool_universal_import->getAllXpath((isset($config['extra_xpath']) ? $config['extra_xpath'] : ''), $node);
            
            $this->populate_fields($config, $row);
            
            foreach ((array) $config['columns'][$cat_field] as $key => $cat) {
              // add subcategories
              if (!empty($config['columns']['sub_product_category'][$key]) && is_string($cat)) {
                foreach ($config['columns']['sub_product_category'][$key] as $subcat) {
                  $cat .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
                }
              }
                
              if (is_scalar($cat) && !isset($rows[$cat])) {
                $rows[$cat] = isset($profile['col_binding'][md5($cat)]) ? $profile['col_binding'][md5($cat)] : '';
              }
            }
            
            // go to next node
            $xml->next($nodeName);
            $i++;
        }
      }
    } else if (in_array($extension, array('csv', 'txt', 'tsv', 'ods', 'xlsx')) && version_compare(phpversion(), '7.1', '>')) { // Spout 3
      require_once DIR_SYSTEM.'library/Spout3/Autoloader/autoload.php';
      
      libxml_disable_entity_loader(false);
      
      if ($extension == 'xlsx') {
        $reader = ReaderEntityFactory::createXLSXReader();
      } else if ($extension == 'ods') {
        $reader = ReaderEntityFactory::createODSReader();
      } else if (in_array($extension, array('csv', 'txt', 'tsv'))) {
        $reader = ReaderEntityFactory::createCSVReader();
        
        $separator = !empty($this->request->post['csv_separator']) ? $this->request->post['csv_separator'] : ',';
        $enclosure = !empty($this->request->post['csv_enclosure']) ? $this->request->post['csv_enclosure'] : '"';
        
        if ($separator == 'tab' || $extension == 'tsv') {
          $separator = "\t";
        }
      
        $reader->setFieldDelimiter($separator);
        $reader->setFieldEnclosure($enclosure);
        //$reader->setEndOfLineCharacter("\r");
      }

      $reader->setShouldFormatDates(true);
      //$reader = ReaderFactory::create(Type::CSV); // for CSV files

      if (!empty($config['encoding']) && $config['encoding'] != 'pass') {
        $reader->setEncoding($config['encoding']);
      }
      
      $reader->open($import_file);

      foreach ($reader->getSheetIterator() as $sheet) {
        if ($sheet->getIndex() === 0) {
          foreach ($sheet->getRowIterator() as $i => $row) {
            if (!empty($this->request->post['csv_header']) && $i === 1) {
              continue;
            }
            
            $config = $this->request->post;
            
            // disable remote api function here
            $this->removeExtraFunc($config, array('remote_api'));
    
            $row = $row->toArray();
            
            $this->populate_fields($config, $row);
            
            foreach ((array) $config['columns'][$cat_field] as $key => $cat) {
              // add subcategories
              if (!empty($config['columns']['sub_product_category'][$key]) && is_string($cat)) {
                foreach ($config['columns']['sub_product_category'][$key] as $subcat) {
                  $cat .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
                }
              }
              
              if (is_scalar($cat) && !isset($rows[$cat])) {
                $rows[$cat] = isset($profile['col_binding'][md5($cat)]) ? $profile['col_binding'][md5($cat)] : '';
              }
            }
          }
        }
      }
      
      $reader->close();
    } else if (in_array($extension, array('csv', 'txt', 'tsv', 'ods', 'xlsx'))) { // Spout 2
      require_once DIR_SYSTEM.'library/Spout/Autoloader/autoload.php';
      
      libxml_disable_entity_loader(false);
      
      if ($extension == 'xlsx') {
        $reader = ReaderFactory::create(Type::XLSX);
      } else if ($extension == 'ods') {
        $reader = ReaderFactory::create(Type::ODS);
      } else if (in_array($extension, array('csv', 'txt', 'tsv'))) {
        $reader = ReaderFactory::create(Type::CSV);
        
        $separator = !empty($this->request->post['csv_separator']) ? $this->request->post['csv_separator'] : ',';
        $enclosure = !empty($this->request->post['csv_enclosure']) ? $this->request->post['csv_enclosure'] : '"';
        
        if ($separator == 'tab' || $extension == 'tsv') {
          $separator = "\t";
        }
      
        $reader->setFieldDelimiter($separator);
        $reader->setFieldEnclosure($enclosure);
        //$reader->setEndOfLineCharacter("\r");
      }

      $reader->setShouldFormatDates(true);
      //$reader = ReaderFactory::create(Type::CSV); // for CSV files

      if (!empty($config['encoding']) && $config['encoding'] != 'pass') {
        $reader->setEncoding($config['encoding']);
      }
      
      $reader->open($import_file);

      foreach ($reader->getSheetIterator() as $sheet) {
        if ($sheet->getIndex() === 0) {
          foreach ($sheet->getRowIterator() as $i => $row) {
            if (!empty($this->request->post['csv_header']) && $i === 1) {
              continue;
            }
            
            $config = $this->request->post;
            
            // disable remote api function here
            $this->removeExtraFunc($config, array('remote_api'));
    
            $this->populate_fields($config, $row);
            
            foreach ((array) $config['columns'][$cat_field] as $key => $cat) {
              // add subcategories
              if (!empty($config['columns']['sub_product_category'][$key]) && is_string($cat)) {
                foreach ($config['columns']['sub_product_category'][$key] as $subcat) {
                  $cat .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
                }
              }
              
              if (is_scalar($cat) && !isset($rows[$cat])) {
                $rows[$cat] = isset($profile['col_binding'][md5($cat)]) ? $profile['col_binding'][md5($cat)] : '';
              }
            }
          }
        }
      }
      
      $reader->close();
      
      
    } else if ($extension == 'json') {
      if (!class_exists('JsonMachine')) {
        require_once DIR_SYSTEM.'library/JsonMachine/Loader.php';
      }
      
      $jsonArray = \JsonMachine\JsonMachine::fromFile($import_file);

      $dataFoundInGivenPath = true;
      
      if (!empty($this->request->post['import_api_field'])) {
        $jsonPath = explode('/', $this->request->post['import_api_field']);
        
        $dataFoundInGivenPath = false;
        $rawData = array();
        foreach ($jsonArray as $jsonKey => $jsonVal) {
          // level 1
          if ($jsonKey == $jsonPath[0] || $jsonPath[0] == '*') {
            // level 2
            if (isset($jsonPath[1])) {
              foreach ($jsonVal as $jsonKey1 => $jsonVal1) {
                if ($jsonKey1 == $jsonPath[1] || $jsonPath[1] == '*') {
                  // level 3
                  if (isset($jsonPath[2])) {
                    foreach ($jsonVal1 as $jsonKey2 => $jsonVal2) {
                      if ($jsonKey2 == $jsonPath[2] || $jsonPath[2] == '*') {
                        // level 4
                        if (isset($jsonPath[3])) {
                          foreach ($jsonVal2 as $jsonKey3 => $jsonVal3) {
                            if ($jsonKey3 == $jsonPath[3] || $jsonPath[3] == '*') {
                              // level 5
                              if (isset($jsonPath[4])) {
                                foreach ($jsonVal3 as $jsonKey4 => $jsonVal4) {
                                  if ($jsonKey4 == $jsonPath[4] || $jsonPath[4] == '*') {
                                    $rawData[] = $this->model_gkd_import_tool->arrayFlat($jsonVal4);
                                    
                                    $dataFoundInGivenPath = true;
                                  }
                                }
                              } else {
                                $rawData[] = $this->model_gkd_import_tool->arrayFlat($jsonVal3);
                                
                                $dataFoundInGivenPath = true;
                              }
                            }
                          }
                        } else {
                          $rawData[] = $this->model_gkd_import_tool->arrayFlat($jsonVal2);
                          
                          $dataFoundInGivenPath = true;
                        }
                      }
                    }
                  } else {
                    $rawData[] = $this->model_gkd_import_tool->arrayFlat($jsonVal1);
      
                    $dataFoundInGivenPath = true;
                  }
                }
              }
            } else {
              $rawData[] = $this->model_gkd_import_tool->arrayFlat($jsonVal);
              
              //$jsonArray = $jsonVal;
              $dataFoundInGivenPath = true;
            }
          }
        }
        
        foreach ($rawData as $row) {
          $config = $this->request->post;
          
          // disable remote api function here
          $this->removeExtraFunc($config, array('remote_api'));
    
          $this->populate_fields($config, $row);
          
          foreach ((array) $config['columns'][$cat_field] as $key => $cat) {
            // add subcategories
            if (!empty($config['columns']['sub_product_category'][$key]) && is_string($cat)) {
              foreach ($config['columns']['sub_product_category'][$key] as $subcat) {
                $cat .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
              }
            }
              
            if (is_scalar($cat) && !isset($rows[$cat])) {
              $rows[$cat] = isset($profile['col_binding'][md5($cat)]) ? $profile['col_binding'][md5($cat)] : '';
            }
          }
        }
        
        if (!$dataFoundInGivenPath) {
          ?>
          <div class="spacer"></div>
          
          <div class="alert alert-danger"><?php echo sprintf($this->language->get('error_api_no_data'), $this->request->post['import_api_field']); ?></div>
          
          <div class="pull-right">
            <button type="button" class="btn btn-default cancel" data-step="3"><i class="fa fa-reply"></i> <?php echo $this->language->get('text_previous_step'); ?></button>
          </div>
          
          <div class="spacer"></div>
          <?php
          exit;
          //die('<div class="alert alert-warning">No data has been found in '.$this->request->post['import_api_field'].', make sure to use correct path.</div>');
        }
      } else {
        foreach ($jsonArray as $json) {
          //$rows[] = $json;
          $rows[] = $this->model_gkd_import_tool->arrayFlat($json);
        }
      }
    } else if ($extension == 'xls') {
      // PHPExcel
      require_once(DIR_SYSTEM.'library/PHPExcel/PHPExcel.php');
      /* to try for better perf:
      $objReader = PHPExcel_IOFactory::createReader('Excel2007');
      $objReader->setReadDataOnly(true);
      $objReader->load($import_file);
      */
      $objPHPExcel = PHPExcel_IOFactory::load($import_file);
      
      $sheet = $objPHPExcel->getSheet(isset($config['sheet']) ? $config['sheet'] : 0);
      $highestRow = $sheet->getHighestRow();
      $highestColumn = $sheet->getHighestColumn();

      $rows = array();
      
      $pop = false;
      
      for ($row = 1; $row <= $highestRow; $row++) {
        $arrRow = $row-1;
        $resrow = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, false, false);

        if ($row === 1 && !empty($this->request->post['csv_header'])) {
          continue;
        }
        
        foreach ((array) $this->request->post['columns'][$cat_field] as $cat) {
          if (isset($resrow[0][$cat]) && is_scalar($resrow[0][$cat]) && !isset($rows[$resrow[0][$cat]])) {
            $rows[$resrow[0][$cat]] = isset($profile['col_binding'][md5($resrow[0][$cat])]) ? $profile['col_binding'][md5($resrow[0][$cat])] : '';
          }
        }
      }
    }
    
    return $rows;
  }
  
  private function recursive_array_intersect_key($master, $mask) {
    if (!is_array($master)) { return $master; }
    
    foreach ($master as $k=>$v) {
      if (!isset($mask[$k])) { unset ($master[$k]); continue; }
      if (is_array($mask[$k])) { $master[$k] = $this->recursive_array_intersect_key($master[$k], $mask[$k]); }
    }
    
    return $master;
  }
}