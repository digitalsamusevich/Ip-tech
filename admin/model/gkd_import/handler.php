<?php
class ModelGkdImportHandler extends Model {

  public $simulation = true;
  public $storeIdToName = array();
  public $layoutIdToName = array();
  public $customer_groups = array();
  public $filetype = '';
  private $tool;
  
  public function getObject() {
    return $this;
  }
  
  public function __construct($registry) {
		parent::__construct($registry);
    $this->tool = $this->model_gkd_import_tool->getObject();
  }
  
  public function forceString($field, $value) {
    if (!is_scalar($value)) {
       $this->tool->log(array(
        'row' => $this->session->data['obui_current_line'],
        'status' => 'error',
        'title' => $this->language->get('warning'),
        'msg' => $field.' does not contains a string value, make sure to not select an array for this field, content: <br/>'.print_r($value, true),
      ));
      
      return '';
    }
    
    return $value;
  }
  
  public function simpleArrayHandler($field, $config) {
    $values_array = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    if (count($config['columns'][$field]) == 1 && $config['columns'][$field][0] === '') {
      return array();
    }
    
    foreach ((array) $config['columns'][$field] as $value) {
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    return $values_array;
  }
  
  public function simpleArrayHandlerValue($value, $config) {
    $values_array = array();
    
    if (empty($value)) {
      return $values_array;
    }
    
    if (count($value) == 1 && $value[0] === '') {
      return array();
    }
    
    foreach ((array) $value as $value) {
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    return $values_array;
  }
  
  public function customerGroupHandler($value, $config) {
    $value = $this->db->escape($this->request->clean($value));
    
    if (!$value) return '';
    
    // get by id
    if (is_numeric($value)) {
      $query = $this->db->query("SELECT customer_group_id, name FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int) $value . "'")->row;
    } else {
      $query = $this->db->query("SELECT customer_group_id, name FROM " . DB_PREFIX . "customer_group_description WHERE name = '" . $value . "'")->row;
    }
    
    if (!empty($query['customer_group_id'])) {
      $customer_group_name = $query['name'];
      $customer_group_id = $query['customer_group_id'];
    } else {
      $this->load->model('localisation/language');
      $languages = $this->model_localisation_language->getLanguages();
    
      $customer_group_data = array(
        'approval' => '',
        'sort_order' => '',
      );
      
      foreach ($languages as $language) {
        $customer_group_data['customer_group_description'][$language['language_id']] = array(
         'name' => $value,
         'description' => '',
        );
      }
      
      if (!$this->simulation) {
        if (version_compare(VERSION, '2.1', '>=')) {
          $this->load->model('customer/customer_group');
          $customer_group_id = $this->model_customer_customer_group->addCustomerGroup($this->request->clean($customer_group_data));
        } else {
          $this->load->model('sale/customer_group');
          $customer_group_id = $this->model_sale_customer_group->addCustomerGroup($this->request->clean($customer_group_data));
        }
      }
      
      $customer_group_name = '['.$this->language->get('new').'] ' . $value;
    }
    
    if ($this->simulation) {
      return $customer_group_name;
    } else {
      return $customer_group_id;
    }
  }
  
  public function countryHandler($value, $config, $get_name = false) {
    $value = $this->db->escape($this->request->clean($value));
    
    if (!$value) return '';
    
    // get by id
    if (is_numeric($value)) {
      $query = $this->db->query("SELECT DISTINCT country_id, name FROM " . DB_PREFIX . "country WHERE country_id = '" . (int) $value . "'")->row;
    } else {
      $query = $this->db->query("SELECT DISTINCT country_id, name FROM " . DB_PREFIX . "country WHERE name = '" . $value . "' OR iso_code_2 = '" . strtoupper($value) . "' OR iso_code_3 = '" . strtoupper($value) . "'")->row;
    }
    
    if ($get_name) {
      return !empty($query['name']) ? $query['name'] : $value;
    }
    
    if (!empty($query['country_id'])) {
      if ($this->simulation) {
        return $query['name'];
      } else {
        return $query['country_id'];
      }
    } else {
      $this->tool->log(array(
        'row' => $this->session->data['obui_current_line'],
        'status' => 'error',
        'title' => $this->language->get('warning'),
        'msg' => $this->language->get('warning_country_not_found') . ': ' . $value,
      ));
    }
    
    return '';
  }
  
  public function zoneHandler($value, $config, $country_id = 0, $get_name = false) {
    $value = $this->db->escape($this->request->clean($value));

    if (!$value) return '';
    
    // get by id
    if (is_numeric($value)) {
      $query = $this->db->query("SELECT DISTINCT zone_id, name FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int) $value . "'")->row;
    } else {
      // get country id if is a string
      if (is_string($country_id)) {
        $country = $this->db->query("SELECT DISTINCT country_id, name FROM " . DB_PREFIX . "country WHERE name = '" . $this->db->escape($country_id) . "'")->row;
        
        if (!empty($country['country_id'])) {
          $country_id = $country['country_id'];
        }
      }
      
      $query = $this->db->query("SELECT DISTINCT zone_id, name FROM " . DB_PREFIX . "zone WHERE name = '" . $value . "' OR (code = '" . strtoupper($value) . "' AND country_id = '" . (int) $country_id . "')")->row;
    }
    
    if ($get_name) {
      return !empty($query['name']) ? $query['name'] : $value;
    }
    
    if (!empty($query['zone_id'])) {
      if ($this->simulation) {
        return $query['name'];
      } else {
        return $query['zone_id'];
      }
    } else {
      $this->tool->log(array(
        'row' => $this->session->data['obui_current_line'],
        'status' => 'error',
        'title' => $this->language->get('warning'),
        'msg' => $this->language->get('warning_zone_not_found') . ': ' . $value,
      ));
    }
    
    return '';
  }
  /*
  public function countryHandler($key, $config) {
    if (empty($config['columns']['address'][$key]['country_id'])) {
      return '';
    }
    
    // get by id
    if (is_numeric($config['columns']['address'][$key]['country_id'])) {
      $query = $this->db->query("SELECT DISTINCT country_id, name FROM " . DB_PREFIX . "country WHERE country_id = '" . (int) $config['columns']['address'][$key]['country_id'] . "'")->row;
      
      if ($this->simulation) {
        if (!empty($query['name'])) {
          return '['.$query['country_id'].'] '.$query['name'];
        } else {
          return $this->language->get('not_found');
        }
      } else {
        if (!empty($query['country_id'])) {
          return $query['country_id'];
        }
      }
      
    // get by name
    } else if (is_string($config['columns']['address'][$key]['country_id'])) {
      $query = $this->db->query("SELECT DISTINCT country_id, name FROM " . DB_PREFIX . "country WHERE name = '" . $this->db->escape($this->request->clean($config['columns']['address'][$key]['country_id'])) . "'")->row;
      
      if (!empty($query['country_id'])) {
        if ($this->simulation) {
          return $query['name'];
        } else {
          return $query['country_id'];
        }
      } else {
        $this->session->data['obui_log'][] = array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => $this->language->get('warning_country_not_found') . ': ' . $config['columns']['address'][$key]['country_id'],
        );
      }
    }
    
    return '';
  }
  
  
  public function zoneHandler($key, $config) {
    if (empty($config['columns']['address'][$key]['zone_id'])) {
      return '';
    }

    // get by id
    if (is_numeric($config['columns']['address'][$key]['zone_id'])) {
      $query = $this->db->query("SELECT DISTINCT zone_id, name FROM " . DB_PREFIX . "zone WHERE country_id = '" . (int) $config['columns']['address'][$key]['zone_id'] . "'")->row;
      
      if ($this->simulation) {
        if (!empty($query['name'])) {
          return $query['name'];
        } else {
          return $this->language->get('not_found');
        }
      } else {
        if (!empty($query['zone_id'])) {
          return $query['zone_id'];
        }
      }
      
    // get by name
    } else if (is_string($config['columns']['address'][$key]['zone_id'])) {
      $query = $this->db->query("SELECT DISTINCT zone_id, name FROM " . DB_PREFIX . "zone WHERE name = '" . $this->db->escape($this->request->clean($config['columns']['address'][$key]['zone_id'])) . "'")->row;
      
      if (!empty($query['zone_id'])) {
        if ($this->simulation) {
          return '['.$query['zone_id'].'] '.$query['name'];
        } else {
          return $query['zone_id'];
        }
      } else {
        $this->session->data['obui_log'][] = array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => $this->language->get('warning_zone_not_found') . ': ' . $config['columns']['address'][$key]['zone_id'],
        );
      }
    }
    
    return '';
  }
  */
  
  public function stockHandler($field, $config) {
    if (empty($config['columns'][$field])) {
      return '';
    }

    // get by id
    if (is_numeric($config['columns'][$field])) {
      $query = $this->db->query("SELECT DISTINCT stock_status_id, name FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int) $config['columns'][$field] . "'")->row;
      
      if ($this->simulation) {
        if (!empty($query['name'])) {
          return '['.$query['stock_status_id'].'] '.$query['name'];
        } else {
          return $this->language->get('not_found');
        }
      } else {
        if (!empty($query['stock_status_id'])) {
          return $query['stock_status_id'];
        }
      }
      
    // get by name
    } else if (is_string($config['columns'][$field])) {
      $query = $this->db->query("SELECT DISTINCT stock_status_id, name FROM " . DB_PREFIX . "stock_status WHERE name = '" . $this->db->escape($this->request->clean($config['columns'][$field])) . "'")->row;
      
      if (!empty($query['stock_status_id'])) {
        if ($this->simulation) {
          return '['.$query['stock_status_id'].'] '.$query['name'];
        } else {
          return $query['stock_status_id'];
        }
      } else if (true) {
        // stock does not exists, create it ?
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
      
        $stock_data = array();
        
        foreach ($languages as $language) {
          $stock_data['stock_status'][$language['language_id']] = array(
           'name' => $config['columns'][$field],
          );
        }
        
        $this->load->model('localisation/stock_status');
        
        if (!$this->simulation) {
          $stock_id = $this->model_localisation_stock_status->addStockStatus($this->request->clean($stock_data));
        } else {
          return '['.$this->language->get('new').'] '.$config['columns'][$field];
        }
        
        return $stock_id;
      }
    }
    
    return '';
  }
  
  public function dataArrayHandler($field, $config) {
    $values_array = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    if (count($config['columns'][$field]) == 1 && $config['columns'][$field][0] === '') {
      return array();
    }
    
    foreach ((array) $config['columns'][$field] as $value) {
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    return $values_array;
  }
  
  public function optionHandler($field, $config, $line, $product_id) {
    $return_values = $values_array = $summed_quantity = $header_keys = $header_keys_number = array();
    
    if (empty($config['columns'][$field]) && empty($config['option_fields'])) {
      return $values_array;
    }
    
    if (isset($config['columns'][$field]) && array_filter($config['columns'][$field])) {
      foreach ((array) $config['columns'][$field] as $key => $value) {
        if ($value === '') {
          continue;
        } else if (is_array($value)) {
          $values_array = array_merge($values_array, $value);
          
          foreach($value as $v) {
            //$header_keys[] = $key;
            $header_keys[] = $config['columns_bindings'][$field][$key];
            $header_keys_number[] = $key;
          }
          //$force_header_name = 'Option'; // force option name because header cannot be found when merged
          
        } else if (!empty($config['option_separator'])) {
          $value = explode(@html_entity_decode($config['option_separator'], ENT_QUOTES, 'UTF-8'), $value);
          $values_array = array_merge($values_array, $value);
          
          foreach($value as $v) {
            $header_keys[] = $config['columns_bindings'][$field][$key];
            $header_keys_number[] = $key;
          }
        } else if (!empty($config['multiple_separator'])) {
          $value = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value);
          $values_array = array_merge($values_array, $value);
          
          foreach($value as $v) {
            $header_keys[] = $config['columns_bindings'][$field][$key];
            $header_keys_number[] = $key;
          }
        // } else if (!empty($config['multiple_separator'])) {
          // $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
          // $force_header_name = 'Option'; // force option name because header cannot be found when merged
        } else {
          $values_array[] = $value;
          $header_keys[] = $config['columns_bindings'][$field][$key];
          $header_keys_number[] = $key;
        }
      }
    } else {
      if (!empty($config['option_fields'])) {
        $values_array[] = '';
      }
    }
    
    $this->load->model('localisation/language');
    $languages = $this->model_localisation_language->getLanguages();
    
    $toInsertArray = array();
    
    foreach ($values_array as $current_key => &$value) {
      $option_names = $option_values = $option_names_ml = $option_values_ml = array();
      $option_type_arr = $option_image_arr = $option_price_arr = $option_price_prefix_arr = $option_required_arr = $option_weight_arr = $option_quantity_arr = $option_subtract_arr = $option_points_arr = $option_sku_arr = $option_upc_arr = $option_ean_arr = $option_model_arr= array();
      $option_type = $option_name = $option_prod_opt_val_id = $option_image = $option_price = $option_price_prefix = $option_required = $option_weight = $option_quantity = $option_subtract = $option_points = $option_sku = $option_upc = $option_ean = $option_model = '';
    
      if (isset($header_keys[$current_key])) {
        $header_key = $header_keys[$current_key];
      }
      
      // csv advanced option fields
      if (!in_array($this->filetype, array('xml', 'json')) && !empty($config['option_fields']) && !$value) {
        if (isset($config['option_fields']['type']) && !empty($line[$config['option_fields']['type']])) {
          $option_type = strtolower($line[$config['option_fields']['type']]);
        }
        /*
        if (isset($config['option_fields']['name'])) {
          // handle multilingual values
          if (is_array($config['option_fields']['name'])) {
            $ml_values = array();
            foreach ($config['option_fields']['name'] as $lang_id => $optNameField) {
              if (!empty($line[$optNameField])) {
                $ml_values[$lang_id] = $line[$optNameField];
              }
            }
            
            if (!empty($ml_values)) {
              $option_names_ml[] = $ml_values;
              $option_names[] = reset($ml_values);
            }
          } else if (!empty($line[$config['option_fields']['name']])) {
            $option_names[] = $line[$config['option_fields']['name']];
          }
        }*/
        if (isset($config['option_fields']['name'])) {
          // handle multilingual values
          if (is_array($config['option_fields']['name'])) {
            $ml_values = array();
            foreach ($config['option_fields']['name'] as $lang_id => $optNameField) {
              if (is_array($optNameField)) {
                foreach ($optNameField as $k => $valField) {
                  if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                    $ml_values[$k][$lang_id] = $line[$valField];
                  } else {
                    if (substr($valField, 0, 2) == './') {
                      $valField = substr($valField, 2);
                    }
                    
                    $currentVal = $this->getArrayPath($value, $valField);
                    if ($currentVal) {
                      //$option_values[] = $this->getArrayPath($value, $valField);
                      $ml_values[$k][$lang_id] = $currentVal;
                      
                    }
                  }
                }
              } else if (!empty($line[$optNameField]) && substr($optNameField, 0, 2) !== './') {
                //$ml_values[$current_key][$lang_id] = $line[$optNameField];
                $currentVal = $line[$optNameField];
              } else {
                if (substr($optNameField, 0, 2) == './') {
                  $optNameField = substr($optNameField, 2);
                }
                
                $currentVal = $this->getArrayPath($value, $optNameField);
                
                // if ($currentVal) {
                  // $ml_values[$current_key][$lang_id] = $currentVal;
                // }
              }
              
              if (($currentVal)) {
                if (is_array($currentVal)) {
                  foreach ($currentVal as $k => $valField) {
                    if (!empty($valField)) {
                      $ml_values[$k][$lang_id] = $valField;
                    }
                  }
                } else {
                  $ml_values[$current_key][$lang_id] = $currentVal;
                }
              }
            }
            /*
            if (!empty($ml_values)) {
              $option_names_ml[] = $ml_values;
              $option_names[] = reset($ml_values);
            }
            */
            if (!empty($ml_values)) {
              foreach($ml_values as $ml_value) {
                $option_names_ml[] = $ml_value;
                $option_names[] = reset($ml_value);
              }
            }
          } else if (!empty($line[$config['option_fields']['name']])) {
            $option_names[] = $line[$config['option_fields']['name']];
          }
        }
        
        if (isset($config['option_fields']['value']) && is_array($config['option_fields']['value'])) {
          $ml_values = array();
          $i = 0;
          
          foreach ($config['option_fields']['value'] as $lang_id => $optValueField) {
            if (($optValueField == '[current]' || $optValueField == '.' || $optValueField == '') && is_string($value)) {
              $ml_values[$current_key][$lang_id] = $value;
            } else if (strpos($optValueField, '~')) {
              $valFields = explode('~', $optValueField);
              $currentVal = array();
              $composed = '';
              
              foreach ($valFields as $k => $valField) {
                if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                  $composed .= (!empty($composed) ? '|' : '') . $line[$valField];
                } else {
                  if (substr($valField, 0, 2) == './') {
                    $valField = substr($valField, 2);
                  }
                  
                  $composed .= (!empty($composed) ? '|' : '') . $this->getArrayPath($value, $valField);
                }
              }
              
              $currentVal[] = $composed;
            } else if (strpos($optValueField, '+')) {
              $valFields = explode('+', $optValueField);
              $currentVal = array();
              
              foreach ($valFields as $k => $valField) {
                if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                  $currentVal[] = $line[$valField];
                } else {
                  if (substr($valField, 0, 2) == './') {
                    $valField = substr($valField, 2);
                  }
                  
                  $currentVal[] = $this->getArrayPath($value, $valField);
                  
                  if ($currentVal) {
                    // $option_values[] = $this->getArrayPath($value, $valField);
                    // $ml_values[$k][$lang_id] = $currentVal;
                    
                  }
                }
              }
            } else if (strpos($optValueField, '|')) {
              $valFields = explode('|', $optValueField);
              //$option_values[] = '';
              
              foreach ($valFields as $valField) {
                if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                  $currentVal = $line[$valField];
                  break;
                } else {
                  if (substr($valField, 0, 2) == './') {
                    $valField = substr($valField, 2);
                  }
                  
                  $currentVal = $this->getArrayPath($value, $valField);
                  if ($currentVal) {
                    //$option_values[] = $this->getArrayPath($value, $valField);
                    //$ml_values[$current_key][$lang_id] = $currentVal;
                    break;
                  }
                }
              }
            } else if (!empty($optValueField)) {
              //$option_values[] = $this->getArrayPath($value, $optValueField);
              // use initial slash to get an absolute value instead of relative to current node
              if ((!$value || substr($optValueField, 0, 1) == '/') && !empty($line[$optValueField])) {
                $optValueField = ltrim($optValueField, '/');
                // $ml_values[$current_key][$lang_id] = $line[$optValueField];
                $currentVal = $line[$optValueField];
              } else {
                $currentVal = $this->getArrayPath($value, $optValueField);
                // if ($currentVal) {
                  // $ml_values[$current_key][$lang_id] = $currentVal;
                // }
              }
              
              if (!empty($config['option_separator'])) {
                $currentVal = explode($config['option_separator'], $currentVal);
              }
            }
            
            if (!empty($currentVal)) {
              if (is_array($currentVal)) {
                foreach ($currentVal as $k => $valField) {
                  if (!empty($valField)) {
                    $ml_values[$k][$lang_id] = $valField;
                  }
                }
              } else {
                $ml_values[$current_key][$lang_id] = $currentVal;
              }
            }
          }
          
          if (!empty($ml_values)) {
            foreach($ml_values as $ml_value) {
              $option_values_ml[] = $ml_value;
              $option_values[] = reset($ml_value);
            }
          }
          
          if (!empty($config['option_fields_default']['name'])) {
            foreach ($config['option_fields_default']['name'] as $lang_id => $optNameField) {
              if (empty($option_names_ml[$i][$lang_id])) {
                $optNames = explode('+', $optNameField);
                $option_names_ml[$i][$lang_id] = isset($optNames[$i]) ? $optNames[$i] : 'Size';
              }
              $option_names[$i] = reset($option_names_ml[$i]);
            }
          }
          
          // copy names if there is more values than names
          if (count($option_names_ml)) {
            while (count($option_values_ml) > count($option_names_ml)) {
              $option_names_ml[] = end($option_names_ml);
              $option_names[] = end($option_names);
            }
          } else if (count($option_names)) {
            while (count($option_values_ml) > count($option_names)) {
              //$option_names_ml[] = end($option_names_ml);
              $option_names[] = end($option_names);
            }
          }
        }
        
        /*
        if (isset($config['option_fields']['value'])) {
          if (is_array($config['option_fields']['value'])) {
            $ml_values = array();
            foreach ($config['option_fields']['value'] as $lang_id => $optValueField) {
              if (!empty($line[$optValueField])) {
                if (is_string($line[$optValueField]) && !empty($this->xfn_multiple_separator[$optValueField])) {
                  $line[$optValueField] = explode($this->xfn_multiple_separator[$optValueField], htmlspecialchars_decode(trim($line[$optValueField])));
                }

                $ml_values[$lang_id] = $line[$optValueField];
              }
            }
            
            if (!empty($ml_values)) {
              $option_values_ml[] = $ml_values;
              $option_values[] = reset($ml_values);
            }
          } else if (is_string($config['option_fields']['value']) && strpos($config['option_fields']['value'], '+')) {
            $valFields = explode('+', $config['option_fields']['value']);
            
            foreach ($valFields as $valField) {
              if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                $option_values[] = $line[$valField];
              }
            }
          } else if (is_string($config['option_fields']['value']) && strpos($config['option_fields']['value'], '|')) {
            $valFields = explode('|', $config['option_fields']['value']);
            $option_values[] = '';
            
            foreach ($valFields as $valField) {
              if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                $option_values[] = $line[$valField];
                break;
              }
            }
          } else if (!empty($line[$config['option_fields']['value']])) {
            //$option_values[] = $line[$config['option_fields']['value']];
            $currentVal = $line[$config['option_fields']['value']];
            
            if (!empty($config['option_separator'])) {
              $option_values[] = explode($config['option_separator'], $currentVal);
            }
          }
        }
        */
        $optionFields = array('prod_opt_val_id', 'image', 'price', 'price_prefix', 'required', 'quantity', 'subtract', 'weight', 'points', 'sku', 'upc', 'ean', 'model');
      
        # add_option_field #
        
        foreach ($optionFields as $currentOptType) {
          if (isset($config['option_fields'][$currentOptType]) && !empty($line[$config['option_fields'][$currentOptType]])) {
            ${'option_'.$currentOptType} = $line[$config['option_fields'][$currentOptType]];
          } else if (!empty($config['option_fields_default'][$currentOptType])) {
            ${'option_'.$currentOptType} = $config['option_fields_default'][$currentOptType];
          }
        }
        
        // set defaults if empty
        foreach ($option_values as $i => $option_value) {
          if (!empty($config['option_fields_default']['name'])) {
            foreach ($config['option_fields_default']['name'] as $lang_id => $optNameField) {
              if (empty($option_names_ml[$i][$lang_id])) {
                $optNames = explode('+', $optNameField);
                $option_names_ml[$i][$lang_id] = isset($optNames[$i]) ? $optNames[$i] : 'Size';
              }
              $option_names[$i] = reset($option_names_ml[$i]);
            }
          }
          
          if (!empty($config['option_fields_default']['value'])) {
            foreach ($config['option_fields_default']['value'] as $lang_id => $optValueField) {
              if (!empty($optValueField)) {
                if (empty($option_values_ml[$i][$lang_id])) {
                  $option_values_ml[$i][$lang_id] = $optValueField;
                }
                $option_values[$i] = reset($option_values_ml[$i]);
              }
            }
          }
        }
      }
      // xml advanced option fields
      else if (in_array($this->filetype, array('xml', 'json')) && isset($config['option_fields']['value']) && array_filter($config['option_fields']['value'])) {
        if (isset($config['option_fields']['type']) && !empty($line[$config['option_fields']['type']])) {
          $option_type = strtolower($line[$config['option_fields']['type']]);
        } else if (isset($config['option_fields']['type']) && !empty($value[$config['option_fields']['type']])) {
          $option_type = strtolower($this->getArrayPath($value, $config['option_fields']['type']));
        }
        
        if (isset($config['option_fields']['name'])) {
          // handle multilingual values
          if (is_array($config['option_fields']['name'])) {
            $ml_values = array();
            foreach ($config['option_fields']['name'] as $lang_id => $optNameField) {
              if (is_array($optNameField)) {
                foreach ($optNameField as $k => $valField) {
                  if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                    $ml_values[$k][$lang_id] = $line[$valField];
                  } else {
                    if (substr($valField, 0, 2) == './') {
                      $valField = substr($valField, 2);
                    }
                    
                    $currentVal = $this->getArrayPath($value, $valField);
                    if ($currentVal) {
                      //$option_values[] = $this->getArrayPath($value, $valField);
                      $ml_values[$k][$lang_id] = $currentVal;
                      
                    }
                  }
                }
              } else if (!empty($line[$optNameField]) && substr($optNameField, 0, 2) !== './') {
                //$ml_values[$current_key][$lang_id] = $line[$optNameField];
                $currentVal = $line[$optNameField];
              } else {
                if (substr($optNameField, 0, 2) == './') {
                  $optNameField = substr($optNameField, 2);
                }
                
                $currentVal = $this->getArrayPath($value, $optNameField);
                
                // if ($currentVal) {
                  // $ml_values[$current_key][$lang_id] = $currentVal;
                // }
              }
              
              if (($currentVal)) {
                if (is_array($currentVal)) {
                  foreach ($currentVal as $k => $valField) {
                    if (!empty($valField)) {
                      $ml_values[$k][$lang_id] = $valField;
                    }
                  }
                } else {
                  $ml_values[$current_key][$lang_id] = $currentVal;
                }
              }
            }
            /*
            if (!empty($ml_values)) {
              $option_names_ml[] = $ml_values;
              $option_names[] = reset($ml_values);
            }
            */
            if (!empty($ml_values)) {
              foreach($ml_values as $ml_value) {
                $option_names_ml[] = $ml_value;
                $option_names[] = reset($ml_value);
              }
            }
          } else if (!empty($line[$config['option_fields']['name']])) {
            $option_names[] = $line[$config['option_fields']['name']];
          }
        }
        
        if (isset($config['option_fields']['value']) && is_array($config['option_fields']['value'])) {
          $ml_values = array();
          $i = 0;
          
          foreach ($config['option_fields']['value'] as $lang_id => $optValueField) {
            if (($optValueField == '[current]' || $optValueField == '.' || $optValueField == '') && is_string($value)) {
              $ml_values[$current_key][$lang_id] = $value;
            } else if (strpos($optValueField, '~')) {
              $valFields = explode('~', $optValueField);
              $currentVal = array();
              $composed = '';
              
              foreach ($valFields as $k => $valField) {
                if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                  $composed .= (!empty($composed) ? '|' : '') . $line[$valField];
                } else {
                  if (substr($valField, 0, 2) == './') {
                    $valField = substr($valField, 2);
                  }
                    
                  $composed .= (!empty($composed) ? '|' : '') . $this->getArrayPath($value, $valField);
                }
              }
              
              $currentVal[] = $composed;
            } else if (strpos($optValueField, '+')) {
              $valFields = explode('+', $optValueField);
              $currentVal = array();
              
              foreach ($valFields as $k => $valField) {
                if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                  $currentVal[] = $line[$valField];
                } else {
                  if (substr($valField, 0, 2) == './') {
                    $valField = substr($valField, 2);
                  }
                    
                  $currentVal[] = $this->getArrayPath($value, $valField);
                  
                  if ($currentVal) {
                    // $option_values[] = $this->getArrayPath($value, $valField);
                    // $ml_values[$k][$lang_id] = $currentVal;
                    
                  }
                }
              }
            } else if (strpos($optValueField, '|')) {
              $valFields = explode('|', $optValueField);
              //$option_values[] = '';
              
              foreach ($valFields as $valField) {
                if (!empty($line[$valField]) && substr($valField, 0, 2) !== './') {
                  $currentVal = $line[$valField];
                  break;
                } else {
                  if (substr($valField, 0, 2) == './') {
                    $valField = substr($valField, 2);
                  }
                  
                  $currentVal = $this->getArrayPath($value, $valField);
                  if ($currentVal) {
                    //$option_values[] = $this->getArrayPath($value, $valField);
                    //$ml_values[$current_key][$lang_id] = $currentVal;
                    break;
                  }
                }
              }
            } else if (!empty($optValueField)) {
              // use initial slash to get an absolute value instead of relative to current node
              if ((!$value || substr($optValueField, 0, 1) == '/') && !empty($line[$optValueField])) {
                $optValueField = ltrim($optValueField, '/');
                // $ml_values[$current_key][$lang_id] = $line[$optValueField];
                $currentVal = $line[$optValueField];
              } else {
                $currentVal = $this->getArrayPath($value, $optValueField);
                // get array of values in case getArrayPath generate extra array container
                if (is_array($currentVal) && count($currentVal) == 1 && is_array($currentVal[0])) {
                  $currentVal = $currentVal[0];
                }
                // if ($currentVal) {
                  // $ml_values[$current_key][$lang_id] = $currentVal;
                // }
              }
              
              if (!empty($config['option_separator'])) {
                $currentVal = explode($config['option_separator'], $currentVal);
              }
            }
            
            if (!empty($currentVal)) {
              if (is_array($currentVal)) {
                foreach ($currentVal as $k => $valField) {
                  if (!empty($valField)) {
                    $ml_values[$k][$lang_id] = $valField;
                  }
                }
              } else {
                $ml_values[$current_key][$lang_id] = $currentVal;
              }
            }
          }
          
          if (!empty($ml_values)) {
            foreach($ml_values as $ml_value) {
              $option_values_ml[] = $ml_value;
              $option_values[] = reset($ml_value);
            }
          }
          
          // copy names if there is more values than names
          if (count($option_names_ml)) {
            while (count($option_values_ml) > count($option_names_ml)) {
              $option_names_ml[] = end($option_names_ml);
              $option_names[] = end($option_names);
            }
          }
        }
        
        $optionFields = array('prod_opt_val_id', 'image', 'price', 'price_prefix', 'required', 'quantity', 'subtract', 'weight', 'points', 'sku', 'upc', 'ean', 'model');
      
        # add_option_field #
        
        foreach ($optionFields as $currentOptType) {
          $currentVal = NULL;
          // if main parameter is empty try to get direct value
          if (empty($value) && isset($config['option_fields'][$currentOptType]) && !empty($line[$config['option_fields'][$currentOptType]])) {
            $currentVal = $line[$config['option_fields'][$currentOptType]];
          
          // else try to get relative value
          } else if (!empty($config['option_fields'][$currentOptType]) && $config['option_fields'][$currentOptType] == '{model_option_value}') {
            $currentVal = $option_values;
            
            if (is_array($currentVal)) {
              foreach ($currentVal as &$cVal) {
                $cVal = $config['columns']['model'] . '-' . $cVal;
              }
            }
          } else if (!empty($config['option_fields'][$currentOptType])) {
            $currentVal = ${'option_'.$currentOptType} = $this->getArrayPath($value, $config['option_fields'][$currentOptType]);
          }
          
          if (!empty($currentVal)) {
            if (is_array($currentVal)) {
              foreach ($currentVal as $k => $valField) {
                if (isset($valField)) {
                  ${'option_'.$currentOptType.'_arr'}[$k] = $valField;
                }
              }
              
               ${'option_'.$currentOptType} = reset(${'option_'.$currentOptType.'_arr'});
            } else {
              ${'option_'.$currentOptType.'_arr'}[$current_key] = $currentVal;
            }
          }
        }
        
        // sum quantities
        foreach ($option_values as $optVal) {
          if (!empty($option_quantity)) {
            $summed_quantity[$optVal] = isset($summed_quantity[$optVal]) ? $summed_quantity[$optVal] + $option_quantity : $option_quantity;
          }
        }
        
        // set defaults if empty
        foreach ($option_values as $i => $option_value) {
          if (!empty($config['option_fields_default']['name'])) {
            foreach ($config['option_fields_default']['name'] as $lang_id => $optNameField) {
              if (empty($option_names_ml[$i][$lang_id])) {
                $optNames = explode('+', $optNameField);
                if (count($option_values) > 1) {
                  $option_names_ml[$i][$lang_id] = isset($optNames[$i]) ? $optNames[$i] : 'Size';
                } else if (isset($header_keys_number[$current_key]) && isset($optNames[$header_keys_number[$current_key]])) {
                  $option_names_ml[$i][$lang_id] = $optNames[$header_keys_number[$current_key]];
                } else {
                  $option_names_ml[$i][$lang_id] = 'Size';
                }
              }
              $option_names[$i] = reset($option_names_ml[$i]);
            }
          }
          
          if (!empty($config['option_fields_default']['value'])) {
            foreach ($config['option_fields_default']['value'] as $lang_id => $optValueField) {
              if (!empty($optValueField)) {
                if (empty($option_values_ml[$i][$lang_id])) {
                  $option_values_ml[$i][$lang_id] = $optValueField;
                }
                $option_values[$i] = reset($option_values_ml[$i]);
              }
            }
          }
        }
      }
      
      // option in array mode
      else if (is_array($value)) {
        $option_type      = !empty($value['type']) ? strtolower($value['type']) : 'select';
        $option_names[]   = isset($value['name']) ? $value['name'] : '';
        $option_values[]  = isset($value['value']) ? $value['value'] : '';
        $option_price     = isset($value['price']) ? (isset($value['price_prefix']) ? $value['price_prefix'] : '') . $value['price'] : '';
        $option_quantity  = isset($value['quantity']) ? $value['quantity'] : '';
        $option_subtract  = isset($value['subtract']) ? $value['subtract'] : '';
        $option_weight    = isset($value['weight']) ? (isset($value['weight_prefix']) ? $value['weight_prefix'] : '') . $value['weight'] : '';
        $option_required  = isset($value['required']) ? $value['required'] : '';
        $option_points    = isset($value['points']) ? (isset($value['points_prefix']) ? $value['points_prefix'] : '') . $value['points'] : '';
        $option_sku       = isset($value['sku']) ? $value['sku'] : '';
        $option_upc       = isset($value['upc']) ? $value['upc'] : '';
      }
      
      // option in string mode
      else if (!empty($config['option_format'])) {
        if (strpos($value, ':') !== false) {
          $values = explode(':', $value);
        }
        
        // set defaults
        foreach (array('prod_opt_val_id', 'name', 'value', 'type', 'price', 'image', 'price_prefix', 'required', 'quantity', 'subtract', 'weight', 'points', 'sku', 'upc', 'ean', 'model') as $currentOptType) {
          if (empty(${'option_'.$currentOptType})) {
            ${'option_'.$currentOptType} =  isset($config['option_fields_default'][$currentOptType]) ? $config['option_fields_default'][$currentOptType] : '';
          }
        }
        
        $optFormat = array_flip($config['option_format']);
        
        $option_type      = (isset($optFormat['type']) && !empty($values[$optFormat['type']])) ? strtolower($values[$optFormat['type']]) : 'select';
        $option_names[]   = (isset($optFormat['name']) && isset($values[$optFormat['name']])) ? $values[$optFormat['name']] : '';
        $option_values[]  = (isset($optFormat['value']) && isset($values[$optFormat['value']])) ? $values[$optFormat['value']] : '';
        $option_price     = (isset($optFormat['price']) && isset($values[$optFormat['price']])) ? $values[$optFormat['price']] : '';
        $option_quantity  = (isset($optFormat['quantity']) && isset($values[$optFormat['quantity']])) ? $values[$optFormat['quantity']] : '';
        $option_subtract  = (isset($optFormat['subtract']) && isset($values[$optFormat['subtract']])) ? $values[$optFormat['subtract']] : '';
        $option_weight    = (isset($optFormat['weight']) && isset($values[$optFormat['weight']])) ? $values[$optFormat['weight']] : '';
        $option_required  = (isset($optFormat['required']) && isset($values[$optFormat['required']])) ? $values[$optFormat['required']] : '';
        $option_points    = (isset($optFormat['points']) && isset($values[$optFormat['points']])) ? $values[$optFormat['points']] : '';
        $option_sku       = (isset($optFormat['sku']) && isset($values[$optFormat['sku']])) ? $values[$optFormat['sku']] : '';
        $option_upc       = (isset($optFormat['upc']) && isset($values[$optFormat['upc']])) ? $values[$optFormat['upc']] : '';
        
        // set defaults
        foreach ($option_values as $i => $option_value) {
          if (!empty($config['option_fields_default']['name'])) {
            foreach ($config['option_fields_default']['name'] as $lang_id => $optNameField) {
              if (empty($option_names_ml[$i][$lang_id])) {
                $optNames = explode('+', $optNameField);
                $option_names_ml[$i][$lang_id] = isset($optNames[$i]) ? $optNames[$i] : 'Size';
              }
              $option_names[$i] = reset($option_names_ml[$i]);
            }
          }
          
          if (!empty($config['option_fields_default']['value'])) {
            foreach ($config['option_fields_default']['value'] as $lang_id => $optValueField) {
              if (!empty($optValueField)) {
                if (empty($option_values_ml[$i][$lang_id])) {
                  $option_values_ml[$i][$lang_id] = $optValueField;
                }
                $option_values[$i] = reset($option_values_ml[$i]);
              }
            }
          }
        }
      }
      
      // option in string mode
      else {
        if (strpos($value, ':') !== false) {
          $values = explode(':', $value);
        }
        
        if (empty($values)) {
          // get column header
          $column_headers = (array) json_decode(base64_decode($config['column_headers']));
          
          if (!empty($config['option_fields_default']['name'])) {
            // set default option name, in case of multiple values set in main option field use comma as separator
            foreach ($config['option_fields_default']['name'] as $lang_id => $optNames) {
              $optNames = explode('+', $optNames);
              
              if (isset($optNames[(isset($header_key) ? $header_key : 0)])) {
                $option_names_ml[$lang_id] = $optNames[(isset($header_key) ? $header_key : 0)];
              } else {
                $option_names_ml[$lang_id] = $optNames[0];
              }
            }
            
            $option_names[] = reset($option_names_ml);
            $option_values[] = $value;
          } else if (empty($force_header_name) && isset($header_key) && !empty($column_headers[ $header_key ])) {
            $option_names[] = $column_headers[ $header_key ];
            $option_values[] = $value;
          } else if (!empty($force_header_name)) {
            $option_names[] = $force_header_name;
            $option_values[] = $value;
          } else {
            $option_names[] = 'Option';
            $option_values[] = $value;
          }
        } else if (count($values) == 2) {
          // name:value
          $option_names[] = $values[0];
          $option_values[] = $values[1];
        } else if (count($values) == 3) {
          // type:name:value
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
        } else if (count($values) == 4) {
          // type:name:value:price
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
          $option_price = $values[3];
        } else if (count($values) == 5) {
          // type:name:value:price:qty
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
          $option_price = $values[3];
          $option_quantity = $values[4];
        } else if (count($values) == 6) {
          // type:name:value:price:qty:subtract
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
          $option_price = $values[3];
          $option_quantity = $values[4];
          $option_subtract = $values[5];
        } else if (count($values) == 7) {
          // type:name:value:price:qty:subtract:weight
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
          $option_price = $values[3];
          $option_quantity = $values[4];
          $option_subtract = $values[5];
          $option_weight = $values[6];
        } else if (count($values) == 8) {
          // type:name:value:price:qty:subtract:weight:required
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
          $option_price = $values[3];
          $option_quantity = $values[4];
          $option_subtract = $values[5];
          $option_weight = $values[6];
          $option_required = $values[7];
        } else if (count($values) == 9) {
          // type:name:value:price:qty:subtract:weight:required
          $option_type = strtolower($values[0]);
          $option_names[] = $values[1];
          $option_values[] = $values[2];
          $option_price = $values[3];
          $option_quantity = $values[4];
          $option_subtract = $values[5];
          $option_weight = $values[6];
          $option_required = $values[7];
          $option_sku = $values[8];
        } else {
          // too much parts ?
          continue;
        }
      }
      
      // set default
      foreach (array('prod_opt_val_id', 'type', 'price', 'image', 'price_prefix', 'required', 'quantity', 'subtract', 'weight', 'points', 'sku', 'upc', 'ean', 'model') as $currentOptType) {
        if (empty(${'option_'.$currentOptType})) {
          ${'option_'.$currentOptType} =  isset($config['option_fields_default'][$currentOptType]) ? $config['option_fields_default'][$currentOptType] : '';
        }
      }
      
      if (empty($option_type)) {
        $option_type = 'select';
      }
      
      // deduplicate if option to insert already exists
      foreach ($option_values as $i => $option_value) {
        if (in_array($option_names[$i].':'.serialize($option_value), $toInsertArray)) {
          //unset($option_values[$i]);
        } else {
          $toInsertArray[] = $option_names[$i].':'.serialize($option_value);
        }
      }
      
      if (!empty($config['filters_from_options'])) {
        if ($option_quantity) {
          foreach ($option_values as $i => $optVal) {
            $config['columns']['product_filter'][$option_names[$i].':'.serialize($optVal)] = $option_names[$i] .':'. $optVal;
          }
        }
      }
      
      // calculate price difference
      if (!empty($config['option_price_mode']) && $option_price && !(isset($option_price_prefix) && $option_price_prefix == '=')) {
        if ($product_id) {
          $query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id = '" . (int) $product_id . "'")->row;
        }
        
        if (isset($query['price'])) {
          $product_price = $query['price'];
        } else {
          $product_price = $config['columns']['price'];
        }
        
        $option_price = (float) $option_price - (float) $product_price;
      }
      
      if ($this->simulation) {
        foreach ($option_values as $i => $option_value) {
          if (!$option_value) continue;

          if (isset($summed_quantity[$option_value])) {
            if (!isset($option_quantity_arr[$i])) {
              $option_quantity_arr[$i] = $summed_quantity[$option_value];
            }
          }
          
          if (!empty($config['include_option'])) {
            if (!in_array($option_names[$i], explode('|', $config['include_option']))) {
              continue;
            }
          }
          
          if (is_array($option_value)) {
            $return_values[$option_names[$i].':'.serialize($option_value).':'.$option_weight] = $option_names[$i] .' <b>></b> '. print_r($option_value, true) . ' [qty: '. (int) $option_quantity . ', price: '.$option_price.']';
          } else {
            $optPreview = '';
            $optPreviewData = array();
            $optPreview .= $option_names[$i] .' <b>></b> '. $option_value;
            
            foreach (array('prod_opt_val_id', 'quantity', 'price', 'ean', 'sku', 'upc', 'model', 'weight') as $dispOptType) {
              if (!empty(${'option_'.$dispOptType.'_arr'}[$i]) || !empty(${'option_'.$dispOptType})) {
                $optPreviewData[] = str_replace('quantity', 'qty', $dispOptType) . ': '. (!empty(${'option_'.$dispOptType.'_arr'}[$i]) ? ${'option_'.$dispOptType.'_arr'}[$i] : ${'option_'.$dispOptType});
              }
            }
            
            if (!empty($optPreviewData)) {
              $optPreview .= ' [';
              $optPreview .= implode(', ', $optPreviewData);
              $optPreview .= ']';
            }
            
            $return_values[$option_names[$i].':'.serialize($option_value).':'.$option_weight] = $optPreview;
            //$return_values[] = $option_names[$i] .' <b>></b> '. $option_value . ' [qty: '. (int) $option_quantity . ', price: '.$option_price.']'; //. ($option_price ? ' <b>></b> '. ($option_price > 0 ? '+' : '') . $option_price : ''); // disabled display price because calculation is not correct in case of product insert
          }
        }
        
        continue;
      }
      
      foreach ($option_values as $i => $option_value) {
        if (!$option_value) continue;
        
        if (isset($option_names_ml[$i])) {
          $option_name_ml = $option_names_ml[$i];
          $option_name = reset($option_names_ml[$i]);
        } else {
          $option_name = $option_names[$i];
        }
        
        if (!empty($config['include_option'])) {
          if (!in_array($option_names[$i], explode('|', $config['include_option']))) {
            continue;
          }
        }
        
        if (isset($summed_quantity[$option_value])) {
          $option_quantity_arr[$i] = $summed_quantity[$option_value];
        }
        
        // set current value in case of array of values
        $optionFields = array('prod_opt_val_id', 'image', 'price', 'price_prefix', 'required', 'quantity', 'subtract', 'weight', 'points', 'sku', 'upc', 'ean', 'model');
      
        # add_option_field #
        
        foreach ($optionFields as $currentOptType) {
          if (isset(${'option_'.$currentOptType.'_arr'}[$i])) {
            ${'option_'.$currentOptType} = ${'option_'.$currentOptType.'_arr'}[$i];
          }
        }
        
        /*
        // option name binding
        switch ($option_name) {
          case 'option_size': $option_name = 'size'; break;
          case 'option_color': $option_name = 'color'; break;
        }
        */
        
        if (isset($option_values_ml[$i])) {
          $option_value_ml = $option_values_ml[$i];
        }
        
        // get option id or create
        $opt_group = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_description WHERE name = '" . $this->db->escape($option_name) . "' OR name = '" . $this->db->escape(htmlspecialchars($option_name, ENT_QUOTES, 'UTF-8')) . "'")->row;
        
        // group exists - get id
        if (!empty($opt_group['option_id'])) {
          $option_id = $opt_group['option_id'];
        }
        //  group not exists - create
        else {
          $opt_group_data = array();
          $opt_group_data['sort_order'] = '';
          $opt_group_data['type'] = $option_type;
          $opt_group_data['option_description'] = array();
          $opt_group_data['option_value'] = array();
          
          foreach ($languages as $language) {
            $opt_group_data['option_description'][$language['language_id']]['name'] = !empty($option_name_ml[$language['language_id']]) ? $option_name_ml[$language['language_id']] : $option_name;
            $opt_group_data['option_description'][$language['language_id']]['description'] = '';
          }
          
          $this->load->model('catalog/option');
          $option_id = $this->model_catalog_option->addOption($this->request->clean($opt_group_data));
        }
        
        
        // get option value id or create
        $opt = $this->db->query("SELECT option_value_id FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int) $option_id . "' AND name = '" . $this->db->escape($option_value) . "' COLLATE utf8_bin")->row;
        
        // option value exists ?
        if (!empty($opt['option_value_id'])) {
          $option_value_id = $opt['option_value_id'];
          
          // force update option
          if (false) { #force_option_udpate#
            // image download
            if ($option_image) {
              if (strpos($option_image, 'http') === false) {
                $opt_data['image'] = trim($option_image);
              } else {
                $file_info = pathinfo(parse_url(trim($option_image), PHP_URL_PATH));
                
                $path = version_compare(VERSION, '2', '>=') ? 'catalog/option/' : 'data/option/';
                
                if (!is_dir(DIR_IMAGE . $path)) {
                  mkdir(DIR_IMAGE . $path, 0777, true);
                }
                
                $opt_data['image'] = $path . urldecode($file_info['filename']) . '.' . $file_info['extension'];
                
                $copyError = $this->tool->copy_image(trim(str_replace(' ', '%20', $option_image)), DIR_IMAGE . $opt_data['image']);
                
                if ($copyError !== true) {
                  $this->tool->log(array(
                    'row' => $this->session->data['obui_current_line'],
                    'status' => 'error',
                    'title' => $this->language->get('warning'),
                    'msg' => $copyError,
                  ));
                  
                  $opt_data['image'] = '';
                }
              }
            }
            
            // create option value
            $this->db->query("UPDATE " . DB_PREFIX . "option_value SET image = '" . $this->db->escape(@html_entity_decode($opt_data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE option_value_id = '" . (int)$option_value_id . "'");
          }
        }
        // not exists - create
        else {
          $optCount = $this->db->query("SELECT COUNT(option_value_id) AS total FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int) $option_id . "'")->row;
          
          $opt_data = array();
          $opt_data['sort_order'] = $optCount['total'];
          $opt_data['image'] = '';
          
          // image download
          if ($option_image) {
            if (strpos($option_image, 'http') === false) {
              $opt_data['image'] = trim($option_image);
            } else {
              $file_info = pathinfo(parse_url(trim($option_image), PHP_URL_PATH));
              
              $path = version_compare(VERSION, '2', '>=') ? 'catalog/option/' : 'data/option/';
              
              if (!is_dir(DIR_IMAGE . $path)) {
                mkdir(DIR_IMAGE . $path, 0777, true);
              }
              
              $opt_data['image'] = $path . urldecode($file_info['filename']) . '.' . $file_info['extension'];
              
              $copyError = $this->tool->copy_image(trim(str_replace(' ', '%20', $option_image)), DIR_IMAGE . $opt_data['image']);
              
              if ($copyError !== true) {
                $this->tool->log(array(
                  'row' => $this->session->data['obui_current_line'],
                  'status' => 'error',
                  'title' => $this->language->get('warning'),
                  'msg' => $copyError,
                ));
                
                $opt_data['image'] = '';
              }
            }
          }
          
          // create option value
          $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(@html_entity_decode($opt_data['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$opt_data['sort_order'] . "'");

          $option_value_id = $this->db->getLastId();

          foreach ($languages as $language) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int) $language['language_id'] . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape(!empty($option_value_ml[$language['language_id']]) ? $option_value_ml[$language['language_id']] : $option_value) . "'");
          }
        }
        
        // @todo: check if option already assigned in product_option, if so skip it to not insert duplicate
        // format values for product
        if (in_array($option_type, array('select', 'radio', 'checkbox', 'image'))) {
          if (!isset($return_values[$option_name])) {
            $return_values[$option_name] = array(
              'type' => $option_type,
              'required' => $option_required,
              'product_option_value' => array(),
              'option_id' => $option_id,
            );
          }
          
          if (empty($option_price_prefix)) {
            $option_price_prefix = !empty($option_price) && ($option_price < 0) ? '-' : '+';
          }
          
          $return_values[$option_name]['product_option_value'][serialize($option_value).$option_weight] = array(
            'product_option_value_id' => $option_prod_opt_val_id,
            'option_value_id' => $option_value_id,
            'quantity' => !empty($option_quantity) ? $option_quantity : '',
            'subtract' => !empty($option_subtract) ? $option_subtract : '',
            'price' => !empty($option_price) ? abs($option_price) : '',
            'price_prefix' => $option_price_prefix ,
            'points' => !empty($option_points) ? abs($option_points) : '',
            'points_prefix' => !empty($option_points) && ($option_points < 0) ? '-' : '+',
            'weight' => !empty($option_weight) ? abs($option_weight) : '',
            'weight_prefix' => !empty($option_weight) && ($option_weight < 0) ? '-' : '+',
            'sku' => !empty($option_sku) ? $option_sku : '',
            'upc' => !empty($option_upc) ? $option_upc : '',
            'ean' => !empty($option_ean) ? $option_ean : '',
            'model' => !empty($option_model) ? $option_model : '',
            # custom_product_option_value #
          );
          
        } else {
          if (!isset($return_values[$option_name])) {
            $return_values[$option_name] = array(
              'type' => $option_type,
              'required' => $option_required,
              'option_id' => $option_id,
              'value' => $option_value,
            );
          }
        }
      }
    }
    
    return $return_values;
  }
  
  public function discountHandler($field, $config) {
    $return_values = $values_array = array();
    
    // advanced mode
    if (!empty($config['columns']['discountByCustomerGroup'])) {
      foreach ($config['columns']['discountByCustomerGroup'] as $customer_group_id => $groupData) {
        $return_values[] = array(
            'customer_group_id' => $customer_group_id,
            'quantity' => !empty($groupData['quantity']) ? $groupData['quantity'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['quantity']) ? $config['defaults']['discountByCustomerGroup'][$customer_group_id]['quantity'] : 99999),
            'priority' => !empty($groupData['priority']) ? $groupData['priority'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['priority']) ? $config['defaults']['discountByCustomerGroup'][$customer_group_id]['priority'] : ''),
            'price' => isset($groupData['price']) ? $groupData['price'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['price']) ? $config['defaults']['discountByCustomerGroup'][$customer_group_id]['price'] : ''),
            'date_start' => !empty($groupData['date_start']) ? $groupData['date_start'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
            'date_end' => !empty($groupData['date_end']) ? $groupData['date_end'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
          );
      }
    } else {
      // parse string mode
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
      
      foreach ($values_array as $value) {
        if (is_array($value)) {
          $customer_group_id = isset($value['customer_group_id']) ? $value['customer_group_id'] : 1;
          
          $return_values[] = array(
            'customer_group_id' => $customer_group_id,
            'quantity' => isset($value['quantity']) ? $value['quantity'] : '',
            'priority' => isset($value['priority']) ? $value['priority'] : '',
            'price' => isset($value['price']) ? $value['price'] : '',
            'date_start' => isset($value['date_start']) ? $value['date_start'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
            'date_end' => isset($value['date_end']) ? $value['date_end'] : (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
            # custom_discount_field #
          );
          
        // string mode with specific format
        } else if (is_string($value) && !empty($config['discount_format'])) {
          if (strpos($value, ':') !== false) {
            $values = explode(':', $value);
          } else {
            $values = (array) $value;
          }
          
          $currentValues = array();
          
          foreach ($config['discount_format'] as $k => $type) {
            $currentValues[$type] = isset($values[$k]) ? $values[$k] : '';
          }
          
          // set defaults
          if (empty($currentValues['priority'])) {
            $currentValues['priority'] = '0';
          }
          
          if (empty($currentValues['quantity'])) {
            $currentValues['quantity'] = '1';
          }
          
          if (empty($currentValues['date_start'])) {
            //$currentValues['date_start'] = '2000-01-01';
          }
          
          if (empty($currentValues['date_end'])) {
            //$currentValues['date_end'] = '2100-01-01';
          }
          
          // save final values
          if (!empty($currentValues['customer_group_id'])) {
            // defined customer group
            $return_values[] = $currentValues;
          } else {
            // all customer groups
            $this->loadCustomerGroups();
            
            foreach ($this->customer_groups as $customer_group_id) {
              $currentValues['customer_group_id'];
              $return_values[] = $currentValues;
            }
          }
        
        // autodetect string
        } else if (is_scalar($value)) {
          if (strpos($value, ':') !== false) {
            $values = explode(':', $value);
            /* formats:
            - price
            - price:date_end
            - price:date_start:date_end
            - qty:price:date_start:date_end
            - customer_group_id:qty:price:date_start:date_end
            - customer_group_id:qty:priority:price:date_start:date_end
            */
            
            # custom_discount_field_string #
            
            if (count($values) == 6) {
              $return_values[] = array(
                'customer_group_id' => $values[0],
                'quantity' => $values[1],
                'priority' => $values[2],
                'price' => $values[3],
                'date_start' => (!empty($values[4]) ? date('Y-m-d', strtotime($values[4])) : ''),
                'date_end' => (!empty($values[5]) ? date('Y-m-d', strtotime($values[5])) : ''),
              );
            } else if (count($values) == 5) {
              $return_values[] = array(
                'customer_group_id' => $values[0],
                'quantity' => $values[1],
                'priority' => 1,
                'price' => $values[2],
                'date_start' => (!empty($values[3]) ? date('Y-m-d', strtotime($values[3])) : ''),
                'date_end' => (!empty($values[4]) ? date('Y-m-d', strtotime($values[4])) : ''),
              );
            } else if (count($values) == 4) {
              $this->loadCustomerGroups();
            
              foreach ($this->customer_groups as $customer_group) {
                $return_values[] = array(
                  'customer_group_id' => $customer_group,
                  'quantity' => $values[0],
                  'priority' => 1,
                  'price' => $values[1],
                  'date_start' => (!empty($values[2]) ? date('Y-m-d', strtotime($values[2])) : ''),
                  'date_end' => (!empty($values[3]) ? date('Y-m-d', strtotime($values[3])) : ''),
                );
              }
            } else if (count($values) == 3) {
              $this->loadCustomerGroups();
            
              foreach ($this->customer_groups as $customer_group) {
                $return_values[] = array(
                  'customer_group_id' => $customer_group,
                  'quantity' => 999999,
                  'priority' => 1,
                  'price' => $values[0],
                  'date_start' => (!empty($values[1]) ? date('Y-m-d', strtotime($values[1])) : ''),
                  'date_end' => (!empty($values[2]) ? date('Y-m-d', strtotime($values[2])) : ''),
                );
              }
            } else if (count($values) == 2) {
              $this->loadCustomerGroups();
            
              foreach ($this->customer_groups as $customer_group) {
                $return_values[] = array(
                  'customer_group_id' => $customer_group,
                  'quantity' => 999999,
                  'priority' => 1,
                  'price' => $values[0],
                  'date_start' => (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
                  'date_end' => $values[1],
                );
              }
            }
          } else {
            $this->loadCustomerGroups();
            
            foreach ($this->customer_groups as $customer_group_id) {
              $return_values[] = array(
                'customer_group_id' => $customer_group_id,
                'quantity' => 999999,
                'priority' => 1,
                'price' => $value,
                'date_start' => (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
                'date_end' => (!empty($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['discountByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
              );
            }
          }
        }
      }
    }
    
    foreach ($return_values as $key => $item) {
      if (!is_numeric($item['price'])) {
        $this->tool->log(array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => $this->language->get('warning_discount_not_numeric'),
        ));
        
        unset($return_values[$key]);
      }
    }
    
    if ($this->simulation) {
      $return_simu = array();
      
      foreach ($return_values as $item) {
        if (isset($item['date_start'])) {
          $return_simu[] =  $item['quantity'] . ' : ' . round($item['price'], 2) . ' (' . $item['date_start'] . ' <b>></b> ' .  $item['date_end'] . ')';
        }
      }
      
      return $return_simu;
    }
    
    return $return_values;
  }
  
  public function specialHandler($field, $config) {
    $return_values = $values_array = array();
    
    // advanced mode
    if (!empty($config['columns']['specialByCustomerGroup'])) {
      foreach ($config['columns']['specialByCustomerGroup'] as $customer_group_id => $groupData) {
        if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($groupData['price'])) {
          // same price, do not add special
          $hasSamePrice = true;
          continue;
        }
        
        $return_values[] = array(
            'customer_group_id' => $customer_group_id,
            'priority' => !empty($groupData['priority']) ? $groupData['priority'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['priority']) ? $config['defaults']['specialByCustomerGroup'][$customer_group_id]['priority'] : ''),
            'price' => isset($groupData['price']) ? $groupData['price'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['price']) ? $config['defaults']['specialByCustomerGroup'][$customer_group_id]['price'] : ''),
            'date_start' => !empty($groupData['date_start']) ? $groupData['date_start'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
            'date_end' => !empty($groupData['date_end']) ? $groupData['date_end'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
          );
      }
    } else {
      // parse string mode
      if (empty($config['columns'][$field])) {
        return $return_values;
      }
      
      foreach ((array) $config['columns'][$field] as $value) {
        if (!$value) {
          continue;
        } else if (is_array($value) && !empty($value['gkd_formatted'])) {
          $values_array[] = $value;
        } else if (is_array($value)) {
          $values_array = array_merge($values_array, $value);
        } else if (!empty($config['multiple_separator'])) {
          $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
        } else {
          $values_array[] = $value;
        }
      }
      
      /*
      $customer_group_id = 1;
      
      if (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) {
        $defaultStartDate = date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']));
      } else {
        $defaultStartDate = '2000-01-01';
      }
      
      if (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) {
        $defaultEndDate = date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']));
      } else {
        $defaultEndDate = '2100-01-01';
      }
      */
      
      foreach ($values_array as &$value) {
        if (is_array($value) && !empty($value['gkd_formatted'])) {
          if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($value['price'])) {
            // same price, do not add special
            $hasSamePrice = true;
            continue;
          }
            
            $this->loadCustomerGroups();
          
            if (isset($value['customer_group_id']) && $value['customer_group_id'] !== '') {
              $customer_group_id = $value['customer_group_id'];
              $return_values[] = array(
                'customer_group_id' => $value['customer_group_id'],
                'priority' => !empty($value['priority']) ? $value['priority'] : 1,
                'price' => $this->tool->floatValue($value['price']),
                'date_start' => !empty($value['date_start']) ? $value['date_start'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
                'date_end' => !empty($value['date_end']) ? $value['date_end'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
              );
            } else {
              foreach ($this->customer_groups as $customer_group_id) {
                $return_values[] = array(
                  'customer_group_id' => $customer_group_id,
                  'priority' => !empty($value['priority']) ? $value['priority'] : 1,
                  'price' => $this->tool->floatValue($value['price']),
                  'date_start' => !empty($value['date_start']) ? $value['date_start'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
                  'date_end' => !empty($value['date_end']) ? $value['date_end'] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
                );
              }
            }
        // string mode with specific format
        } else if (is_string($value) && !empty($config['special_format'])) {
          if (strpos($value, ':') !== false) {
            $values = explode(':', $value);
          } else {
            $values = (array) $value;
          }
          
          $currentValues = array();
          
          foreach ($config['special_format'] as $k => $type) {
            $currentValues[$type] = isset($values[$k]) ? $values[$k] : '';
          }
          
          // set defaults
          if (empty($currentValues['priority'])) {
            $currentValues['priority'] = '0';
          }
          
          if (empty($currentValues['date_start'])) {
            //$currentValues['date_start'] = '2000-01-01';
          }
          
          if (empty($currentValues['date_end'])) {
            //$currentValues['date_end'] = '2100-01-01';
          }
          
          // save final values
          if (!empty($currentValues['customer_group_id'])) {
            // defined customer group
            $return_values[] = $currentValues;
          } else {
            // all customer groups
            $this->loadCustomerGroups();
            
            foreach ($this->customer_groups as $customer_group_id) {
              $currentValues['customer_group_id'];
              $return_values[] = $currentValues;
            }
          }
        
        // string mode autodetect
        } else if (is_string($value) && strpos($value, ':') !== false) {
          $values = explode(':', $value);
          
          if (count($values) == 5) {
            if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($values[2])) {
            // same price, do not add special
            $hasSamePrice = true;
            continue;
          }

            $customer_group_id = $values[0];
            
            $return_values[] = array(
              'customer_group_id' => $values[0],
              'priority' => $values[1],
              'price' => $this->tool->floatValue($values[2]),
              'date_start' => !empty($values[3]) ? $values[3] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
              'date_end' => !empty($values[4]) ? $values[4] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
            );
          } else if (count($values) == 4) {
            if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($values[2])) {
              // same price, do not add special
              $hasSamePrice = true;
              continue;
            }

            $customer_group_id = $values[0];
            
            $return_values[] = array(
              'customer_group_id' => $values[0],
              'priority' => $values[1],
              'price' => $this->tool->floatValue($values[2]),
              'date_start' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
              'date_end' => !empty($values[3]) ? $values[3] : (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
            );
          } else if (count($values) == 3) {
            if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($values[2])) {
              // same price, do not add special
              $hasSamePrice = true;
              continue;
            }
            
            $customer_group_id = $values[0];
            
            $return_values[] = array(
              'customer_group_id' => $values[0],
              'priority' => $values[1],
              'price' => $this->tool->floatValue($values[2]),
              'date_start' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
              'date_end' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
            );
          } else if (count($values) == 2) {
            if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($values[1])) continue; {
              // same price, do not add special
              $hasSamePrice = true;
              continue;
            }
            
            $customer_group_id = $values[0];
            
            $return_values[] = array(
              'customer_group_id' => $values[0],
              'priority' => 1,
              'price' => $this->tool->floatValue($values[1]),
              'date_start' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
              'date_end' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
            );
          }
        } else if (is_scalar($value)) {
          $this->loadCustomerGroups();
          
          foreach ($this->customer_groups as $customer_group_id) {
            if (isset($config['columns']['price']) && $config['columns']['price'] == $this->tool->floatValue($value)) {
              // same price, do not add special
              $hasSamePrice = true;
              continue;
            }
            
            $return_values[] = array(
              'customer_group_id' => $customer_group_id,
              'priority' => 1,
              'price' => $this->tool->floatValue($value),
              'date_start' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_start'])) : ''),
              'date_end' => (!empty($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end']) ? date('Y-m-d', strtotime($config['defaults']['specialByCustomerGroup'][$customer_group_id]['date_end'])) : ''),
            );
          }
        }
      }
    }
    
    $return_values = array_filter($return_values, array($this, 'filterEmptyPrice'));
    
    if ($this->simulation) {
      $return_simu = array();
      
      foreach ($return_values as $item) {
        $return_simu[] = round($item['price'], 2) . ' ('.$item['date_start'] . ' <b>></b> ' .  $item['date_end'] . ')';
      }
      
      return $return_simu;
    }
    
    if (empty($return_values) && !empty($hasSamePrice)) {
      return 'reset';
    }
    
    return $return_values;
  }
  
  public function filterHandler($field, $config) {
    $return_values = $values_array = $column_bindings = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    foreach ((array) $config['columns'][$field] as $key => $value) {
      # custom_filter_handler
      
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
        
        foreach ($value as $arr_val) {
          $column_bindings[] = $config['columns_bindings'][$field][$key];
        }
      } else if (!empty($config['multiple_separator']) && strpos($value, $config['multiple_separator']) !== false) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
        
        foreach (explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value) as $arr_val) {
          $column_bindings[] = isset($config['columns_bindings'][$field][$key]) ? $config['columns_bindings'][$field][$key] : '';
        }
      } else {
        $values_array = array_merge($values_array, (array) $value);
        
        $column_bindings[] = isset($config['columns_bindings'][$field][$key]) ? $config['columns_bindings'][$field][$key] : '';
      }
    }
    
    $values_array = array_filter($values_array);

    $this->load->model('localisation/language');
    $languages = $this->model_localisation_language->getLanguages();
    
    foreach ($values_array as $current_key => &$value) {
      // xml advanced values
      if ($this->filetype == 'xml' && !empty($config['filter_fields']['name'][$this->config->get('config_language_id')])) {
        foreach (array('group', 'name') as $attribute_type) {
          foreach ($languages as $language) {
            if (!empty($config['filter_fields'][$attribute_type][$language['language_id']])) {
              ${'filter_'.$attribute_type.'_ml'}[$language['language_id']] = $this->getArrayPath($value, $config['filter_fields'][$attribute_type][$language['language_id']]);
            }
          }
          
          if (!empty(${'filter_'.$attribute_type.'_ml'})) {
            ${'filter_'.$attribute_type} = reset(${'filter_'.$attribute_type.'_ml'});
          }
        
          // set defaults if empty
          if (empty(${'filter_'.$attribute_type})) {
            foreach ($languages as $language) {
              if ($attribute_type == 'group') {
                ${'filter_'.$attribute_type.'_ml'}[$language['language_id']] = 'Default';
              } else {
                ${'filter_'.$attribute_type.'_ml'}[$language['language_id']] = '';
              }
            }
            
            if (!empty(${'filter_'.$attribute_type.'_ml'})) {
              ${'filter_'.$attribute_type} = reset(${'filter_'.$attribute_type.'_ml'});
            } else {
              ${'filter_'.$attribute_type} = ($attribute_type == 'group') ? 'Default' : '';
            }
          }
        }
      // import by filter id
      } else if (!empty($config['filter_identifier']) && is_numeric($value)) {
        if ($this->simulation) {
          $filter = $this->db->query("SELECT fd.name, fgd.name AS 'group' FROM " . DB_PREFIX . "filter_description fd LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fgd.filter_group_id = fd.filter_group_id) WHERE fd.filter_id = '" . (int) $value . "'")->row;
          
          if (isset($filter['group'])) {
            $filter_group = $filter['group'];
          }
          
          if (isset($filter['name'])) {
            $filter_name = $filter['name'];
          }
        } else {
          $return_values[] = $value;
        }
      } else if (is_scalar($value)) {
        if (strpos($value, ':') !== false) {
          $values = explode(':', $value);
        }
        
        if (!isset($values)) {
          // get column header
          $column_headers = (array) json_decode(base64_decode($config['column_headers']));
          
          if (!empty($column_headers[ $column_bindings[$current_key] ])) {
            if ($this->filetype == 'xml') {
              $filter_group = basename($column_headers[ $column_bindings[$current_key] ]);
            } else {
              $filter_group = $column_headers[ $column_bindings[$current_key] ];
            }
            
            $filter_name = $value;
          } else {
            continue;
          }
        } else if (isset($values) && count($values) == 2) {
          $filter_group = $values[0];
          $filter_name = $values[1];
        } else {
          // too much parts ?
          continue;
        }
      }
      
      if (!empty($return_values)) {
        //continue; ??
      }
      
      if (!isset($filter_name) || $filter_name === '') {
        continue;
      }
      
      if (!isset($filter_group) || $filter_group === '') {
        continue;
      }
      
      if ($this->simulation) {
        if (trim($filter_name)) {
          $return_values[] = $filter_group .' <b>></b> '. $filter_name;
        }
        
        continue;
      }
      
      $filter_group = $this->request->clean($filter_group);
      $filter_name = $this->request->clean($filter_name);
      
      $filter = $this->db->query("SELECT fd.filter_id FROM " . DB_PREFIX . "filter_description fd LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fgd.filter_group_id = fd.filter_group_id) WHERE fgd.name = '" . $this->db->escape($filter_group) . "' AND fd.name = '" . $this->db->escape($filter_name) . "'")->row;
      
      // filter exists ?
      if (!empty($filter['filter_id'])) {
        $filter_id = $filter['filter_id'];
      }
      // not exists - create
      else {
        $filter_data = array();
        $filter_data['sort_order'] = 1;
        
        $filter_group_query = $this->db->query("SELECT filter_group_id FROM " . DB_PREFIX . "filter_group_description WHERE name = '" . $this->db->escape($filter_group) . "'")->row;
        
        // group exists - get id
        if (!empty($filter_group_query['filter_group_id'])) {
          $filter_data['filter_group_id'] = $filter_group_query['filter_group_id'];
        } 
        //  group not exists - create
        else {
          $filter_group_data = array();
          $filter_group_data['sort_order'] = 0;
          
          foreach ($languages as $language) {
            $filter_group_data['filter_group_description'][$language['language_id']] = !empty($filter_group_ml[$language['language_id']]) ? $filter_group_ml[$language['language_id']] : $filter_group;
          }
          
          $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group` SET sort_order = '" . (int)$filter_group_data['sort_order'] . "'");

          $filter_data['filter_group_id'] = $this->db->getLastId();

          foreach ($languages as $language) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "filter_group_description SET filter_group_id = '" . (int)$filter_data['filter_group_id'] . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($filter_group_data['filter_group_description'][$language['language_id']]) . "'");
          }
        }
        
        // create filter
        foreach ($languages as $language) {
          $filter_data['filter_description'][$language['language_id']] = !empty($filter_name_ml[$language['language_id']]) ? $filter_name_ml[$language['language_id']] : $filter_name;
        }
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "filter SET filter_group_id = '" . (int)$filter_data['filter_group_id'] . "', sort_order = 0");

				$filter_id = $this->db->getLastId();

				foreach ($languages as $language) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "filter_description SET filter_id = '" . (int)$filter_id . "', language_id = '" . (int)$language['language_id'] . "', filter_group_id = '" . (int)$filter_data['filter_group_id'] . "', name = '" . $this->db->escape($filter_data['filter_description'][$language['language_id']]) . "'");
				}
      }
      
      // format values for product - only if not empty value
      if (trim($filter_name)) {
        $return_values[] = $filter_id;
      }
    }
    
    return array_unique($return_values);
  }
  
  public function attributeHandler($field, $config, $line) {
    $attributes_array = $return_values = $values_array = $header_keys = array();
    
    if (empty($config['columns'][$field]) && !empty($config['attribute_fields'])) {
      $values_array[] = '';
      $config['columns'][$field] = array();
    } else if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    foreach ((array) $config['columns'][$field] as $key => $value) {
      if (is_string($value) && strpos($value, '</table>') !== false) {
        $dom = new DOMDocument;
        @$dom->loadHTML($value);
        
        foreach ($dom->getElementsByTagName('tr') as $tr) {
          $td = array();
          foreach ($tr->getElementsByTagName('td') as $node) {
            $td[] = trim(preg_replace('/\s+/u', ' ', strip_tags($node->nodeValue)), " \t");
          }
          
          if (count($td) == 1) {
            $values_array[] = array(
              'group' => 'Default',
              'name' => 'Attribute',
              'value' => $td[0],
            );
          } else if (count($td) == 2) {
            $values_array[] = array(
              'group' => 'Default',
              'name' => $td[0],
              'value' => $td[1],
            );
          } else if (count($td) == 3) {
            $values_array[] = array(
              'group' => $td[0],
              'name' => $td[1],
              'value' => $td[2],
            );
          }
        }
      } else if (is_string($value) && strpos($value, '</li>') !== false) {
        $dom = new DOMDocument;
        @$dom->loadHTML($value);
        
        foreach ($dom->getElementsByTagName('li') as $node) {
          $values_array[] = $node->nodeValue;
        }
      } else if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
        foreach($value as $v) {
          $header_keys[] = $key;
        }
      } else if ($value && !empty($config['multiple_separator'])) {
        $value = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value);
        $values_array = array_merge($values_array, $value);
        foreach($value as $v) {
          $header_keys[] = $key;
        }
      // } else if ($value == '' && !empty($config['attribute_fields']['value'][$this->config->get('config_language_id')])) {
      } else {
        $values_array = array_merge($values_array, (array) $value);
        //$values_array[$key] = $value;
        $header_keys[] = $key;
      }
    }
    //$values_array = array_filter($values_array); // do not filter, else it make the count wrong (for headers)
    
    $this->load->model('localisation/language');
    $languages = $this->model_localisation_language->getLanguages();
    if (!empty($config['extra_fields'])) {
      $extraFields = array_flip(explode(',', $config['extra_fields']));
    } else {
      $extraFields = array();
    }
    
    // foreach ($values_array as $current_key => &$value) {
    //for ($i = 0; $i <= count($values_array); $i++) {
    for ($i = 0; $i < count($values_array); ++$i) {
      $current_key = $i;
      
      if (!isset($values_array[$i])) continue;
      
      $value = $values_array[$i];
      
      if (isset($header_keys[$current_key])) {
        $header_key = $header_keys[$current_key];
      } else {
        $header_key = 0;
      }
      
      // csv advanced values
      if ((!is_scalar($values_array[$i]) || $values_array[$i] == '') && !in_array($this->filetype, array('xml', 'json')) && !empty($config['attribute_fields'])) {
        foreach ($config['attribute_fields'] as $attrFieldKey => $attribute_fields) {
          foreach (array('group', 'name', 'value') as $attribute_type) {
            if (!isset(${'multi_attribute_'.$attribute_type.'_ml'})) {
              ${'multi_attribute_'.$attribute_type.'_ml'} = array();
            }
            
            foreach ($languages as $language) {
              if (!empty($attribute_fields[$attribute_type][$language['language_id']])) {
                // use . or [current] to have main attribute value
                if (($attribute_fields[$attribute_type][$language['language_id']] == '[current]' || $attribute_fields[$attribute_type][$language['language_id']] == '.' || $attribute_fields[$attribute_type][$language['language_id']] == '') && is_string($value)) {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $value;
                  
                // use [attributes] to ...
                } else if ($attribute_fields[$attribute_type][$language['language_id']] == '[attributes]') {
                  if ($attribute_type == 'name') {
                    foreach ($value as $k => $v) {
                      if (!$v) continue;
                      if (!strpos($k, '@')) {
                        $remove_key = $k.'@';
                      }
                      $values_array[] = $attribute_fields['group'][$language['language_id']].':'.str_replace('@', '', strstr($k, '@')).':'.$v;
                    }
                    continue 2;
                  }
                
                // if array of values process each one
                } else if (isset($line[$attribute_fields[$attribute_type][$language['language_id']]]) && is_array($line[$attribute_fields[$attribute_type][$language['language_id']]])) {
                  // if we are in value or the value parameter is array then process other fields same way
                  if ($attribute_type == 'value' || is_array($line[$attribute_fields['value'][$language['language_id']]])) {
                    foreach ($line[$attribute_fields[$attribute_type][$language['language_id']]] as $k => $v) {
                      ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k] = $v;
                    }
                  } else {
                    // group or name refers to an array, try to get key corresponding to current value key
                    if (isset($line[$attribute_fields[$attribute_type][$language['language_id']]][$current_key])) {
                      ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']][$current_key] = $line[$attribute_fields[$attribute_type][$language['language_id']]][$current_key];
                    }
                  }
                
                // use @ to ...
                } else if (strpos($attribute_fields[$attribute_type][$language['language_id']], '@')) {
                  if (isset($line[$attribute_fields[$attribute_type][$language['language_id']]]) && is_array($line[$attribute_fields[$attribute_type][$language['language_id']]])) {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $line[$attribute_fields[$attribute_type][$language['language_id']]][$current_key];
                  } else if (isset($line[$attribute_fields[$attribute_type][$language['language_id']]])) {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $line[$attribute_fields[$attribute_type][$language['language_id']]];
                  }
                  
                // get path
                } else {
                  if ($attribute_type == 'value' && strpos($attribute_fields['value'][$language['language_id']], '+')) {
                    foreach (explode('+', $attribute_fields[$attribute_type][$language['language_id']]) as $k => $vals) {
                      foreach (explode('~', $vals) as $v) {
                        if (substr($v, 0, 1) == "'" && substr($v, -1) == "'") {
                          $resVal = substr($v, 1, -1);
                        } else {
                          $resVal = $this->getArrayPath(is_array($value) ? $value : $line, $v);
                        }
                        
                        if ($resVal !== '') {
                          if (!isset(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k])) {
                            ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k] = '';
                          }
                          
                          ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k] .= $resVal;
                        }
                      }
                      
                      if (isset(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k]) && trim(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k]) == '') {
                        unset(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey.$k]);
                      }
                    }
                  } else {
                    //${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $this->getArrayPath(is_array($value) ? $value : $line, $attribute_fields[$attribute_type][$language['language_id']]);
                    
                    ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey] = '';
                    
                    //${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = '';
                    
                    // use ~ to combine multiple values in one
                    foreach (explode('~', $attribute_fields[$attribute_type][$language['language_id']]) as $k => $v) {
                      $resVal = $this->getArrayPath(is_array($value) ? $value : $line, $v);
                      
                      //${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] .= $resVal;
                      ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey] .= $resVal;
                    }
                  }
                }
              } else {
                if ($attribute_type == 'group') {
                  ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey] = !empty($config['attribute_fields_default'][$attrFieldKey][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attrFieldKey][$attribute_type][$language['language_id']] : 'Default';
                } else {
                  ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$attrFieldKey] = isset($config['attribute_fields_default'][$attrFieldKey][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attrFieldKey][$attribute_type][$language['language_id']] : '';
                }
              }
            }
            
            if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
              ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
            }
          
            // set defaults if empty
            if (empty(${'attribute_'.$attribute_type})) {
              foreach ($languages as $language) {
                if (!empty($config['attribute_fields_default'][$attribute_type][$language['language_id']])) {
                  if ($attribute_type == 'group') {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = isset($config['attribute_fields_default'][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attribute_type][$language['language_id']] : 'Default';
                  } else {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = isset($config['attribute_fields_default'][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attribute_type][$language['language_id']] : '';
                  }
                }
              }
              
              if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
                ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
              } else {
                ${'attribute_'.$attribute_type} = ($attribute_type == 'group') ? 'Default' : '';
              }
            }
          }
        }
      
      // xml advanced values
      } else if ((!is_scalar($values_array[$i]) || $values_array[$i] == '') && in_array($this->filetype, array('xml', 'json')) && !empty($config['attribute_fields']['value'][$this->config->get('config_language_id')])) {
        foreach (array('group', 'name', 'value') as $attribute_type) {
          ${'multi_attribute_'.$attribute_type.'_ml'} = array();
          
          foreach ($languages as $language) {
            if (!empty($config['attribute_fields'][$attribute_type][$language['language_id']])) {
              // use . or [current] to have main attribute value
              if (($config['attribute_fields'][$attribute_type][$language['language_id']] == '[current]' || $config['attribute_fields'][$attribute_type][$language['language_id']] == '.' || $config['attribute_fields'][$attribute_type][$language['language_id']] == '') && is_string($value)) {
                ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $value;
                
              // use [attributes] to ...
              } else if ($config['attribute_fields'][$attribute_type][$language['language_id']] == '[attributes]') {
                if ($attribute_type == 'name') {
                  foreach ($value as $k => $v) {
                    if (!$v) continue;
                    if (!strpos($k, '@')) {
                      $remove_key = $k.'@';
                    }
                    $values_array[] = $config['attribute_fields']['group'][$language['language_id']].':'.str_replace('@', '', strstr($k, '@')).':'.$v;
                  }
                  continue 2;
                }
              
              // if array of values process each one
              } else if (isset($line[$config['attribute_fields'][$attribute_type][$language['language_id']]]) && is_array($line[$config['attribute_fields'][$attribute_type][$language['language_id']]])) {
                // if we are in value or the value parameter is array then process other fields same way
                if ($attribute_type == 'value' || is_array($line[$config['attribute_fields']['value'][$language['language_id']]])) {
                  foreach ($line[$config['attribute_fields'][$attribute_type][$language['language_id']]] as $k => $v) {
                    ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k] = $v;
                  }
                } else {
                  // group or name refers to an array, try to get key corresponding to current value key
                  if (isset($line[$config['attribute_fields'][$attribute_type][$language['language_id']]][$current_key])) {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']][$current_key] = $line[$config['attribute_fields'][$attribute_type][$language['language_id']]][$current_key];
                  }
                }
              
              // if array of values process each one [extra field mode]
              } else if (isset($extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]) && isset($line['__extra_field_'.$extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]]) && is_array($line['__extra_field_'.$extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]])) {
                // if we are in value or the value parameter is array then process other fields same way
                if ($attribute_type == 'value' || is_array($line['__extra_field_'.$extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]])) {
                  foreach ($line['__extra_field_'.$extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]] as $k => $v) {
                    ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k] = $v;
                  }
                } else {
                  // group or name refers to an array, try to get key corresponding to current value key
                  if (isset($line['__extra_field_'.$extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]][$current_key])) {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']][$current_key] = $line['__extra_field_'.$extraFields[$config['attribute_fields'][$attribute_type][$language['language_id']]]][$current_key];
                  }
                }
              
              // use @ to ...
              } else if (strpos($config['attribute_fields'][$attribute_type][$language['language_id']], '@')) {
                if (isset($line[$config['attribute_fields'][$attribute_type][$language['language_id']]]) && is_array($line[$config['attribute_fields'][$attribute_type][$language['language_id']]])) {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $line[$config['attribute_fields'][$attribute_type][$language['language_id']]][$current_key];
                } else if (isset($line[$config['attribute_fields'][$attribute_type][$language['language_id']]])) {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $line[$config['attribute_fields'][$attribute_type][$language['language_id']]];
                }
                
              // get path
              } else {
                if ($attribute_type == 'value' && strpos($config['attribute_fields']['value'][$language['language_id']], '+')) {
                  foreach (explode('+', $config['attribute_fields'][$attribute_type][$language['language_id']]) as $k => $vals) {
                    foreach (explode('~', $vals) as $v) {
                      if (substr($v, 0, 1) == "'" && substr($v, -1) == "'") {
                        $resVal = substr($v, 1, -1);
                      } else {
                        $resVal = $this->getArrayPath(is_array($value) ? $value : $line, $v);
                      }
                      
                      if ($resVal !== '') {
                        if (!isset(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k])) {
                          ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k] = '';
                        }
                        
                        ${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k] .= $resVal;
                      }
                    }
                    
                    if (isset(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k]) && trim(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k]) == '') {
                      unset(${'multi_attribute_'.$attribute_type.'_ml'}[$language['language_id']][$k]);
                    }
                  }
                } else {
                  //${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $this->getArrayPath(is_array($value) ? $value : $line, $config['attribute_fields'][$attribute_type][$language['language_id']]);
                  
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = '';
                  
                  // use ~ to combine multiple values in one
                  foreach (explode('~', $config['attribute_fields'][$attribute_type][$language['language_id']]) as $k => $v) {
                    $resVal = $this->getArrayPath(is_array($value) ? $value : $line, $v);
                    if (is_array($resVal)) {
                      $resVal = implode(', ', $resVal);
                    }
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] .= $resVal;
                  }
                }
              }
            }
          }
          
          if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
            ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
          }
        
          // set defaults if empty
          if (empty(${'attribute_'.$attribute_type})) {
            foreach ($languages as $language) {
              if (!empty($config['attribute_fields_default'][$attribute_type][$language['language_id']])) {
                if ($attribute_type == 'group') {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = isset($config['attribute_fields_default'][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attribute_type][$language['language_id']] : 'Default';
                } else {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = isset($config['attribute_fields_default'][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attribute_type][$language['language_id']] : '';
                }
              }
            }
            
            if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
              ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
            } else {
              ${'attribute_'.$attribute_type} = ($attribute_type == 'group') ? 'Default' : '';
            }
          }
        }
      } else if (is_array($value)) {
        $attribute_group = isset($value['group']) ? $value['group'] : 'Default';
        $attribute_name = isset($value['name']) ? $value['name'] : 'Attribute';
        $attribute_value = isset($value['value']) ? $value['value'] : '';
      } else if (is_scalar($value)) {
        if (strpos($value, ':') !== false) {
          $values = explode(':', $value);
        }
        
        if (!isset($values)) {
          // get column header
          $column_headers = (array) json_decode(base64_decode($config['column_headers']));
          
          //if ($this->filetype == 'xml' &&
          if (isset($config['columns_bindings'][$field][$header_key]) && !empty($column_headers[ $config['columns_bindings'][$field][$header_key] ])) {
            $attribute_group = 'Default';
            if ($this->filetype == 'xml') {
              $attribute_name = basename($column_headers[ $config['columns_bindings'][$field][$header_key] ]);
            } else {
              $attribute_name = $column_headers[ $config['columns_bindings'][$field][$header_key] ];
            }
            
            if (strpos($attribute_name, ':')) {
              $attrData = explode(':', $attribute_name);
              
              if (isset($attrData[2])) {
                $attribute_group = $attrData[1];
                $attribute_name = $attrData[2];
              } else {
                $attribute_name = $attrData[1];
              }
            }
            
            $attribute_value = $value;
          } else {
            $attribute_group = 'Default';
            $attribute_name = 'Attribute';
            $attribute_value = $value;
          }
          
          // get default names to see if there is any custom binding
          foreach (array('group', 'name', 'value') as $attribute_type) {
            if (isset($config['attribute_fields_default'][$attribute_type]) && !empty(array_filter($config['attribute_fields_default'][$attribute_type]))) {
              foreach ($languages as $language) {
                if (!empty($config['attribute_fields_default'][$attribute_type][$language['language_id']])) {
                  $custom_data = explode('|', $config['attribute_fields_default'][$attribute_type][$language['language_id']]);
                  
                  if (!empty($custom_data[$header_key])) {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $custom_data[$header_key];
                  } else if (!empty($custom_data[0])) {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $custom_data[0];
                  } else {
                    ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = ${'attribute_'.$attribute_type};
                  }
                }
              }
              
              if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
                ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
              } else {
                ${'attribute_'.$attribute_type} = ($attribute_type == 'group') ? 'Default' : '';
              }
            }
          }
        } else if (!empty($config['attr_mode']) || count($values) > 3) {
          preg_match('/(.+?):(.+)/', $value, $values);
          
          $attribute_group = 'Default';
          $attribute_name = $values[1];
          $attribute_value = $values[2];
        } else if (count($values) == 2) {
          $attribute_group = 'Default';
          $attribute_name = $values[0];
          $attribute_value = $values[1];
        } else if (count($values) == 3) {
          $attribute_group = $values[0] ? $values[0] : 'Default';
          $attribute_name = $values[1];
          $attribute_value = $values[2];
        } else {
          // too much parts ?
          continue;
        }
      } else {
        continue;
      }
      
      $attr_array = array();
      
      foreach (array('group', 'name', 'value') as $attribute_type) {
        $attr_array['attribute_'.$attribute_type] = isset(${'attribute_'.$attribute_type}) ? ${'attribute_'.$attribute_type} : '';
        $attr_array['attribute_'.$attribute_type.'_ml'] = isset(${'attribute_'.$attribute_type.'_ml'}) ? ${'attribute_'.$attribute_type.'_ml'} : '';
      }
      
      if (!empty($multi_attribute_value_ml) && is_array($multi_attribute_value_ml)) {
        foreach (reset($multi_attribute_value_ml) as $k => $multiAttr) {
          foreach (array('group', 'name', 'value') as $sub_type) {
            $attr_array['attribute_'.$sub_type.'_ml'] = array();
            
            foreach ($languages as $language) {
              if (isset(${'multi_attribute_'.$sub_type.'_ml'}[$language['language_id']][$k])) {
                $attr_array['attribute_'.$sub_type.'_ml'][$language['language_id']] = ${'multi_attribute_'.$sub_type.'_ml'}[$language['language_id']][$k];
              }
            }
            
            if (reset($attr_array['attribute_'.$sub_type.'_ml']) !== false) {
              $attr_array['attribute_'.$sub_type] = reset($attr_array['attribute_'.$sub_type.'_ml']);
            }
          }
          
          $attributes_array[] = $attr_array;
          }
      } else {
        $attributes_array[] = $attr_array;
      }
    }
    
    /*
    // attribute group binding
    switch ($attribute_name) {
      case 'size_for_cloth': $attribute_group = 'Group 1'; break;
      case 'color_for_cloth': $attribute_group = 'Group 2'; break;
    }
    
    // attribute name binding
    switch ($attribute_name) {
      case 'size_for_cloth': $attribute_name = 'size'; break;
      case 'color_for_cloth': $attribute_name = 'color'; break;
    }
    */
    
    // combine duplicates
    if (true) {
      foreach ($attributes_array as $current_key => &$attr) {
        foreach ($attributes_array as $subkey => $subAttr) {
          if ($subkey != $current_key && $attr['attribute_group'] == $subAttr['attribute_group'] && $attr['attribute_name'] == $subAttr['attribute_name'] && $attr['attribute_value'] != $subAttr['attribute_value']) {
            $attributes_array[$current_key]['attribute_value'] = $attr['attribute_value'] . ', ' . $subAttr['attribute_value'];
            
            if (is_array($attr['attribute_value_ml'])) {
              foreach ($attr['attribute_value_ml'] as $language_id => &$attrValMl) {
                if (isset($subAttr['attribute_value_ml'][$language_id])) {
                  $attrValMl = $attrValMl . ', ' . $subAttr['attribute_value_ml'][$language_id];
                }
              }
            }
            
            unset($attributes_array[$subkey]);
          }
        }
      }
    }
    
    foreach ($attributes_array as $current_key => $attributeValues) {
      foreach ($attributeValues as $attName => $attVal) {
        ${$attName} = $attVal;
      }
      
      if (!empty($config['filters_from_attributes'])) {
        $config['columns']['product_filter'][] = $attribute_name .':'. $attribute_value;
      }
      
      if ($attribute_group == '' || $attribute_name == '' || $attribute_value == '') {
        continue;
      }
      
      if ($this->simulation) {
        if (trim($attribute_value)) {
          
          if (is_numeric($attribute_name)) {
            $attrExists = $this->db->query("SELECT attribute_id, name FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . $this->db->escape($attribute_name) . "'")->row;
            
            if (empty($attrExists['attribute_id'])) {
              $this->tool->log(array(
                'row' => $this->session->data['obui_current_line'],
                'status' => 'error',
                'title' => $this->language->get('warning'),
                'msg' => 'Attribute ID not found: '.$attribute_name,
              ));
              
              continue;
            }
          } else {
            $attrExists = $this->db->query("SELECT attribute_id, name FROM " . DB_PREFIX . "attribute_description WHERE name = '" . $this->db->escape($attribute_name) . "'")->row;
          }
          
          if (!empty($attrExists['attribute_id'])) {
            $attribute_name = $attrExists['name'];
          }
          
          $return_values[] = $attribute_group .' <b>></b> '. $attribute_name .' <b>></b> '. $attribute_value;
        }
        
        continue;
      }
      
      if (is_numeric($attribute_name)) {
        $attrExists = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . $this->db->escape($attribute_name) . "'")->row;
      } else {
        $attrExists = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE name = '" . $this->db->escape($attribute_name) . "'")->row;
      }
      
      // attribute exists ?
      if (!empty($attrExists['attribute_id'])) {
        $attribute_id = $attrExists['attribute_id'];
        
        $checkAttrTable = true;
      }
      // not exists and is numeric - trigger error
      else if (is_numeric($attribute_name)) {
        $this->tool->log(array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => 'Attribute ID not found: '.$attribute_name,
        ));
      }
      // not exists - create
      else {
        $attr_data = array();
        $attr_data['sort_order'] = 1;
        
        $attr_group = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name = '" . $this->db->escape($attribute_group) . "'")->row;
        
        // group exists - get id
        if (!empty($attr_group['attribute_group_id'])) {
          $attr_data['attribute_group_id'] = $attr_group['attribute_group_id'];
        } 
        //  group not exists - create
        else {
          $attr_group_data = array();
          $attr_group_data['sort_order'] = 1;
          
          foreach ($languages as $language) {
            $attr_group_data['attribute_group_description'][$language['language_id']]['name'] = !empty($attribute_group_ml[$language['language_id']]) ? $attribute_group_ml[$language['language_id']] : $attribute_group;
          }
          
          if (version_compare(VERSION, '2', '>=')) {
            $this->load->model('catalog/attribute_group');
            $attr_data['attribute_group_id'] = $this->model_catalog_attribute_group->addAttributeGroup($this->request->clean($attr_group_data));
          } else {
            $this->load->model('gkd_import/attribute');
            $attr_data['attribute_group_id'] = $this->model_gkd_import_attribute->addAttributeGroup($this->request->clean($attr_group_data));
          }
        }
        
        // create attribute
        foreach ($languages as $language) {
          $attr_data['attribute_description'][$language['language_id']]['name'] = !empty($attribute_name_ml[$language['language_id']]) ? $attribute_name_ml[$language['language_id']] : $attribute_name;
        }
        
        if (version_compare(VERSION, '2', '>=')) {
          $this->load->model('catalog/attribute');
          $attribute_id = $this->model_catalog_attribute->addAttribute($attr_data);
        } else {
          $this->load->model('gkd_import/attribute');
          $attribute_id = $this->model_gkd_import_attribute->addAttribute($this->request->clean($attr_data));
        }
      }
      
      if (!empty($checkAttrTable)) {
        // check if corresponding attribute table exists, create it if not
        if (!empty($attribute_id)) {
          $attrExists = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute WHERE attribute_id = '" . (int) $attribute_id . "'")->row;
        }
        
        if (empty($attrExists['attribute_id'])) {
          // check for attr group
          $attr_group = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name = '" . $this->db->escape($attribute_group) . "'")->row;
        
          // group exists - get id
          if (!empty($attr_group['attribute_group_id'])) {
            $attr_data['attribute_group_id'] = $attr_group['attribute_group_id'];
          } 
          //  group not exists - create
          else {
            $attr_group_data = array();
            $attr_group_data['sort_order'] = 1;
            
            foreach ($languages as $language) {
              $attr_group_data['attribute_group_description'][$language['language_id']]['name'] = !empty($attribute_group_ml[$language['language_id']]) ? $attribute_group_ml[$language['language_id']] : $attribute_group;
            }
            
            if (version_compare(VERSION, '2', '>=')) {
              $this->load->model('catalog/attribute_group');
              $attr_data['attribute_group_id'] = $this->model_catalog_attribute_group->addAttributeGroup($this->request->clean($attr_group_data));
            } else {
              $this->load->model('gkd_import/attribute');
              $attr_data['attribute_group_id'] = $this->model_gkd_import_attribute->addAttributeGroup($this->request->clean($attr_group_data));
            }
          }
        
          $this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_id = '" . (int) $attr['attribute_id'] . "', attribute_group_id = '" . (int) $attr_data['attribute_group_id'] . "', sort_order = 0");
        }
      }
      
      // format values for product - only if not empty value
      if (trim($attribute_value)) {
        $return_values[$current_key]['attribute_id'] = $attribute_id;
      
        foreach ($languages as $language) {
          $return_values[$current_key]['product_attribute_description'][$language['language_id']]['text'] = !empty($attribute_value_ml[$language['language_id']]) ? $attribute_value_ml[$language['language_id']] : $attribute_value;
        }
      }
      /*
      $value = array(
        'attribute_id' => $attribute_id,
      );
      
      foreach ($languages as $language) {
        $value['product_attribute_description'][$language['language_id']]['text'] = !empty($attribute_value_ml[$language['code']]) ? $attribute_value_ml[$language['code']] : $attribute_value;
      }
      */
    }
    
    return $return_values;
  }
  
  public function attributeHandler_old($field, $config, $line) {
    $return_values = $values_array = $header_keys = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    foreach ((array) $config['columns'][$field] as $key => $value) {
      if (is_string($value) && strpos($value, '</li>') !== false) {
        $dom = new DOMDocument;
        @$dom->loadHTML($value);
        
        foreach ($dom->getElementsByTagName('li') as $node) {
          $values_array[] = $node->nodeValue;
        }
      } else if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
        foreach($value as $v) {
          $header_keys[] = $key;
        }
      } else if (!empty($config['multiple_separator'])) {
        $value = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value);
        $values_array = array_merge($values_array, $value);
        foreach($value as $v) {
          $header_keys[] = $key;
        }
      } else {
        $values_array[$key] = $value;
        $header_keys[] = $key;
      }
    }
    //$values_array = array_filter($values_array); // do not filter, else it make the count wrong (for headers)

    $this->load->model('localisation/language');
    $languages = $this->model_localisation_language->getLanguages();
    
    // foreach ($values_array as $current_key => &$value) {
    //for ($i = 0; $i <= count($values_array); $i++) {
    for($i = 0; $i < count($values_array); ++$i) {
      $current_key = $i;
      
      if (!isset($values_array[$i])) continue;
      
      $value = $values_array[$i];
      
      if (isset($header_keys[$current_key])) {
        $header_key = $header_keys[$current_key];
      }
      
      // xml advanced values
      if (!is_string($value) && $this->filetype == 'xml' && !empty($config['attribute_fields']['value'][$this->config->get('config_language_id')])) {
        foreach (array('group', 'name', 'value') as $attribute_type) {
          foreach ($languages as $language) {
            if (!empty($config['attribute_fields'][$attribute_type][$language['language_id']])) {
              if ($config['attribute_fields'][$attribute_type][$language['language_id']] == '.') {
                ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $value;
              } else if ($config['attribute_fields'][$attribute_type][$language['language_id']] == '[attributes]') {
                if ($attribute_type == 'name') {
                  foreach ($value as $k => $v) {
                    if (!$v) continue;
                    if (!strpos($k, '@')) {
                      $remove_key = $k.'@';
                    }
                    $values_array[] = $config['attribute_fields']['group'][$language['language_id']].':'.str_replace('@', '', strstr($k, '@')).':'.$v;
                  }
                  continue 2;
                }
              } else if (strpos($config['attribute_fields'][$attribute_type][$language['language_id']], '@')) {
                if (isset($line[$config['attribute_fields'][$attribute_type][$language['language_id']]]) && is_array($line[$config['attribute_fields'][$attribute_type][$language['language_id']]])) {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $line[$config['attribute_fields'][$attribute_type][$language['language_id']]][$current_key];
                } else {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $line[$config['attribute_fields'][$attribute_type][$language['language_id']]];
                }
              } else {
                ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = $this->getArrayPath($value, $config['attribute_fields'][$attribute_type][$language['language_id']]);
              }
            }
          }
          
          if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
            ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
          }
        
          // set defaults if empty
          if (empty(${'attribute_'.$attribute_type})) {
            foreach ($languages as $language) {
              if (!empty($config['attribute_fields_default'][$attribute_type][$language['language_id']])) {
                if ($attribute_type == 'group') {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = isset($config['attribute_fields_default'][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attribute_type][$language['language_id']] : 'Default';
                } else {
                  ${'attribute_'.$attribute_type.'_ml'}[$language['language_id']] = isset($config['attribute_fields_default'][$attribute_type][$language['language_id']]) ? $config['attribute_fields_default'][$attribute_type][$language['language_id']] : '';
                }
              }
            }
            
            if (!empty(${'attribute_'.$attribute_type.'_ml'})) {
              ${'attribute_'.$attribute_type} = reset(${'attribute_'.$attribute_type.'_ml'});
            } else {
              ${'attribute_'.$attribute_type} = ($attribute_type == 'group') ? 'Default' : '';
            }
          }
        }
      } else if (is_array($value)) {
        $attribute_group = isset($value['group']) ? $value['group'] : 'Default';
        $attribute_name = isset($value['name']) ? $value['name'] : 'Attribute';
        $attribute_value = isset($value['value']) ? $value['value'] : '';
      } else if (is_scalar($value)) {
        if (strpos($value, ':') !== false) {
          $values = explode(':', $value);
        }
        
        if (!isset($values)) {
          // get column header
          $column_headers = (array) json_decode(base64_decode($config['column_headers']));
          
          //if ($this->filetype == 'xml' &&
          if (isset($config['columns_bindings'][$field][$header_key]) && !empty($column_headers[ $config['columns_bindings'][$field][$header_key] ])) {
            $attribute_group = 'Default';
            if ($this->filetype == 'xml') {
              $attribute_name = basename($column_headers[ $config['columns_bindings'][$field][$header_key] ]);
            } else {
              $attribute_name = $column_headers[ $config['columns_bindings'][$field][$header_key] ];
            }
            
            $attribute_value = $value;
          } else {
            $attribute_group = 'Default';
            $attribute_name = 'Attribute';
            $attribute_value = $value;
          }
        } else if (isset($values) && count($values) == 2) {
          $attribute_group = 'Default';
          $attribute_name = $values[0];
          $attribute_value = $values[1];
        } else if (count($values) == 3) {
          $attribute_group = $values[0] ? $values[0] : 'Default';
          $attribute_name = $values[1];
          $attribute_value = $values[2];
        } else {
          // too much parts ?
          continue;
        }
      } else {
        continue;
      }
      
      /*
      // attribute group binding
      switch ($attribute_name) {
        case 'size_for_cloth': $attribute_group = 'Group 1'; break;
        case 'color_for_cloth': $attribute_group = 'Group 2'; break;
      }
      
      // attribute name binding
      switch ($attribute_name) {
        case 'size_for_cloth': $attribute_name = 'size'; break;
        case 'color_for_cloth': $attribute_name = 'color'; break;
      }
      */
      
      if (!empty($config['filters_from_attributes'])) {
        $config['columns']['product_filter'][] = $attribute_name .':'. $attribute_value;
      }
      
      if ($this->simulation) {
        if (trim($attribute_value)) {
          $return_values[] = $attribute_group .' <b>></b> '. $attribute_name .' <b>></b> '. $attribute_value;
        }
        
        continue;
      }
      
      $attr = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE name = '" . $this->db->escape($attribute_name) . "'")->row;
      
      // attribute exists ?
      if (!empty($attr['attribute_id'])) {
        $attribute_id = $attr['attribute_id'];
      }
      // not exists - create
      else {
        $attr_data = array();
        $attr_data['sort_order'] = 1;
        
        $attr_group = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name = '" . $this->db->escape($attribute_group) . "'")->row;
        
        // group exists - get id
        if (!empty($attr_group['attribute_group_id'])) {
          $attr_data['attribute_group_id'] = $attr_group['attribute_group_id'];
        } 
        //  group not exists - create
        else {
          $attr_group_data = array();
          $attr_group_data['sort_order'] = 1;
          
          foreach ($languages as $language) {
            $attr_group_data['attribute_group_description'][$language['language_id']]['name'] = !empty($attribute_group_ml[$language['language_id']]) ? $attribute_group_ml[$language['language_id']] : $attribute_group;
          }
          
          if (version_compare(VERSION, '2', '>=')) {
            $this->load->model('catalog/attribute_group');
            $attr_data['attribute_group_id'] = $this->model_catalog_attribute_group->addAttributeGroup($this->request->clean($attr_group_data));
          } else {
            $this->load->model('gkd_import/attribute');
            $attr_data['attribute_group_id'] = $this->model_gkd_import_attribute->addAttributeGroup($this->request->clean($attr_group_data));
          }
        }
        
        // create attribute
        foreach ($languages as $language) {
          $attr_data['attribute_description'][$language['language_id']]['name'] = !empty($attribute_name_ml[$language['language_id']]) ? $attribute_name_ml[$language['language_id']] : $attribute_name;
        }
        
        if (version_compare(VERSION, '2', '>=')) {
          $this->load->model('catalog/attribute');
          $attribute_id = $this->model_catalog_attribute->addAttribute($attr_data);
        } else {
          $this->load->model('gkd_import/attribute');
          $attribute_id = $this->model_gkd_import_attribute->addAttribute($this->request->clean($attr_data));
        }
      }
      
      // format values for product - only if not empty value
      if (trim($attribute_value)) {
        $return_values[$current_key]['attribute_id'] = $attribute_id;
      
        foreach ($languages as $language) {
          $return_values[$current_key]['product_attribute_description'][$language['language_id']]['text'] = !empty($attribute_value_ml[$language['language_id']]) ? $attribute_value_ml[$language['language_id']] : $attribute_value;
        }
      }
      /*
      $value = array(
        'attribute_id' => $attribute_id,
      );
      
      foreach ($languages as $language) {
        $value['product_attribute_description'][$language['language_id']]['text'] = !empty($attribute_value_ml[$language['code']]) ? $attribute_value_ml[$language['code']] : $attribute_value;
      }
      */
    }
    
    return $return_values;
  }
  
  public function imageHandler($field, $config, $multiple = false, $item_id = NULL, $get_path = false, $main_image = false) {
    $image_array = array();
    
    if (empty($config['columns'][$field])) {
      if ($multiple) {
        return $image_array;
      } else {
        return '';
      }
    }
    
    $sort_order = 0;
    
    foreach ((array) $config['columns'][$field] as $images) {
      
      if (!empty($config['multiple_separator']) && is_string($images)) {
        $images = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $images);
      }
      
      if (is_array($main_image)) {
        $main_image = array_shift($main_image);
      }
      
      if ($multiple) {
        $images = (array) $images;
      }
      
      if ($multiple && isset($images[0]) && $main_image == $images[0]) {
        array_shift($images);
      }
      
      foreach ((array) $images as $image) {
        if (is_array($image)) continue;
        
        $image = trim($image);
        
        if ($config['image_download'] && $image) {
          // if (substr($image, 0, 2) == '//') {
            // $image = 'http:' . $image;
          // }
          
          // add http if image starts by www.
          if (substr($image, 0, 4) == 'www.') {
            $image = 'http://'.$image;
          }
          
          if (substr($image, 0, 2) == '//') {
            $image = 'https:'.$image;
          }
          
          if (!empty($config['image_http_auth'])) {
            $image = str_replace(array('http://', 'https://'), array('http://'.$config['image_http_auth'].'@', 'https://'.$config['image_http_auth'].'@'), $image);
          }
          
          //$file_info = pathinfo(parse_url(trim($image), PHP_URL_PATH));
          $image = urldecode($image);
          //$file_info = pathinfo($this->tool->mb_parse_url(trim($image), PHP_URL_PATH));
          $file_info = pathinfo(parse_url(trim($image), PHP_URL_PATH));
          
          if (!empty($file_info['extension'])) {
            $file_info['extension'] = strtolower($file_info['extension']);
          }
          
          if (!empty($config['image_name_param'])) {
            parse_str(parse_url($image, PHP_URL_QUERY), $query_params);
            
            if (!empty($query_params[$config['image_name_param']])) {
              $file_info = pathinfo($query_params[$config['image_name_param']]);
            }
          }
          
          if (!isset($file_info['dirname'])) {
            $file_info['dirname'] = '';
          }
          
          // if no extension, get it by mime
          if (empty($file_info['extension']) || !in_array(strtolower($file_info['extension']), array('gif', 'jpg', 'jpeg', 'png', 'webp'))) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, trim($image));
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:30.0) Gecko/20100101 Firefox/30.0');
            
            $res = curl_exec($ch);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            
            switch($contentType) {
              case 'image/bmp': $file_info['extension'] = 'bmp'; break;
              case 'image/gif': $file_info['extension'] = 'gif'; break;
              case 'image/jpeg': $file_info['extension'] = 'jpg'; break;
              case 'image/jpeg;': $file_info['extension'] = 'jpg'; break;
              case 'image/pipeg': $file_info['extension'] = 'jfif'; break;
              case 'image/tiff': $file_info['extension'] = 'tif'; break;
              case 'image/png': $file_info['extension'] = 'png'; break;
              case 'image/png;': $file_info['extension'] = 'png'; break;
              case 'image/webp': $file_info['extension'] = 'webp'; break;
              default: $file_info['extension'] = '';
            }
            
            preg_match('/filename=[\'\"]?([^\"]+)/', $res, $match);
            
            if (!empty($match[1])) {
              $headerFileInfo = pathinfo($match[1]);
              
              if (!empty($headerFileInfo['filename'])) {
                $file_info['filename'] = $headerFileInfo['filename'];
                if (!empty($headerFileInfo['extension'])) {
                  $file_info['extension'] = $headerFileInfo['extension'];
                }
              } else {
                $file_info['filename'] = $match[1];
                if (empty($file_info['extension'])) {
                  $file_info['extension'] = pathinfo($match[1], PATHINFO_EXTENSION);
                }
              }
            }

            curl_close($ch);
          }
          
          // sanitize filename
          //$file_info['filename'] = preg_replace('/[^A-Za-z0-9_\s\.-~]/', '', $file_info['filename']);
          
          if (substr_count($file_info['dirname'], 'http')) {
            // incorrect array extract
           if (!$multiple) {
              return '';
            } else {
              continue;
            }
          }
          
          if (!in_array(strtolower($file_info['extension']), array('gif', 'jpg', 'jpeg', 'png', 'webp'))) {
            $this->tool->log(array(
              'row' => $this->session->data['obui_current_line'],
              'status' => 'error',
              'title' => $this->language->get('warning'),
              'msg' => $this->language->get('warning_incorrect_image_format') . ' ' . str_replace(' ', '%20', $image),
            ));
            
            if (!$multiple) {
              return '';
            } else {
              continue;
            }
          }
          
          if ($this->simulation && !$get_path) {
            if (!$multiple) {
              /* Now handled before
              if (!in_array(strtolower($file_info['extension']), array('gif', 'jpg', 'jpeg', 'png'))) {
                return array('error_format', $image);
              }*/
              return $image;
            } else {
              /* Now handled before
              if (!in_array(strtolower($file_info['extension']), array('gif', 'jpg', 'jpeg', 'png'))) {
                $image_array[] = 'error_format';
                continue;
              }*/
              $image_array[] = $image;
              continue;
            }
          }
          
          // detect if image is on actual server
          if (!in_array(substr($image, 0, 4), array('http', 'ftp:'))) {
            $filename = trim($image);
          
            if (!$multiple) {
              return $filename;
            } else {
              if (!empty($filename)) {
                $image_array[] = array(
                  'image' => $filename,
                  'sort_order' => $sort_order++,
                );
              }
              continue;
            }
          }
          
          if (version_compare(VERSION, '2', '>=')) {
            $path = 'catalog/';
            //$http_path = HTTP_CATALOG . 'image/catalog/';
          } else {
            $path = 'data/';
            //$http_path = HTTP_CATALOG . 'image/data/';
          }
          
          if (substr($config['image_location'], 0, 4) != 'http') {
            if (trim($config['image_location'], '/\\')) {
              $path .= trim($config['image_location'], '/\\') . '/';
            }
          }
          
          // sanitize dirname
          if (!empty($file_info['dirname'])) {
            $dirParts = explode('/', $file_info['dirname']);
            
            foreach ($dirParts as &$dirPart) {
              //$dirPart = urldecode(urldecode($dirPart));
              
              if (empty($config['image_sanitize'])) {
                $dirPart = $this->tool->urlify($dirPart, null, true, true);
              } else if ($config['image_sanitize'] == 'safe') {
                $dirPart = $this->tool->urlify($dirPart, null, true, false);
              } else if ($config['image_sanitize'] == 'lcase') {
                $dirPart = strtolower($dirPart);
              }
            }
            
            $file_info['dirname'] = implode('/', $dirParts);
          }
          
          if ($config['image_keep_path'] && trim($file_info['dirname'], '/\\')) {
            $path .= trim($file_info['dirname'], '/\\') . '/';
            
            if (!empty($config['image_remove_path'])) {
              $path = str_replace($config['image_remove_path'], '', $path);
            }
          }
          
          //$path = urldecode($path);
          
          if (!is_dir(DIR_IMAGE . $path)) {
            mkdir(DIR_IMAGE . $path, 0777, true);
          }
          
          // sanitize filename
          if (!empty($file_info['filename'])) {
            //$file_info['filename'] = urldecode(urldecode($file_info['filename']));
            
            if (empty($config['image_sanitize'])) {
              $file_info['filename'] = $this->tool->urlify($file_info['filename'], null, true, true);
            } else if ($config['image_sanitize'] == 'safe') {
              $file_info['filename'] = $this->tool->urlify($file_info['filename'], null, true, false);
            } else if ($config['image_sanitize'] == 'lcase') {
              $file_info['filename'] = strtolower($file_info['filename']);
            }
			    }
          
          //$filename = $path . urldecode($file_info['filename']) . '.' . $file_info['extension'];
          $filename = $path . $file_info['filename'] . '.' . $file_info['extension'];
          
          if ((!$item_id && $this->config->get('mlseo_insertautoimgname')) || ($item_id && $this->config->get('mlseo_editautoimgname'))) {
            $this->load->model('tool/seo_package');
            $seo_image_name = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_image_name_pattern'), $this->config->get('config_language_id'), $config['columns']);
            
            $seoPath = pathinfo($filename);
            
            if (!empty($seoPath['filename'])) {
              $seoFilename = $this->model_tool_seo_package->filter_seo($seo_image_name, 'image', '', '');
              $filename = $seoPath['dirname'] . '/' . $seoFilename . '.' . $seoPath['extension'];
              
              /* do not rename it, will be done after: if (file_exists(DIR_IMAGE . $filename)) {
                $x = 1;
                
                while (file_exists(DIR_IMAGE . $filename)) {
                  $filename = $seoPath['dirname'] . '/' . $seoFilename . '-' . $x . '.' . $seoPath['extension'];
                  $x++;
                }
              }*/
            }
          } 
          
          if ($this->simulation && $get_path) {
              return $filename;
          }
          
          if ($config['image_exists'] == 'rename') {
            $x = 1;
            while (file_exists(DIR_IMAGE . $filename)) {
              $filename = $path . $file_info['filename'] . '-' . $x++ . '.' . $file_info['extension'];
            }
          } else if ($config['image_exists'] == 'keep' && file_exists(DIR_IMAGE . $filename)) {
            // image skipped
            if (!$multiple) {
              return $filename;
            } else {
              $image_array[] = array(
                'image' => $filename,
                'sort_order' => $sort_order++,
              );
              
              continue;
            }
          }
          
          // copy image, replace space chars for compatibility with copy()
          // if (!@copy(trim(str_replace(' ', '%20', $image)), DIR_IMAGE . $filename)) {
          $copyError = $this->tool->copy_image(trim($image), DIR_IMAGE . $filename);
          
          if ($copyError !== true) {
            if (empty($config['image_not_exists'])) {
              $this->tool->log(array(
                'row' => $this->session->data['obui_current_line'],
                'status' => 'error',
                'title' => $this->language->get('warning'),
                'msg' => $copyError,
              ));
            } else {
              throw new GkdSkipException($copyError);
            }
            
            $filename = '';
          }
          
        } else {
          // get direct value
          $filename = trim($image);
          
          $path = '';
          if (substr($config['image_location'], 0, 4) != 'http') {
            if (trim($config['image_location'], '/\\')) {
              $path = trim($config['image_location'], '/\\') . '/';
            }
          }
          
          $filename = !empty($filename) ? $path.$filename : '';
          
          // throw error if image is not url and image not exists
          if (substr($config['image_location'], 0, 4) != 'http' && !empty($filename) && substr($filename, 0, 4) != 'http' && !is_file(DIR_IMAGE . $filename)) {
            if (empty($config['image_not_exists'])) {
              $this->tool->log(array(
                'row' => $this->session->data['obui_current_line'],
                'status' => 'error',
                'title' => $this->language->get('warning'),
                'msg' => 'The following image has not been found: ' . DIR_IMAGE . $filename,
              ));
            } else {
              throw new GkdSkipException('The following image has not been found: ' . DIR_IMAGE . $filename);
            }
          }
          
          if ($this->simulation) {
            if (!$multiple) {
              return $filename;
            } else {
              if (!empty($filename)) {
                $image_array[] = $filename;
              }
              continue;
            }
          }
        }
        
        // one field only, directly return first value
        if (!$multiple) {
          return $filename;
        }
        
        if (!empty($filename)) {
          $image_array[] = array(
            'image' => $filename,
            'sort_order' => $sort_order++,
          );
        }
      }
    }
    
    return $image_array;
  }
  
  public function downloadHandler($field, $config) {
    $downloadArray = array();
    
    if (empty($config['columns'][$field])) {
      return array();
    }
    
    $sort_order = 0;
    $filenames = array();
    
    foreach ((array) $config['columns'][$field] as $k => $downloads) {
      if (!empty($config['multiple_separator']) && is_string($downloads)) {
        $downloads = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $downloads);
      }
        
      foreach ((array) $downloads as $download) {
        if ((!is_scalar($download) || $download == '') && in_array($this->filetype, array('xml', 'json')) && !empty($config['download_fields']['value'])) {
          if (!empty($config['download_fields']['value'])) {
            $download = $this->getArrayPath(is_array($download) ? $download : $line, $config['download_fields']['value']);
          }
          
          /*
          foreach (array('name') as $download_type) { // to terminate
            foreach ($languages as $language) {
              ${'download_'.$download_type.'_ml'} = array();
              
              if (!empty($config['download_fields'][$download_type][$language['language_id']])) {
                // use . or [current] to have main download value
                if (($config['download_fields'][$download_type][$language['language_id']] == '[current]' || $config['download_fields'][$download_type][$language['language_id']] == '.' || $config['download_fields'][$download_type][$language['language_id']] == '') && is_string($value)) {
                  ${'download_'.$download_type.'_ml'}[$language['language_id']] = $value;
                  
                // use [attributes] to ...
                } else if ($config['download_fields'][$download_type][$language['language_id']] == '[attributes]') {
                  if ($download_type == 'name') {
                    foreach ($value as $k => $v) {
                      if (!$v) continue;
                      if (!strpos($k, '@')) {
                        $remove_key = $k.'@';
                      }
                      $values_array[] = $config['download_fields']['group'][$language['language_id']].':'.str_replace('@', '', strstr($k, '@')).':'.$v;
                    }
                    continue 2;
                  }
                
                // if array of values process each one
                } else if (isset($line[$config['download_fields'][$download_type][$language['language_id']]]) && is_array($line[$config['download_fields'][$download_type][$language['language_id']]])) {
                  // if we are in value or the value parameter is array then process other fields same way
                  if ($download_type == 'value' || is_array($line[$config['download_fields']['value'][$language['language_id']]])) {
                    foreach ($line[$config['download_fields'][$download_type][$language['language_id']]] as $k => $v) {
                      ${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k] = $v;
                    }
                  } else {
                    // group or name refers to an array, try to get key corresponding to current value key
                    if (isset($line[$config['download_fields'][$download_type][$language['language_id']]][$current_key])) {
                      ${'download_'.$download_type.'_ml'}[$language['language_id']][$current_key] = $line[$config['download_fields'][$download_type][$language['language_id']]][$current_key];
                    }
                  }
                
                // use @ to ...
                } else if (strpos($config['download_fields'][$download_type][$language['language_id']], '@')) {
                  if (isset($line[$config['download_fields'][$download_type][$language['language_id']]]) && is_array($line[$config['download_fields'][$download_type][$language['language_id']]])) {
                    ${'download_'.$download_type.'_ml'}[$language['language_id']] = $line[$config['download_fields'][$download_type][$language['language_id']]][$current_key];
                  } else if (isset($line[$config['download_fields'][$download_type][$language['language_id']]])) {
                    ${'download_'.$download_type.'_ml'}[$language['language_id']] = $line[$config['download_fields'][$download_type][$language['language_id']]];
                  }
                  
                // get path
                } else {
                  if ($download_type == 'value' && strpos($config['download_fields']['value'][$language['language_id']], '+')) {
                    foreach (explode('+', $config['download_fields'][$download_type][$language['language_id']]) as $k => $vals) {
                      foreach (explode('~', $vals) as $v) {
                        if (substr($v, 0, 1) == "'" && substr($v, -1) == "'") {
                          $resVal = substr($v, 1, -1);
                        } else {
                          $resVal = $this->getArrayPath(is_array($value) ? $value : $line, $v);
                        }
                        
                        if ($resVal !== '') {
                          if (!isset(${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k])) {
                            ${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k] = '';
                          }
                          
                          ${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k] .= $resVal;
                        }
                      }
                      
                      if (isset(${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k]) && trim(${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k]) == '') {
                        unset(${'multi_download_'.$download_type.'_ml'}[$language['language_id']][$k]);
                      }
                    }
                  } else {
                    //${'download_'.$download_type.'_ml'}[$language['language_id']] = $this->getArrayPath(is_array($value) ? $value : $line, $config['download_fields'][$download_type][$language['language_id']]);
                    
                    ${'download_'.$download_type.'_ml'}[$language['language_id']] = '';
                    
                    // use ~ to combine multiple values in one
                    foreach (explode('~', $config['download_fields'][$download_type][$language['language_id']]) as $k => $v) {
                      $resVal = $this->getArrayPath(is_array($value) ? $value : $line, $v);
                      
                      ${'download_'.$download_type.'_ml'}[$language['language_id']] .= $resVal;
                    }
                  }
                }
              }
            }
            
            if (!empty(${'download_'.$download_type.'_ml'})) {
              ${'download_'.$download_type} = reset(${'download_'.$download_type.'_ml'});
            }
          
            // set defaults if empty
            if (empty(${'download_'.$download_type})) {
              foreach ($languages as $language) {
                if (!empty($config['download_fields_default'][$download_type][$language['language_id']])) {
                  if ($download_type == 'group') {
                    ${'download_'.$download_type.'_ml'}[$language['language_id']] = isset($config['download_fields_default'][$download_type][$language['language_id']]) ? $config['download_fields_default'][$download_type][$language['language_id']] : 'Default';
                  } else {
                    ${'download_'.$download_type.'_ml'}[$language['language_id']] = isset($config['download_fields_default'][$download_type][$language['language_id']]) ? $config['download_fields_default'][$download_type][$language['language_id']] : '';
                  }
                }
              }
              
              if (!empty(${'download_'.$download_type.'_ml'})) {
                ${'download_'.$download_type} = reset(${'download_'.$download_type.'_ml'});
              } else {
                ${'download_'.$download_type} = '';
              }
            }
          } */
        }
      
        $download = trim($download);
        
        if ($download) {
          // add http if image starts by www.
          if (substr($download, 0, 4) == 'www.') {
            $download = 'http://'.$download;
          }
          
          $file_info = pathinfo(parse_url(trim($download), PHP_URL_PATH));
          
          // if no extension, get it by mime
          /*
          if (empty($file_info['extension'])) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, trim($download));
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ch);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            switch($contentType) {
              case 'image/bmp': $file_info['extension'] = 'bmp'; break;
              case 'image/gif': $file_info['extension'] = 'gif'; break;
              case 'image/jpeg': $file_info['extension'] = 'jpg'; break;
              case 'image/pipeg': $file_info['extension'] = 'jfif'; break;
              case 'image/tiff': $file_info['extension'] = 'tif'; break;
              case 'image/png': $file_info['extension'] = 'png'; break;
              default: $file_info['extension'] = '';
            }
          }
          */

          if (substr_count($file_info['dirname'], 'http')) {
            // incorrect array extract
            continue;
          }
          
          // detect if file is on actual server
          if (substr($download, 0, 4) !== 'http') {
            $filename = trim($download);
          
            if (!empty($filename)) {
              // @todo: get download ID by filename
              $downloadId = '';
              
              if (!empty($downloadId)) {
                $downloadArray[] = $downloadId;
              }
            }
            continue;
          }
          
          if (empty($file_info['extension'])) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, trim($download));
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $res = curl_exec($ch);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            
            preg_match('/filename=[\'\"]?([^\"]+)/', $res, $match);
            
            if (!empty($match[1])) {
              $headerFileInfo = pathinfo($match[1]);
              
              if (!empty($headerFileInfo['filename'])) {
                $file_info['filename'] = $headerFileInfo['filename'];
                $file_info['extension'] = $headerFileInfo['extension'];
              } else {
                $file_info['filename'] = $match[1];
                if (empty($file_info['extension'])) {
                  $file_info['extension'] = pathinfo($match[1], PATHINFO_EXTENSION);
                }
              }
            }

            curl_close($ch);
          }
          
          $filename = urldecode($file_info['filename']) . '.' . $file_info['extension'];
        
          if ($this->simulation) {
            $downloadArray[] = $filename;
            continue;
          }
          
          // copy image, replace space chars for compatibility with copy()
          $filenameToken = $filename . '.' . token(32);
          $copyResult = $this->tool->copy_image(trim(str_replace(' ', '%20', $download)), DIR_DOWNLOAD . $filenameToken);
          
          if ($copyResult !== true) {
            $this->tool->log(array(
              'row' => $this->session->data['obui_current_line'],
              'status' => 'error',
              'title' => $this->language->get('warning'),
              'msg' => $copyResult,
            ));
            
            $filename = '';
          } else {
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            
            $downloadData = array();
            
            $downloadData['filename'] = $filenameToken;
            $downloadData['mask'] = $filename;
            
            foreach ($languages as $language) {
              $downloadData['download_description'][$language['language_id']]['name'] = $filename;
            }
            
            $this->load->model('catalog/download');
            $downloadId = $this->model_catalog_download->addDownload($downloadData);
          }
          
        }
        
        if (!empty($downloadId)) {
          $downloadArray[] = $downloadId;
        }
      }
    }
    
    return $downloadArray;
  }
  
  public function publicDownloadHandler($field, $config) {
    $downloadArray = array();
    
    if (empty($config['columns'][$field])) {
      return array();
    }
    
    $sort_order = 0;
    $filenames = array();
    
    foreach ((array) $config['columns'][$field] as $language_id => $sectionDownloads) {
      if (empty($sectionDownloads)) continue;
      
      foreach ($sectionDownloads as $k => $downloads) {
        if (!empty($config['multiple_separator']) && is_string($downloads)) {
          $downloads = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $downloads);
        }
        
        foreach ((array) $downloads as $download) {
          if ((!is_scalar($download) || $download == '') && in_array($this->filetype, array('xml', 'json')) && !empty($config['download_fields']['value'])) {
            if (!empty($config['download_fields']['value'])) {
              $download = $this->getArrayPath(is_array($download) ? $download : $line, $config['download_fields']['value']);
            }
          }
        
          $download = trim($download);
          
          if ($download) {
            // add http if image starts by www.
            if (substr($download, 0, 4) == 'www.') {
              $download = 'http://'.$download;
            }
            
            $file_info = pathinfo(parse_url(trim($download), PHP_URL_PATH));
            
            // if no extension, get it by mime
            /*
            if (empty($file_info['extension'])) {
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, trim($download));
              curl_setopt($ch, CURLOPT_HEADER, 1);
              curl_setopt($ch, CURLOPT_NOBODY, 1);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_exec($ch);
              $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
              curl_close($ch);
              
              switch($contentType) {
                case 'image/bmp': $file_info['extension'] = 'bmp'; break;
                case 'image/gif': $file_info['extension'] = 'gif'; break;
                case 'image/jpeg': $file_info['extension'] = 'jpg'; break;
                case 'image/pipeg': $file_info['extension'] = 'jfif'; break;
                case 'image/tiff': $file_info['extension'] = 'tif'; break;
                case 'image/png': $file_info['extension'] = 'png'; break;
                default: $file_info['extension'] = '';
              }
            }
            */

            if (substr_count($file_info['dirname'], 'http')) {
              // incorrect array extract
              continue;
            }
            
            // detect if file is on actual server
            if (substr($download, 0, 4) !== 'http') {
              $filename = trim($download);
            
              if (!empty($filename)) {
                // @todo: get download ID by filename
                $downloadId = '';
                
                if (is_file(DIR_SYSTEM.'../files/'.$download)) {
                  $downloadArray[$language_id][] = $downloadId;
                }
              }
              continue;
            }
            
            if (empty($file_info['extension'])) {
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, trim($download));
              curl_setopt($ch, CURLOPT_HEADER, true);
              curl_setopt($ch, CURLOPT_NOBODY, true);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              
              $res = curl_exec($ch);
              $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
              
              preg_match('/filename=[\'\"]?([^\"]+)/', $res, $match);
              
              if (!empty($match[1])) {
                $headerFileInfo = pathinfo($match[1]);
                
                if (!empty($headerFileInfo['filename'])) {
                  $file_info['filename'] = $headerFileInfo['filename'];
                  $file_info['extension'] = $headerFileInfo['extension'];
                } else {
                  $file_info['filename'] = $match[1];
                  if (empty($file_info['extension'])) {
                    $file_info['extension'] = pathinfo($match[1], PATHINFO_EXTENSION);
                  }
                }
              }

              curl_close($ch);
            }
            
            $filename = urldecode($file_info['filename']) . '.' . $file_info['extension'];
          
            if ($this->simulation) {
              $downloadArray[$language_id][] = $filename;
              continue;
            }
            
            // copy file, replace space chars for compatibility with copy()
            if (!is_dir(DIR_SYSTEM.'../files/.tmp/')) {
              mkdir(DIR_SYSTEM.'../files/.tmp/');
            }
            
            $copyResult = $this->tool->copy_image(trim(str_replace(' ', '%20', $download)), DIR_SYSTEM.'../files/.tmp/' . $filename);
            
            if ($copyResult !== true) {
              $this->tool->log(array(
                'row' => $this->session->data['obui_current_line'],
                'status' => 'error',
                'title' => $this->language->get('warning'),
                'msg' => $copyResult,
              ));
              
              $filename = '';
            } else {
            }
            
          }
          
          if (!empty($filename)) {
            $downloadArray[$language_id][] = $filename;
          }
        }
      }
    }
    
    return $downloadArray;
  }
  
  /*
  public function productGroupHandler($field, $config) {
    if (empty($config['columns'][$field])) {
      return array();
    }
    
    $array_values = array();
    
    foreach ((array) $config['columns'][$field] as $products) {
      if (!empty($config['multiple_separator'])) {
        $products = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $products);
      }
        
      foreach ((array) $products as $value) {
        if (!$value) {
          continue;
        } else {
          if (is_numeric($value)) {
            $array_values[] = $value;
            continue;
          }
          
          $value = $this->request->clean(trim($value));
          
          $query = $this->db->query("SELECT product_group_id FROM " . DB_PREFIX . "product_group_description WHERE name = '" . $this->db->escape($value) . "'")->row;

          if (!empty($query['product_group_id'])) {
            $array_values[] = $query['product_group_id'];
          } 
        }
      }
    }
    
    return $array_values;
  }
  */
  
  public function orderStatusHandler($field, $config) {
    $this->load->model('gkd_import/order');
    $value = $init_value = $config['columns'][$field];

    if (is_numeric($value)) {
      // numeric, treat as status id
      if ($this->simulation) {
        $value = $this->model_gkd_import_order->getOrderStatusName($value);
      }
    } else if (is_string($value)) {
      // string, get status id
      $value = $this->model_gkd_import_order->getOrderStatusIdFromName($value);
    }
    
    if (!$value && isset($config['defaults']['order_status_id'])) {
      if ($this->simulation) {
        $value = '"'.$init_value . '" not found, set default: ' . $this->model_gkd_import_order->getOrderStatusName($config['defaults']['order_status_id']);
      } else {
        $value = $config['defaults']['order_status_id'];
      }
    }
    
    return $value;
  }
  
  public function booleanHandler($field, $config) {
    // handle '', '0', 0
    if (empty($config['columns'][$field])) {
      return 0;
    }
    
    $value = $config['columns'][$field];

    return $this->isBoolean($value);
  }
  
  public function isBoolean($value) {
    // handle numeric values
    if (is_numeric($value)) {
      if ($value > 0) {
        return 1;
      } else {
        return 0;
      }
      
    // handle textual values
    } else if (is_string($value)) {
      switch (strtolower($value)) {
        case 'disabled':
        case 'inactive':
        case 'false':
        case 'off':
        case 'no':
        case 'non':
        case 'na':
        case 'ne':
        case 'nu':
        case 'não':
        case 'nē':
        case 'nej':
        case 'nei':
        case 'nee':
        case 'nein':
        case 'nie':
        case 'όχι':
        case 'нет':
        case 'ні':
        case 'evet':
        case 'לא':
        case 'いいえ':
        case 'не':
        case '0':
        case 'n':
          return 0; break;
          
        case 'enabled':
        case 'active':
        case 'true':
        case 'on':
        case 'yes':
        case 'oui':
        case 'sí':
        case 'sì':
        case 'da':
        case 'sim':
        case 'taip':
        case 'jo':
        case 'да':
        case 'hayır':
        case 'tak':
        case 'так':
        case 'ναι':
        case 'はい':
        case 'ja':
        case 'jā':
        case 'כן':
        case '1':
        case 'y':
          return 1; break;
      }
    }
    
    // in case not catched return value
    return $value ? 1 : 0;
  }
  
  public function storeHandler($field, $config) {
    $return_values = $values_array = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    if (is_array($config['columns'][$field]) && count($config['columns'][$field]) == 1 && $config['columns'][$field][0] === '') {
      if (isset($config['defaults']['product_store'])) {
        return $config['defaults']['product_store'];
      } else {
        return array();
      }
    }
    
    foreach ((array) $config['columns'][$field] as $value) {
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    if (empty($this->storeIdToName)) {
      $this->storeIdToName[0] = $this->config->get('config_name');
      $this->load->model('setting/store');
      $stores = $this->model_setting_store->getStores();
      foreach ($stores as $storeItem) {
        $this->storeIdToName[$storeItem['store_id']] = $storeItem['name'];
      }
    }
    
    foreach ($values_array as $store) {
      if (is_numeric($store)) {
        $return_values[$store] = $store;
      } else if (array_search($store, $this->storeIdToName) !== false) {
        $return_values[$store] = array_search($store, $this->storeIdToName);
      } else {
        $this->tool->log(array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => sprintf($this->language->get('warning_store_not_found'), $store),
        ));
      }
    }
    
    if ($this->simulation) {
      foreach ($return_values as &$return_val) {
        if (isset($this->storeIdToName[(int) $return_val])) {
          $return_val = $this->storeIdToName[(int) $return_val];
        }
      }
    }
    
    return $return_values;
  }
  
  public function layoutHandler($field, $config) {
    $return_values = $values_array = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    foreach ((array) $config['columns'][$field] as $value) {
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    if (empty($this->layoutIdToName)) {
      $this->load->model('design/layout');
      $layouts = $this->model_design_layout->getLayouts();
      
      $data['layouts'] = array('' => '');
      foreach ($layouts as $layout) {
        $this->layoutIdToName[$layout['layout_id']] = $layout['name'];
      }
    }
    
    foreach ($values_array as $store_id => $val) {
      $return_values[$store_id] = '';
      
      if ($val === '') {
        if (isset($config['defaults']['product_layout'][$store_id])) {
          $return_values[$store_id] = $config['defaults']['product_layout'][$store_id];
        }
      } else if (is_numeric($val)) {
        $return_values[$store_id] = $val;
      } else if (array_search($val, $this->layoutIdToName) !== false) {
        $return_values[$store_id] = array_search($val, $this->layoutIdToName);
      } else {
        $this->tool->log(array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => sprintf($this->language->get('warning_layout_not_found'), $val),
        ));
      }
    }
    
    if ($this->simulation) {
      foreach ($return_values as &$return_val) {
        if (isset($this->layoutIdToName[(int) $return_val])) {
          $return_val = $this->layoutIdToName[(int) $return_val];
        }
      }
    }
    
    return $return_values;
  }
  
  public function manufacturerHandler($config) {
    if (empty($config['columns']['manufacturer_id'])) {
      return '';
    }
    if (is_numeric($config['columns']['manufacturer_id'])) {
      $query = $this->db->query("SELECT DISTINCT manufacturer_id, name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . $this->db->escape($this->request->clean($config['columns']['manufacturer_id'])) . "'")->row;
      
      if ($this->simulation) {
        if (!empty($query['name'])) {
          if ($query['manufacturer_id'] && !empty($config['delete']) && $config['delete'] == 'missing_brand') {
            $this->session->data['obui_delete_brand'][] = $query['manufacturer_id'];
          }
          return '['.$query['manufacturer_id'].'] '.$query['name'];
        } else {
          return $this->language->get('not_found');
        }
      } else {
        if (!empty($query['manufacturer_id']) && !empty($config['delete']) && $config['delete'] == 'missing_brand') {
          $this->session->data['obui_delete_brand'][] = $query['manufacturer_id'];
        }
        if (!empty($query['manufacturer_id'])) {
          return $query['manufacturer_id'];
        }
      }
    }
    
    if (!is_string($config['columns']['manufacturer_id'])) {
      return '';
    }
    
    $query = $this->db->query("SELECT DISTINCT manufacturer_id, name FROM " . DB_PREFIX . "manufacturer WHERE name = '" . $this->db->escape($this->request->clean($config['columns']['manufacturer_id'])) . "'")->row;

    $this->load->model('localisation/language');
    $languages = $this->model_localisation_language->getLanguages();
    
    if (!empty($query['manufacturer_id'])) {
      if ($query['manufacturer_id'] && !empty($config['delete']) && $config['delete'] == 'missing_brand') {
        $this->session->data['obui_delete_brand'][] = $query['manufacturer_id'];
      }
      
      if ($this->simulation) {
        return '['.$query['manufacturer_id'].'] '.$query['name'];
      } else {
        return $query['manufacturer_id'];
      }
    } else if (!empty($config['manufacturer_create'])) {
      // manufacturer does not exists, create it ?
      $manufacturer_data = array(
        'name' => $config['columns']['manufacturer_id'],
        'sort_order' => 0,
        'manufacturer_store' => isset($config['columns']['product_store']) ? $config['columns']['product_store'] : (isset($config['defaults']['product_store']) ? $config['defaults']['product_store'] : array(0)),
        'keyword' => $this->tool->filter_seo($config['columns']['manufacturer_id']),
      );
      
      $manufacturer_data['manufacturer_seo_url'] = array();
      
      // for Complete SEO Package
      if ($this->config->get('mlseo_enabled')) {
        $stores = array();
        
        if ($this->config->get('mlseo_multistore')) {
          $this->load->model('setting/store');
          $stores = $this->model_setting_store->getStores();
        }
        
        $stores[] = array(
          'store_id' => 0,
          'name' => 'default',
        );
        
        foreach ($stores as $store) {
          foreach ($languages as $language) {
            $manufacturer_data['seo_manufacturer_description'][$store['store_id']][$language['language_id']] = array(
              'name' => $config['columns']['manufacturer_id'],
              'description' => '',
              'seo_keyword' => '',
              'seo_h1' => '',
              'seo_h2' => '',
              'seo_h3' => '',
              'meta_title' => '',
              'meta_description' => '',
              'meta_keyword' => '',
            );
          }
        }
      }
      
      // for other manufacturer data modules
      foreach ($languages as $language) {
        $manufacturer_data['manufacturer_description'][$language['language_id']] = array(
          'name' => $config['columns']['manufacturer_id'],
          'description' => '',
          'meta_title' => $config['columns']['manufacturer_id'],
          'meta_h1' => '',
          'meta_description' => '',
          'meta_keyword' => '',
          'smp_h1_title' => '',
        );
      }
      
      $this->load->model('catalog/manufacturer');
      
      if (!$this->simulation) {
        $manufacturer_id = $this->model_catalog_manufacturer->addManufacturer($this->request->clean($manufacturer_data));
      } else {
        return '['.$this->language->get('new').'] '.$config['columns']['manufacturer_id'];
      }
      
      return $manufacturer_id;
    }
    
    return '';
  }
  
  public function parentCategoryHandler($field, $config, $ml_parent = array()) {
    if (empty($config['columns'][$field]) && empty($ml_parent)) {
      if ($this->simulation) {
        return $this->language->get('text_none');
      } else {
        return 0;
      }
    }
    
    if (empty($config['columns'][$field]) && !empty($ml_parent['name'])) {
      $config['columns'][$field] = reset($ml_parent['name']);
    }
    
    $this->load->model('catalog/category');
      
    $this->load->model('localisation/language');
    $languages = $this->model_localisation_language->getLanguages();
    
    $parent_id = 0;
    $simu_text = '';
    
    if (!empty($config['subcategory_separator'])) {
      $subcategories = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $config['columns'][$field]);
      if (!empty($ml_parent['name'])) {
        foreach($ml_parent['name'] as $lang_id => $val) {
          $subcategories_ml[$lang_id] = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $ml_parent['name'][$lang_id]);
        }
      }
      if (!empty($ml_parent['seo_keyword'])) {
        foreach($ml_parent['seo_keyword'] as $lang_id => $val) {
          $subcategories_seo_ml[$lang_id] = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $ml_parent['seo_keyword'][$lang_id]);
        }
      }
    } else {
      $subcategories = (array) $config['columns'][$field];
    }
    
    $full_categories = $subcategories;
    
    $cat_name = array_pop($subcategories);
    
    if (!is_string($cat_name)) {
      //continue;
    }
    
    // detect cat id on top category
    if (isset($full_categories[0]) && ctype_digit($full_categories[0]) && $full_categories[0] > 0) {
      if ($this->simulation) {
        $query = $this->db->query("SELECT name, category_id FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int) $full_categories[0] . "'")->row;
        
        if (!empty($query['category_id'])) {
          $full_categories[0] = $query['name'];
        }
      }
    }
        
    if (is_numeric($cat_name)) {
      $parent_id = $cat_name;
      $simu_text = '[' . $cat_name . ']';
    } else {
      $parent_name = $parent_lvl2_name = $parent_lvl3_name = false;
      
      if (count($subcategories)) {
        $parent_name = array_pop($subcategories);
      }
      
      if (count($subcategories)) {
        $parent_lvl2_name = array_pop($subcategories);
      }
      
      if (count($subcategories)) {
        $parent_lvl3_name = array_pop($subcategories);
      }
      
      // 2 parents levels detection, then 1, then 0
      if (!empty($parent_lvl3_name)) {
        if (is_array($cat_name) || is_array($parent_name) || is_array($parent_lvl2_name) || is_array($parent_lvl3_name)) return 0;
        $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id LEFT JOIN " . DB_PREFIX . "category_description pcd ON pcd.category_id = c.parent_id LEFT JOIN " . DB_PREFIX . "category pc ON pc.category_id = pcd.category_id LEFT JOIN " . DB_PREFIX . "category_description ppcd ON ppcd.category_id = pc.parent_id LEFT JOIN " . DB_PREFIX . "category ppc ON ppc.category_id = ppcd.category_id LEFT JOIN " . DB_PREFIX . "category_description pppcd ON pppcd.category_id = ppc.parent_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND pcd.name = '" . $this->db->escape(trim($this->request->clean($parent_name))) . "' AND ppcd.name = '" . $this->db->escape(trim($this->request->clean($parent_lvl2_name))) . "' AND pppcd.name = '" . $this->db->escape(trim($this->request->clean($parent_lvl3_name))) . "' GROUP BY cd.category_id")->rows;
      } else if (!empty($parent_lvl2_name)) {
        if (is_array($cat_name) || is_array($parent_name) || is_array($parent_lvl2_name)) return 0;
        $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id LEFT JOIN " . DB_PREFIX . "category_description pcd ON pcd.category_id = c.parent_id LEFT JOIN " . DB_PREFIX . "category pc ON pc.category_id = pcd.category_id LEFT JOIN " . DB_PREFIX . "category_description ppcd ON ppcd.category_id = pc.parent_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND pcd.name = '" . $this->db->escape(trim($this->request->clean($parent_name))) . "' AND ppcd.name = '" . $this->db->escape(trim($this->request->clean($parent_lvl2_name))) . "' GROUP BY cd.category_id")->rows;
      } else if (!empty($parent_name)) {
        if (is_array($cat_name) || is_array($parent_name)) return 0;
        $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id LEFT JOIN " . DB_PREFIX . "category_description pcd ON pcd.category_id = c.parent_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND pcd.name = '" . $this->db->escape(trim($this->request->clean($parent_name))) . "' GROUP BY cd.category_id")->rows;
      } else {
        if (is_array($cat_name)) return 0;
        $query = $this->db->query("SELECT name, category_id FROM " . DB_PREFIX . "category_description WHERE name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' GROUP BY category_id")->rows;
      }
      
      if (count($query) === 1) {
        if ($this->simulation) {
          $simu_text = '['.$query[0]['category_id'].'] ' . implode(' <b>></b> ', $full_categories);
        } else {
          $parent_id = $query[0]['category_id'];
        }
      } else if (!empty($config['category_create'])) {
        // category does not exists, create it ?
        $this->load->model('catalog/category');
        
        $parent_id = 0;
        
        $gkd_extra_fields = !empty($config['extra']) ? $config['extra'] : array();
        $gkd_extra_desc_fields = !empty($config['extraml']) ? $config['extraml'] : array();
        
        foreach ($full_categories as $key => $cat_name) {
          $cat_exists = $this->db->query("SELECT cd.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id WHERE name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND c.parent_id = '".(int) $parent_id."'")->row;
          
          if (empty($cat_exists['category_id'])) {
            $cat_data = array(
              'parent_id' => $parent_id,
              'column' => 3,
              'top' => 1,
              'sort_order' => 0,
              'category_store' => isset($config['columns']['product_store']) ? $config['columns']['product_store'] : (isset($config['defaults']['product_store']) ? $config['defaults']['product_store'] : array(0)),
              'status' => 1,
              'keyword' => $this->tool->urlify($cat_name),
            );
            
            foreach ($gkd_extra_fields as $extra_field) {
              $cat_data[$extra_field] = isset($config['columns'][$extra_field]) ? $config['columns'][$extra_field] : '';
            }
            
            $cat_data['category_seo_url'] = array();
            
            foreach ($languages as $language) {
              if (!empty($subcategories_seo_ml[$language['language_id']][$key])) {
                $seo = $subcategories_seo_ml[$language['language_id']][$key];
              } else if (!empty($subcategories_ml[$language['language_id']][$key])) {
                $seo = $this->tool->urlify($subcategories_ml[$language['language_id']][$key]);
              } else {
                $seo = $this->tool->urlify($cat_name);
              }
              
              $cat_data['category_description'][$language['language_id']] = array(
               'name' => !empty($subcategories_ml[$language['language_id']][$key]) ? trim($subcategories_ml[$language['language_id']][$key]) : trim($cat_name),
               'description' => '',
               'meta_title' => !empty($subcategories_ml[$language['language_id']][$key]) ? trim($subcategories_ml[$language['language_id']][$key]) : trim($cat_name),
               'meta_description' => '',
               'meta_keyword' => '',
               'seo_h1' => '',
               'meta_h1' => '',
               'custom_imgtitle' => '',
               'custom_alt' => '',
               'custom_h1' => '',
               'custom_h2' => '',
               'smp_h1_title' => '',
               'seo_keyword' => $seo,
              );
              
              foreach ($gkd_extra_desc_fields as $extra_field) {
                $cat_data['category_description'][$language['language_id']][$extra_field] = isset($config['columns']['product_description'][$language['language_id']][$extra_field]) ? $config['columns']['product_description'][$language['language_id']][$extra_field] : '';
              }
            }
            
            // for Complete SEO Package
            if ($this->config->get('mlseo_enabled') && $this->config->get('mlseo_multistore')) {
              $this->load->model('setting/store');
              $stores = $this->model_setting_store->getStores();
              
              foreach ($stores as $store) {
                foreach ($languages as $language) {
                  $cat_data['seo_category_description'][$store['store_id']][$language['language_id']] = array(
                    'name' => !empty($subcategories_ml[$language['language_id']][$key]) ? trim($subcategories_ml[$language['language_id']][$key]) : trim($cat_name),
                    'description' => '',
                    'seo_keyword' => '',
                    'seo_h1' => '',
                    'seo_h2' => '',
                    'seo_h3' => '',
                    'meta_title' => '',
                    'meta_description' => '',
                    'meta_keyword' => '',
                  );
                }
              }
            }
            
            if (!$this->simulation) {
              if (version_compare(VERSION, '2', '>=')) {
                $parent_id = $this->model_catalog_category->addCategory($this->request->clean($cat_data));
              } else {
                $this->load->model('gkd_import/category');
                $parent_id = $this->model_gkd_import_category->addCategory($this->request->clean($cat_data));
              }
            } else {
              $simu_text .= $simu_text ? ' &gt; ' : '';
              $simu_text .= '['.$this->language->get('new').'] ' . trim($cat_name);
            }
          } else {
            $parent_id = $cat_exists['category_id'];
        
            if ($this->simulation) {
              $simu_text .= $simu_text ? ' &gt; ' : '';
              $simu_text .= '['.$cat_exists['category_id'].'] ' . $cat_name;
            }
          }
        }
      }
    }
    
    return $this->simulation ? $simu_text : $parent_id;
  }
  
  public function categoryHandler($field, $config, $product_id, $force_parent_id = false, $ids_field = false) {
    $values = array();
    
    if (!isset($config['columns'][$field])) {
      return $values;
    }
    
    // get current values if necessary
    if (!$this->simulation && !empty($config['category_insert_type']) && $config['category_insert_type'] == 'rm_add' && !in_array($product_id, $this->session->data['obui_processed_ids'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int) $product_id. "'");
    }
    
    if ((!empty($config['category_insert_type']) && $config['category_insert_type'] == 'update') || (!empty($config['category_insert_type']) && $config['category_insert_type'] == 'rm_add' && in_array($product_id, $this->session->data['obui_processed_ids']))) {
      $currentCats = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int) $product_id. "'")->rows;
      
      foreach ($currentCats as $currentCat) {
        $config['columns'][$field][] = $currentCat['category_id'];
      }
    }
    
    foreach ((array) $config['columns'][$field] as $key => $categories) {
      # custom_category_handler
      
      if (!$categories) {
        //continue; // do not skip to make possible to get subcategories in case main is empty
      }
      
      // add subcategories
      if (!empty($config['columns']['sub_'.$field][$key]) && is_string($categories)) {
        foreach ($config['columns']['sub_'.$field][$key] as $subcat) {
          $categories .= @html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8') . $subcat;
        }
      }
      
      // handle category bindings
      if (is_scalar($categories) && !empty($config['col_binding'][md5($categories)])) {
        if (!empty($config['include_subcat'])) {
          $addCategories = array();
          
          foreach ((array) $config['col_binding'][md5($categories)] as $colBindId) {
            $parent_query = $this->db->query("SELECT parent_id, category_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int) $colBindId. "'")->row;
            
            if ($config['include_subcat'] == 'parent') {
              if (!empty($parent_query['parent_id'])) {
                $addCategories[] = $parent_query['parent_id'];
              }
            } else if ($config['include_subcat'] == 'all') {
              while (!empty($parent_query['parent_id'])) {
                $addCategories[] = $parent_query['parent_id'];
                $parent_query = $this->db->query("SELECT parent_id, category_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int) $parent_query['parent_id']. "'")->row;
              }
            }
            
            $config['col_binding'][md5($categories)] = array_merge($config['col_binding'][md5($categories)], $addCategories);
          }
        }
      
        $categories = $config['col_binding'][md5($categories)];
        
        if ($this->simulation) {
          foreach ((array) $categories as $colBindId) {
            $query = $this->db->query("SELECT cd.name, c.parent_id, c.category_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int) $colBindId. "'")->row;
            if (!empty($query['category_id'])) {
              $cat_name = $query['name'];
              $cat_id = $query['category_id'];
              while (!empty($query['parent_id'])) {
                $query = $this->db->query("SELECT cd.name, c.parent_id, c.category_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int) $query['parent_id']. "'")->row;
                
                if (!empty($query['name'])) {
                  $cat_name = $query['name'] . ' <b>></b> ' . $cat_name;
                }
              }
              $values[] = '['.$cat_id.'] ' . $cat_name;
            } else {
              $this->session->data['obui_processed']['error']++;
        
              $this->tool->log(array(
                'row' => $this->session->data['obui_current_line'],
                'status' => 'error',
                'title' => $this->language->get('warning'),
                'msg' => $this->language->get('warning_category_id'),
              ));
            }
          }
        } else {
          $values = array_merge($categories, $values);
        }
        
        continue;
      } else if (isset($config['col_binding_mode']) && $config['col_binding_mode'] == '2') {
        throw new GkdSkipException($this->language->get('info_col_binding_skip'));
        
        continue;
      } else if (isset($config['col_binding_mode']) && $config['col_binding_mode'] == '1') {
        continue;
      } else if (isset($config['col_binding_mode']) && $config['col_binding_mode'] == '3') {
        continue;
      }
      
      if (is_string($categories) && !empty($config['category_separator']) && strpos($categories, $config['category_separator']) !== false) {
        $categories = explode(@html_entity_decode($config['category_separator'], ENT_QUOTES, 'UTF-8'), $categories);
      } else if (is_string($categories) && !empty($config['multiple_separator']) && strpos($categories, $config['multiple_separator']) !== false) {
        $categories = explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $categories);
      }
      
      $this->load->model('localisation/language');
      $languages = $this->model_localisation_language->getLanguages();
      
      // xml fix
      if ($this->filetype == 'xml' && isset($categories['category']) && !isset($categories['category']['nameEn'])) {
        $categories = $categories['category'];
      }
      
      if (!empty($config['include_subcat'])) {
        $addCategories = array();
        
        foreach ((array) $categories as $ckey => $category) {
          if (ctype_digit($category) && $category > 0) {
            $parent_query = $this->db->query("SELECT parent_id, category_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int) $category. "'")->row;
            if ($config['include_subcat'] == 'parent') {
              if (!empty($parent_query['parent_id'])) {
                $addCategories[] = $parent_query['parent_id'];
              }
            } else if ($config['include_subcat'] == 'all') {
              while (!empty($parent_query['parent_id'])) {
                $addCategories[] = $parent_query['parent_id'];
                $parent_query = $this->db->query("SELECT parent_id, category_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int) $parent_query['parent_id']. "'")->row;
              }
            }
          } else if (!empty($config['subcategory_separator']) && is_string($category)) {
            $category = html_entity_decode($category, ENT_QUOTES, 'UTF-8');
            $subcategories = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $category);
            $subcategories = array_map('trim', $subcategories);
            $subcategories = array_filter($subcategories);
            
            if ($config['include_subcat'] == 'parent') {
              array_pop($subcategories);
              
              $subcat = implode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $subcategories);
            
              if ($subcat) {
                $addCategories[] = $subcat;
              }
            } else if ($config['include_subcat'] == 'all') {
              while ($subcategories) {
                array_pop($subcategories);
                
                $subcat = implode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $subcategories);
              
                if ($subcat) {
                  $addCategories[] = $subcat;
                }
              }
            }
          }
        }
        
        $categories = array_merge((array) $categories, $addCategories);
      }
      
      $categories = array_unique((array) $categories);
      
      foreach ($categories as $ckey => $category) {
        $full_categories_ml = array();
        
        // xml fix
        if ($this->filetype == 'xml' && isset($category['nameEn'])) {
          foreach ($languages as $language) {
            $category_ml[$language['code']] = !empty($value['name'.ucfirst(substr($language['code'], 0, 2))]) ? $value['name'.ucfirst(substr($language['code'], 0, 2))] : '';
            
            if (!empty($config['subcategory_separator'])) {
              $subcategories_ml[$language['code']] = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $category_ml[$language['code']]);
            } else {
              $subcategories_ml[$language['code']] = (array) $category_ml[$language['code']];
            }
            
            $full_categories_ml = $subcategories_ml;
          }
          
          $category = $category['nameEn'];
        }
        /*
        if (isset($category['nameEn'])) {
          $categoryFr = $category['nameFr'];
          if (!empty($config['subcategory_separator'])) {
            $subcategoriesFr = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $categoryFr);
          } else {
            $subcategoriesFr = (array) $categoryFr;
          }
          $full_categoriesFr = $subcategoriesFr;
          
          $category = $category['nameEn'];
        }
        */
        
        # else we treat as csv
        // direct cat id 
        if (ctype_digit($category) && $category > 0) {
          if ($this->simulation) {
            $query = $this->db->query("SELECT name, category_id FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int) $category . "'")->row;
            if (!empty($query['category_id'])) {
              $values[] = '['.$query['category_id'].'] ' . $query['name'];
            } else {
              $this->session->data['obui_processed']['error']++;
        
              $this->tool->log(array(
                'row' => $this->session->data['obui_current_line'],
                'status' => 'error',
                'title' => $this->language->get('warning'),
                'msg' => $this->language->get('warning_category_id'),
              ));
            }
          } else {
            $values[] = $category;
          }
          
          continue;
        }
        
        if (!empty($config['subcategory_separator']) && is_string($category)) {
          $category = html_entity_decode($category, ENT_QUOTES, 'UTF-8');
          $subcategories = explode(@html_entity_decode($config['subcategory_separator'], ENT_QUOTES, 'UTF-8'), $category);
          $subcategories = array_map('trim', $subcategories);
          $subcategories = array_filter($subcategories);
        } else {
          $subcategories = (array) $category;
        }
        
        $full_categories = $subcategories;
        
        $cat_name = array_pop($subcategories);
        
        if (!is_string($cat_name)) {
          continue;
        }
        
        // detect cat id on top category
        if (isset($full_categories[0]) && ctype_digit($full_categories[0]) && $full_categories[0] > 0) {
          if ($this->simulation) {
            $query = $this->db->query("SELECT name, category_id FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int) $full_categories[0] . "'")->row;
            
            if (!empty($query['category_id'])) {
              $full_categories[0] = $query['name'];
            }
          }
        }
        
        $parent_name = $parent_lvl2_name = $parent_lvl3_name = false;
        
        if (count($subcategories)) {
          $parent_name = array_pop($subcategories);
        }
        if (count($subcategories)) {
          $parent_lvl2_name = array_pop($subcategories);
        }
        
        if (count($subcategories)) {
          $parent_lvl3_name = array_pop($subcategories);
        }
        
        // 2 parents levels detection, then 1, then 0
        if (!empty($parent_lvl3_name)) {
          if (is_array($cat_name) || is_array($parent_name) || is_array($parent_lvl2_name) || is_array($parent_lvl3_name)) continue;
          $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id LEFT JOIN " . DB_PREFIX . "category_description pcd ON pcd.category_id = c.parent_id LEFT JOIN " . DB_PREFIX . "category pc ON pc.category_id = pcd.category_id LEFT JOIN " . DB_PREFIX . "category_description ppcd ON ppcd.category_id = pc.parent_id LEFT JOIN " . DB_PREFIX . "category ppc ON ppc.category_id = ppcd.category_id LEFT JOIN " . DB_PREFIX . "category_description pppcd ON pppcd.category_id = ppc.parent_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND pcd.name = '" . $this->db->escape(trim($this->request->clean($parent_name))) . "' AND ppcd.name = '" . $this->db->escape(trim($this->request->clean($parent_lvl2_name))) . "' AND pppcd.name = '" . $this->db->escape(trim($this->request->clean($parent_lvl3_name))) . "' GROUP BY cd.category_id")->rows;
        } else if (!empty($parent_lvl2_name)) {
          if (is_array($cat_name) || is_array($parent_name) || is_array($parent_lvl2_name)) continue;
          $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id LEFT JOIN " . DB_PREFIX . "category_description pcd ON pcd.category_id = c.parent_id LEFT JOIN " . DB_PREFIX . "category pc ON pc.category_id = pcd.category_id LEFT JOIN " . DB_PREFIX . "category_description ppcd ON ppcd.category_id = pc.parent_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND pcd.name = '" . $this->db->escape(trim($this->request->clean($parent_name))) . "' AND ppcd.name = '" . $this->db->escape(trim($this->request->clean($parent_lvl2_name))) . "' GROUP BY cd.category_id")->rows;
        } else if (!empty($parent_name)) {
          if (is_array($cat_name) || is_array($parent_name)) continue;
          $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id LEFT JOIN " . DB_PREFIX . "category_description pcd ON pcd.category_id = c.parent_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND pcd.name = '" . $this->db->escape(trim($this->request->clean($parent_name))) . "' GROUP BY cd.category_id")->rows;
        } else {
          if (is_array($cat_name)) continue;
          $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id WHERE cd.name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' OR c.code = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' GROUP BY cd.category_id")->rows;
        }
        
        if (count($query) === 1) {
          if ($this->simulation) {
            if (count($full_categories) > 1) {
              $values[] = '['.$query[0]['category_id'].'] ' . implode(' <b>></b> ', $full_categories);
            } else {
              $values[] = '['.$query[0]['category_id'].'] ' . $query[0]['name'];
            }
          } else {
            $values[] = $query[0]['category_id'];
          }
          /* no more useful, filtered by query
        } else if (!empty($parent_name) && count($query) > 1) {
          foreach ($query as $row) {
            $parent_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int) $row['category_id'] . "'")->row;

            if (!empty($parent_query['name']) && trim($parent_query['name']) == trim($this->request->clean($parent_name))) {
              if ($this->simulation) {
                $values[] = '['.$row['category_id'].'] ' . implode(' <b>></b> ', $full_categories);
              } else {
                $values[] = $row['category_id'];
              }
            }
          }
          */
        } else if (!empty($config['category_create'])) {
          // category does not exists, create it ?
          $this->load->model('catalog/category');
          
          $parent_id = 0;
          
          $gkd_extra_fields = !empty($config['extra']) ? $config['extra'] : array();
          $gkd_extra_desc_fields = !empty($config['extraml']) ? $config['extraml'] : array();
          
          foreach ($full_categories as $cat_name) {
            $cat_name_ml = array();
            
            if ($force_parent_id) {
              $parent_id = $force_parent_id;
            }
            
            // xml fix
            foreach ($full_categories_ml as $lang_id => $cat) {
              $cat_name_ml[$lang_id] = trim(array_shift($cat));
            }
            
            /*
            if (isset($full_categoriesFr)) {
              $cat_name_ml['fr-fr'] = trim(array_shift($full_categoriesFr));
            }*/
            
            if ($ids_field && !empty($config['columns'][$ids_field][$key][$ckey])) {
              $current_cat_id = $config['columns'][$ids_field][$key][$ckey];
              $cat_exists = $this->db->query("SELECT cd.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id WHERE c.category_id = '".(int) $current_cat_id."' AND c.parent_id = '".(int) $parent_id."'")->row;
            } else {
              $cat_exists = $this->db->query("SELECT cd.category_id FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category c ON cd.category_id = c.category_id WHERE name = '" . $this->db->escape(trim($this->request->clean($cat_name))) . "' AND c.parent_id = '".(int) $parent_id."'")->row;
            }
            
            if (empty($cat_exists['category_id'])) {
              $cat_data = array(
                'parent_id' => $parent_id,
                'column' => 3,
                'top' => 1,
                'sort_order' => 0,
                'category_store' => isset($config['columns']['product_store']) ? $config['columns']['product_store'] : (isset($config['defaults']['product_store']) ? $config['defaults']['product_store'] : array(0)),
                'status' => 1,
                'noindex' => '',
                'keyword' => $this->tool->urlify($cat_name),
              );
              
              if (!empty($current_cat_id)) {
                $cat_data['category_id'] = $current_cat_id;
              }
            
              foreach ($gkd_extra_fields as $extra_field) {
                $cat_data[$extra_field] = isset($config['columns'][$extra_field]) ? $config['columns'][$extra_field] : '';
              }
              
              $cat_data['category_seo_url'] = array();
              
              foreach ($languages as $language) {
                $cat_data['category_description'][$language['language_id']] = array(
                 'name' => !empty($cat_name_ml[$language['code']]) ? $cat_name_ml[$language['code']] : trim($cat_name),
                 'description' => '',
                 'meta_title' => !empty($cat_name_ml[$language['code']]) ? $cat_name_ml[$language['code']] : trim($cat_name),
                 'meta_description' => '',
                 'meta_keyword' => '',
                 'seo_h1' => '',
                 'meta_h1' => '',
                 'custom_imgtitle' => '',
                 'custom_alt' => '',
                 'custom_h1' => '',
                 'custom_h2' => '',
                 'smp_h1_title' => '',
                 'tag' => '',
                 'seo_keyword' => !empty($cat_name_ml[$language['code']]) ? $this->tool->urlify($cat_name_ml[$language['code']]) : $this->tool->urlify($cat_name),
                );
                
                foreach ($gkd_extra_desc_fields as $extra_field) {
                  $cat_data['category_description'][$language['language_id']][$extra_field] = isset($config['columns']['product_description'][$language['language_id']][$extra_field]) ? $config['columns']['product_description'][$language['language_id']][$extra_field] : '';
                }
              }
              
              // for Complete SEO Package
              if ($this->config->get('mlseo_enabled') && $this->config->get('mlseo_multistore')) {
                $this->load->model('setting/store');
                $stores = $this->model_setting_store->getStores();
                
                foreach ($stores as $store) {
                  foreach ($languages as $language) {
                    $cat_data['seo_category_description'][$store['store_id']][$language['language_id']] = array(
                      'name' => !empty($cat_name_ml[$language['code']]) ? $cat_name_ml[$language['code']] : trim($cat_name),
                      'description' => '',
                      'seo_keyword' => '',
                      'seo_h1' => '',
                      'seo_h2' => '',
                      'seo_h3' => '',
                      'meta_title' => '',
                      'meta_description' => '',
                      'meta_keyword' => '',
                    );
                  }
                }
              }
              
              if (!$this->simulation) {
                if (version_compare(VERSION, '2', '>=')) {
                  $parent_id = $this->model_catalog_category->addCategory($this->request->clean($cat_data));
                } else {
                  $this->load->model('gkd_import/category');
                  $parent_id = $this->model_gkd_import_category->addCategory($this->request->clean($cat_data));
                }
              }
            } else {
              $parent_id = $cat_exists['category_id'];
            }
          }
          
          // last id is assigned category
          if ($this->simulation) {
            if (!empty($current_cat_id)) {
              $values[] = '['.$this->language->get('new').'] [' . $current_cat_id . '] ' . implode(' <b>></b> ', $full_categories);
            } else {
              $values[] = '['.$this->language->get('new').'] ' . implode(' <b>></b> ', $full_categories);
            }
          } else {
            $values[] = $parent_id;
          }
        }
      }
    }
    
    if (empty($values) && isset($config['col_binding_mode']) && $config['col_binding_mode'] == '3') {
      throw new GkdSkipException($this->language->get('info_col_binding_skip_empty'));
    }
    
    return array_unique($values);
  }
  
  public function customerCustomFieldsHandler($custom_field_id, $value, $config) {
    $return_values = $values_array = array();
    
    // Get Custom Fields
    if (version_compare(VERSION, '2', '>=')) {
      if (version_compare(VERSION, '2.2', '>=')) {  
        $this->load->model('customer/custom_field');
        $custom_field = $this->model_customer_custom_field->getCustomField($custom_field_id);
      } else {
        $this->load->model('sale/custom_field');
        $custom_field = $this->model_sale_custom_field->getCustomField($custom_field_id);
      }
    }
    
    if (in_array($custom_field['type'], array('radio', 'select'))) {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value cfv LEFT JOIN " . DB_PREFIX . "custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfvd.name = '" . $this->db->escape($value) . "'")->row;
      
      if (!empty($query['custom_field_value_id'])) {
        $return_values = $query['custom_field_value_id'];
      }
    } else if (in_array($custom_field['type'], array('checkbox'))) {
      foreach ((array) $value as $val) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value cfv LEFT JOIN " . DB_PREFIX . "custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfvd.name = '" . $this->db->escape($value) . "'")->row;
        
        if (!empty($query['custom_field_value_id'])) {
          $return_values[] = $query['custom_field_value_id'];
        }
      }
    } else {
      $return_values = $value;
    }

    if ($this->simulation) {
      return $custom_field['name'] . ' <b>></b> ' . implode(', ', (array) $return_values);
    } else {
      return $return_values;
    }
  }
  
  public function relatedHandler($field, $config) {
    $return_values = $values_array = array();
    
    if (empty($config['columns'][$field])) {
      return $values_array;
    }
    
    if (count($config['columns'][$field]) == 1 && $config['columns'][$field][0] === '') {
      return array();
    }
    
    foreach ((array) $config['columns'][$field] as $value) {
      if (is_array($value)) {
        $values_array = array_merge($values_array, $value);
      } else if (!empty($config['multiple_separator'])) {
        $values_array = array_merge($values_array, explode(@html_entity_decode($config['multiple_separator'], ENT_QUOTES, 'UTF-8'), $value));
      } else {
        $values_array[] = $value;
      }
    }
    
    foreach ($values_array as $value) {
      $found = false;
      if (is_string($value) && $value != '') {
        // string, try to find product by name, model, sku, ean, upc
        $query = $this->db->query("SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name = '".$this->db->escape($value)."' OR p.model = '".$this->db->escape($value)."' OR p.sku = '".$this->db->escape($value)."' OR p.ean = '".$this->db->escape($value)."' OR p.upc = '".$this->db->escape($value)."'")->rows;
        
        if (!empty($query)) {
          foreach ($query as $related) {
            if (isset($related['product_id'])) {
              if ($this->simulation) {
                $return_values[$related['product_id']] = '['.$related['product_id'].'] ' . $related['name'];
              } else {
                $return_values[$related['product_id']] = $related['product_id'];
              }
            }
          }
          
          $found = true;
        }
      }
      
      if (!$found && is_numeric($value)) {
        // numeric, consider it is ID
        if ($this->simulation) {
          $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '".(int) $value."'")->row;
          
          if (isset($query['name'])) {
            $return_values[] = '['.$value.'] ' . $query['name'];
            $found = true;
          } else {
            $return_values[] = '['.$value.'] ' . 'Not found, set this as the related product_id';
            $found = 'force_id';
          }
          
        } else {
          $return_values[] = $value;
        }
      }
      
      if (!$found && $this->simulation) {
        $this->tool->log(array(
          'row' => $this->session->data['obui_current_line'],
          'status' => 'error',
          'title' => $this->language->get('warning'),
          'msg' => 'The value "'.$value.'" has not been found as related product',
        ));
        
        $return_values[] = '['.$value.'] Not found';
      }
    }
    
    return $return_values;
  }
  
  public function currencyHandler($field, $config) {
    $value = $config['columns'][$field];

    if (is_numeric($value)) {
      // numeric, treat as status id
      if ($this->simulation) {
        $value = $this->getOrderStatusName($value);
      }
    } else if (is_string($value)) {
      // string, get status id
      $value = $this->getOrderStatusIdFromName($value);
    }
    
    return $value;
  }
  
  private $tax_classes = NULL;
  
  public function taxClassHandler($field, $config) {
    $value = $config['columns'][$field];
    
    // no binding and default value enabled: return default directly
    if ($config['columns_bindings'][$field] == '' && $config['defaults'][$field]) {
      if ($this->simulation) {
        if (!isset($this->tax_classes[$config['defaults'][$field]])) {
          $tax_class = $this->db->query(
            "SELECT tax_class_id, title FROM " . DB_PREFIX . "tax_class
              WHERE tax_class_id = '".(int) $config['defaults'][$field]."'")->row;
              
          $this->tax_classes[$tax_class['tax_class_id']] = $tax_class['title'];
        }
        
        return $this->tax_classes[$config['defaults'][$field]];
      } else {
        return $config['defaults'][$field];
      }
    }
    
    if (!$value) return 0;
    
    // try to detect by title, id, or rate
    if ($value) {
      if (!isset($this->tax_classes)) {
        $this->tax_classes = $this->db->query(
          "SELECT tc.tax_class_id, tc.title, r.rate FROM " . DB_PREFIX . "tax_class tc
            LEFT JOIN " . DB_PREFIX . "tax_rule tr ON (tc.tax_class_id = tr.tax_class_id)
            LEFT JOIN " . DB_PREFIX . "tax_rate r ON (tr.tax_rate_id = r.tax_rate_id)
            WHERE r.type = 'P'")->rows;
      }
      
      foreach ($this->tax_classes as $tax_class) {
        if ($value == $tax_class['title']) {
          return $this->simulation ? $tax_class['title'] : $tax_class['tax_class_id'];
        } else if ($value == $tax_class['tax_class_id']) {
          return $this->simulation ? $tax_class['title'] : $tax_class['tax_class_id'];
        } else if ($value == $tax_class['rate']) {
          return $this->simulation ? $tax_class['title'] : $tax_class['tax_class_id'];
        }
      }
    }
    
    return 0;
  }
  
  public function loadCustomerGroups() {
    if (!$this->customer_groups) {
      $query = $this->db->query("SELECT customer_group_id FROM " . DB_PREFIX . "customer_group")->rows;
      
      foreach ($query as $cg) {
        $this->customer_groups[] = $cg['customer_group_id'];
      }
    }
  }
  
  protected function filterEmptyPrice($val) {
    return isset($val['price']) && !empty($val['price']);
  }
  
  public function getArrayPath($val, $path, $string_only = true) {
    if (!is_null($path) && is_array($val) && array_key_exists($path, $val)) {
      $val = $val[$path];
    } else if (strpos($path, '/')) {
      $arrItems = explode('/', $path);
      //$arrItems = explode('/', str_replace('[0]/', '/0/', $path));
      //$initKey = array_shift($arrItems);
      if (is_array($val)) {
        foreach ($arrItems as $arrItem) {
          array_shift($arrItems);
          
          // get array data with *
          if (!is_null($arrItem) && is_array($val) && $arrItem == '*') {
            $arrayVal = array();
            
            foreach($val as $val) {
              foreach ($arrItems as $arrItem) {
                if (!is_null($arrItem) && is_array($val) && array_key_exists($arrItem, $val)) {
                  $arrayVal[] = $val[$arrItem];
                } else if (isset($val[0]) && is_array($val[0]) && !is_null($arrItem) && array_key_exists($arrItem, $val[0])) {
                  $arrayVal[] = $val[0][$arrItem];
                }
              }
            }
            
            return $arrayVal;
          
          // get normal data
          } else if (!is_null($arrItem) && is_array($val) && array_key_exists($arrItem, $val)) {
            $val = $val[$arrItem];
          } else if (isset($val[0]) && is_array($val[0]) && !is_null($arrItem) && array_key_exists($arrItem, $val[0])) {
            $val = $val[0][$arrItem];
          } else {
            return '';
          }
        }
      }
    } else {
      $val = '';
    }
    
    if ($string_only && !is_scalar($val)) {
      return '';
    }
    
    return $val;
  }
}