<?php
class ModelGkdImportProduct extends Model {
  public function __construct($registry) {
    parent::__construct($registry);
    
    if (version_compare(VERSION, '3', '>=')) {
      $this->url_alias = 'seo_url';
    } else {
      $this->url_alias = 'url_alias';
    }
  }
  
	public function editProduct($product_id, $data, $config) {
    $product_data = array('product_id', 'model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 'quantity', 'minimum', 'subtract', 'stock_status_id',
                          'date_available', 'manufacturer_id', 'shipping', 'price', 'points', 'weight', 'weight_class_id', 'length', 'width', 'height', 'length_class_id',
                          'status', 'tax_class_id', 'sort_order', 'image');
    
    if (!empty($data['gkd_extra_fields'])) {
      $product_data = array_merge($product_data, $data['gkd_extra_fields']);
    }
    
    // do not insert image in add mode
    if (!empty($config['image_insert_type']) && $config['image_insert_type'] == 'rm_add' && in_array($product_id, $this->session->data['obui_processed_ids'])) {
      unset($product_data['image']);
    }
    
    $main_query = array();
    
    foreach ($product_data as $item_col) {
      if (isset($data[$item_col])) {
        $main_query[] = "`" . $this->db->escape($item_col) . "` = '" . $this->db->escape($data[$item_col]) . "'";
      }
    }
    
    if (!empty($data['import_batch'])) {
      $main_query[] = "`import_batch` = '" . $this->db->escape($data['import_batch']) . "'";
    }
      
    if (!in_array('date_modified', $product_data)) {
      $main_query[] = "`date_modified` = NOW()";
    }
    
		$this->db->query("UPDATE " . DB_PREFIX . "product SET " . implode(',', $main_query) . " WHERE product_id = '" . (int)$product_id . "'");
    
    if (isset($data['seo_product_description'])) {
      if ($this->config->get('mlseo_multistore')) {
        //$this->load->model('catalog/seo_package');
        $this->setSeoDescriptions('product', $data, $product_id);
      }
    }
    
    if (isset($data['product_description'])) {

      foreach ($data['product_description'] as $language_id => $desc_values) {
        $description_query = '';
        
        if ($this->config->get('mlseo_enabled')) {
          $this->load->model('tool/seo_package');
          
          if (isset($desc_values['seo_keyword']) && !$desc_values['seo_keyword'] && $this->config->get('mlseo_editautourl')) {
            $seo_kw = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_url_pattern'), $language_id, $data);
          } else if (!empty($desc_values['seo_keyword'])) {
            $seo_kw = @html_entity_decode($desc_values['seo_keyword'], ENT_QUOTES, 'UTF-8');
            $seo_kw = $this->model_tool_seo_package->filter_seo($seo_kw, 'product', $product_id, $language_id);
          }
          
          if (!empty($seo_kw)) {
            if (version_compare(VERSION, '3', '>=')) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "' AND language_id = '" . (int)$language_id . "' AND store_id = 0");
              $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET query = 'product_id=" . (int)$product_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($seo_kw) . "', store_id = 0");
            } else if ($this->config->get('mlseo_ml_mode')) {
              $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "' AND language_id = '" . (int)$language_id . "'");
              $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
            } else {
              $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");
              $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
            }
          }
          
          if (isset($desc_values['meta_title']) && !$desc_values['meta_title'] && $this->config->get('mlseo_editautoseotitle')) {
            $desc_values['meta_title'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_title_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['meta_description']) && !$desc_values['meta_description']  && $this->config->get('mlseo_editautometadesc')) {
            $desc_values['meta_description'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_description_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['meta_keyword']) && !$desc_values['meta_keyword']  && $this->config->get('mlseo_editautometakeyword')) {
            $desc_values['meta_keyword'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_keyword_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['seo_h1']) && !$desc_values['seo_h1']  && $this->config->get('mlseo_editautoh1')) {
            $desc_values['seo_h1'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_h1_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['seo_h2']) && !$desc_values['seo_h2']  && $this->config->get('mlseo_editautoh2')) {
            $desc_values['seo_h2'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_h2_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['seo_h3']) && !$desc_values['seo_h3']  && $this->config->get('mlseo_editautoh3')) {
            $desc_values['seo_h3'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_h3_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['image_title']) && !$desc_values['image_title']  && $this->config->get('mlseo_editautoimgtitle')) {
            $desc_values['image_title'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_image_title_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['image_alt']) && !$desc_values['image_alt']  && $this->config->get('mlseo_editautoimgalt')) {
            $desc_values['image_alt'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_image_alt_pattern'), $language_id, $data);
          }
          
          if (isset($desc_values['tag']) && !$desc_values['tag']  && $this->config->get('mlseo_editautotags')) {
            $desc_values['tag'] = $this->model_tool_seo_package->transformProduct($this->config->get('mlseo_product_tag_pattern'), $language_id, $data);
          }
        }
        
        // handle extra data for universal import
        $univimp_extra_desc = '';

        if (!empty($data['gkd_extra_desc_fields'])) {
          foreach ($data['gkd_extra_desc_fields'] as $extra_field) {
            if (isset($value[$extra_field])) {
              $desc_values[$extra_field] = $value[$extra_field];
            }
          }
        }
        
        foreach ($desc_values as $desc_col => $desc_val) {
          $description_query .= "`" . $this->db->escape($desc_col) . "` = '" . $this->db->escape($desc_val) . "',";
        }
        
        $description_query = rtrim($description_query, ',');
        
        $rowExists = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$language_id . "'")->row;
        
        if (!empty($rowExists)) {
          $this->db->query("UPDATE " . DB_PREFIX . "product_description SET " . $description_query . " WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$language_id . "'");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', " . $description_query);
        }
      }
    }
    
    if (isset($data['product_store'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
      
      foreach ($data['product_store'] as $store_id) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
      }
		}

    if (isset($data['product_attribute'])) {
      if (empty($config['preserve_attribute'])) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
      }

      if (!empty($data['product_attribute'])) {
        foreach ($data['product_attribute'] as $product_attribute) {
          if ($product_attribute['attribute_id']) {
            // Removes duplicates
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

            foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
              $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
            }
          }
        }
      }
		}

		if (isset($data['product_option'])) {
      // delete only if replace mode or remove on first iteration
      if (!empty($config['reset_options']) || empty($config['option_insert_type']) || (!empty($config['option_insert_type']) && $config['option_insert_type'] == 'rm_add' && !in_array($product_id, $this->session->data['obui_processed_ids']))) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
      }

      $opt_quantity = 0;
      $updatedOptions = array();
      
			foreach ($data['product_option'] as $product_option) {
        $product_option_id = false; // reset product_option_id in order to correctly detect if exists or not
        
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
            // try to get current option ID
            if (!empty($config['option_insert_type']) && ($config['option_insert_type'] == 'rm_add' || $config['option_insert_type'] == 'add' || $config['option_insert_type'] == 'update')) {
              $opt_query = $this->db->query("SELECT `product_option_id` FROM `" . DB_PREFIX . "product_option` WHERE `product_id` = '" . (int) $product_id . "' AND `option_id` = '" . (int) $product_option['option_id'] . "'")->row;
              
              $product_option_id = isset($opt_query['product_option_id']) ? $opt_query['product_option_id'] : '';
            }
            
            // insert product option if empty
            if (empty($product_option_id)) {
              //$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
              $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
              $product_option_id = $this->db->getLastId();
            }
            
            $updatedOptions[] = (int) $product_option_id;
            
            $updatedOptionsValues = array();
            
						foreach ($product_option['product_option_value'] as $product_option_value) {
							//$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
              
              $extraOptions = '';
              
              if (!empty($product_option_value['product_option_value_id'])) {
                $prod_opt_val_id = $this->db->query("SELECT product_option_value_id FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "'")->row;
                
                $extraOptions .= "product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', ";
              } else if (!empty($config['option_insert_type']) && $config['option_insert_type'] == 'update') {
                $prod_opt_val_id = $this->db->query("SELECT product_option_value_id FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND product_option_id = '" . (int)$product_option_id . "' AND option_id = '" . (int)$product_option['option_id'] . "' AND option_value_id = '" . (int)$product_option_value['option_value_id'] . "'")->row;
              }
            
              foreach ((array) $this->config->get('config_gkdExtraOptionFields') as $extraOption) {
                $extraOptions .= $this->db->escape($extraOption)." = '" . $this->db->escape($product_option_value[$extraOption]) . "', ";
              }
              
              #extra_option_values#
              
              if (empty($prod_opt_val_id['product_option_value_id'])) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', ".$extraOptions." weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                $product_option_value_id = $this->db->getLastId();
              } else {
                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', ".$extraOptions." weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "' WHERE product_option_value_id = '".(int)  $prod_opt_val_id['product_option_value_id']."'");
                $product_option_value_id = $prod_opt_val_id['product_option_value_id'];
              }
              
              $updatedOptionsValues[] = (int) $product_option_value_id;
              
              #extra_option_values_operations#
              
              $opt_quantity += (int) $product_option_value['quantity'];
						}
            
					}
				} else {
          if (isset($product_option['product_option_id'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
            $updatedOptions[] = (int) $product_option['product_option_id'];
          } else {
            //$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
            $updatedOptions[] = $this->db->getLastId();
          }
				}
        
        // delete non included option values
        if (!empty($config['option_insert_type']) && ($config['option_insert_type'] == 'update') && !empty($updatedOptionsValues)) {
          $this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE `product_id` = '" . (int) $product_id . "' AND product_option_id = '" . (int)$product_option_id . "' AND `product_option_value_id` NOT IN (" . implode(',', $updatedOptionsValues) . ")");
        }
			}
      
      // delete non included option 
      if (!empty($config['option_insert_type']) && ($config['option_insert_type'] == 'update') && !empty($updatedOptions)) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE `product_id` = '" . (int) $product_id . "' AND `product_option_id` NOT IN (" . implode(',', $updatedOptions) . ")");
      }
      
      // sum option qty to product qty
      if (!empty($config['option_qty_mode']) && $opt_quantity) {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = quantity + " . (int) $opt_quantity . " WHERE product_id = '" . (int)$product_id . "'");
      }
		}


    if (isset($data['product_discount'])) {
      if (empty($config['discount_insert_type'])) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
      }
      
      foreach ($data['product_discount'] as $product_discount) {
        if (isset($config['discount_insert_type']) && $config['discount_insert_type'] == 'update') {
          $this->db->query("UPDATE " . DB_PREFIX . "product_discount SET quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "' WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$product_discount['customer_group_id'] . "'");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
        }
      }
    }


		if (isset($data['product_special'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
    
		if (isset($data['product_image'])) {
      if (empty($config['image_insert_type']) || (!empty($config['image_insert_type']) && $config['image_insert_type'] == 'rm_add' && !in_array($product_id, $this->session->data['obui_processed_ids']))) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
      }
      
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		if (isset($data['product_download'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}
    
    // Public downloads
    if (isset($data['product_public_download'])) {
      $dl_dir = DIR_SYSTEM.'../files/'.$product_id;
      
      if (!is_dir($dl_dir)) {
        if (!is_writable(dirname($dl_dir))) {
          die('Folder is not writable, make sure to give write access to: '.dirname($dl_dir));
        } else {
          mkdir($dl_dir);
        }
      }
      
      $public_downloads = array();
      
      if (!empty($data['product_public_download']['mask'])) {
        $public_downloads['mask'] = $data['product_public_download']['mask'];
        unset($data['product_public_download']['mask']);
      }
      

      foreach ($data['product_public_download'] as $lang => &$pd) {
        
        foreach ($pd as &$pdl) {
          if (is_file(DIR_SYSTEM.'../files/.tmp/'.$pdl)) {
            rename(DIR_SYSTEM.'../files/.tmp/'.$pdl, $dl_dir.'/'.$pdl);
            $pdl = $product_id . '/' . $pdl;
          } else if (is_file($dl_dir.'/'.$pdl)) {
            $pdl = $product_id . '/' . $pdl;
          }
        }
        
        $public_downloads[$lang] = implode('|', $pd);
      }
      
      if (!empty($public_downloads)) {
        $public_downloads = serialize($public_downloads);
      } else {
        $public_downloads = '';
      }
      
      $this->db->query("UPDATE " . DB_PREFIX . "product SET public_download = '" . $this->db->escape($public_downloads) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		if (isset($data['product_category'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
        
        // set filters to cateogories
        if (!empty($config['filter_to_category'])) {
          if (!empty($data['product_filter']) && !empty($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
              foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
              }
            }
          }
  			}
  		}
		}

		if (isset($data['product_related'])) {
      // delete only if replace mode or remove on first iteration
      if (empty($config['related_insert_type']) || (!empty($config['related_insert_type']) && $config['related_insert_type'] == 'rm_add' && !in_array($product_id, $this->session->data['obui_processed_ids']))) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
      }

			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

			foreach ($data['product_reward'] as $customer_group_id => $value) {
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
				}
			}
		}

		if (isset($data['product_layout'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
    
    if (!$this->config->get('mlseo_enabled')) {
      // v3.x
      if (isset($data['product_seo_url'])) { 
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
        
        foreach ($data['product_seo_url']as $store_id => $language) {
          foreach ($language as $language_id => $keyword) {
            if (!empty($keyword)) {
              $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
            }
          }
        }
      // v2.x
      } else if (isset($data['keyword'])) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");

        if ($data['keyword']) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }
      }
		}

		if (isset($data['product_recurring'])) {
      $this->db->query("DELETE FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = " . (int)$product_id);

			foreach ($data['product_recurring'] as $product_recurring) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_id . ", customer_group_id = " . (int)$product_recurring['customer_group_id'] . ", `recurring_id` = " . (int)$product_recurring['recurring_id']);
			}
		}

    // Compatibility Mega filter
    if ($this->config->get( 'mfilter_plus_version' )) {
      require_once DIR_SYSTEM . 'library/mfilter_plus.php';
      Mfilter_Plus::getInstance($this)->updateProduct($product_id);
    }
    
		$this->cache->delete('product');
	}
  
  public function addProductOption($product_id, $data) {
    foreach ($data['product_option'] as $product_option) {
      // do a product option id exists for this option?
      $prod_opt_id = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'")->row;
      
      if (!empty($prod_opt_id['product_option_id'])) {
        $product_option['product_option_id'] = $prod_opt_id['product_option_id'];
      }
      
      if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
        if (isset($product_option['product_option_value'])) {
          if (isset($product_option['product_option_id'])) {
            //$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
            $product_option_id = $product_option['product_option_id'];
          } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
            $product_option_id = $this->db->getLastId();
          }

          foreach ($product_option['product_option_value'] as $product_option_value) {
            $extraOptions = '';
            
            if (!empty($product_option_value['product_option_value_id'])) {
              $prod_opt_val_id = $this->db->query("SELECT product_option_value_id FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "'")->row;
              
              $extraOptions .= "product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', ";
            } else {
              if ($data['option_insert_type'] != 'add') {
                $prod_opt_val_id = $this->db->query("SELECT product_option_value_id FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND product_option_id = '" . (int)$product_option_id . "' AND option_id = '" . (int)$product_option['option_id'] . "' AND option_value_id = '" . (int)$product_option_value['option_value_id'] . "'")->row;
              }
            }
              
            foreach ((array) $this->config->get('config_gkdExtraOptionFields') as $extraOption) {
              $extraOptions .= $this->db->escape($extraOption)." = '" . $this->db->escape($product_option_value[$extraOption]) . "', ";
            }
            
            #extra_option_values#
            
            if (empty($prod_opt_val_id['product_option_value_id'])) {
              $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', ".$extraOptions." weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
              $product_option_value_id = $this->db->getLastId();
            } else {
              $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "' WHERE product_option_value_id = '".(int)  $prod_opt_val_id['product_option_value_id']."'");
              $product_option_value_id = $prod_opt_val_id['product_option_value_id'];
            }
            
            #extra_option_values_operations#
          }
        }
      } else {
        if (isset($product_option['product_option_id'])) {
          $this->db->query("UPDATE " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
        }
      }
    }
    
    // Compatibility Mega filter
    if ($this->config->get( 'mfilter_plus_version' )) {
      require_once DIR_SYSTEM . 'library/mfilter_plus.php';
      Mfilter_Plus::getInstance($this)->updateProduct($product_id);
    }
  }
  
  public function findProductId($data) {
    $search = array();
    
    if (!empty($data['name'])) {
      $search[] = "pd.name = '". $this->db->escape($data['name']) ."'";
    }
    
    if (!empty($data['model'])) {
      $search[] = "p.model = '". $this->db->escape($data['model']) ."'";
    }
    
    if (empty($search)) {
      return '';
    }
    
    $search = implode(' OR ', $search);
    
    $query = $this->db->query("SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id WHERE " . $search)->row;
    
    if (isset($query['product_id'])) {
      return $query['product_id'];
    }
  }
  
  public function setSeoDescriptions($type, $data, $item_id = false) {
    if (!$this->config->get('mlseo_enabled')) return;
    
    if (!isset($data['seo_'.$type.'_description'])) return;
    
    $this->load->model('tool/seo_package');
    
    $this->db->query("DELETE FROM " . DB_PREFIX . "seo_".$this->db->escape($type)."_description WHERE ".$this->db->escape($type)."_id = '" . (int)$item_id . "'");
    
    foreach ($data['seo_'.$type.'_description'] as $store_id => $languages) {
      
      foreach ($languages as $language_id => $value) {
        $description_query = '';
        
        $data[$type.'_id'] = $item_id; // add item id into dataset for use with patterns
        
        $seo_kw = '';
        $extra_fields = '';
        
        if (isset($value['seo_keyword']) && !$value['seo_keyword'] && $this->config->get('mlseo_insertautourl')) {
          $seo_kw = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_url_pattern'), $language_id, $data);
        } else if (!empty($value['seo_keyword'])) {
          $seo_kw = html_entity_decode($value['seo_keyword'], ENT_QUOTES, 'UTF-8');
        }
        
        if ($seo_kw) {
          $seo_kw = $this->model_tool_seo_package->filter_seo($seo_kw, $type, $item_id, $language_id);
        }
        
        if (version_compare(VERSION, '3', '>=') || ($this->config->get('mlseo_multistore') && $this->config->get('mlseo_ml_mode'))) {
          $this->db->query("INSERT INTO " . DB_PREFIX . $this->url_alias . " SET query = '".$this->db->escape($type)."_id=" . (int)$item_id . "', store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
        } else if ($this->config->get('mlseo_multistore')) {
          $this->db->query("INSERT INTO " . DB_PREFIX . $this->url_alias . " SET query = '".$this->db->escape($type)."_id=" . (int)$item_id . "', store_id = '" . (int)$store_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
        } else if ($type == 'manufacturer') {
          $this->db->query("INSERT INTO " . DB_PREFIX . $this->url_alias . " SET query = '".$this->db->escape($type)."_id=" . (int)$item_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
        }
        
        if (isset($value['meta_title']) && !$value['meta_title'] && $this->config->get('mlseo_insertautoseotitle')) {
          $value['meta_title'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_title_pattern'), $language_id, $data);
        }
        if (isset($value['meta_description']) && !$value['meta_description'] && $this->config->get('mlseo_insertautometadesc')) {
          $value['meta_description'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_description_pattern'), $language_id, $data);
        }
        if (isset($value['meta_keyword']) && !$value['meta_keyword'] && $this->config->get('mlseo_insertautometakeyword')) {
          $value['meta_keyword'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_keyword_pattern'), $language_id, $data);
        }
        if (isset($value['seo_h1']) && !$value['seo_h1'] && $this->config->get('mlseo_insertautoh1')) {
          $value['seo_h1'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_h1_pattern'), $language_id, $data);
        }
        if (isset($value['seo_h2']) && !$value['seo_h2'] && $this->config->get('mlseo_insertautoh2')) {
          $value['seo_h2'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_h2_pattern'), $language_id, $data);
        }
        if (isset($value['seo_h3']) && !$value['seo_h3'] && $this->config->get('mlseo_insertautoh3')) {
          $value['seo_h3'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_h3_pattern'), $language_id, $data);
        }
        if ($type == 'product') {
          if (isset($value['image_title']) && !$value['image_title'] && $this->config->get('mlseo_insertautoimgtitle')) {
            $value['image_title'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_image_title_pattern'), $language_id, $data);
          }
          if (isset($value['image_alt']) && !$value['image_alt'] && $this->config->get('mlseo_insertautoimgalt')) {
            $value['image_alt'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_image_alt_pattern'), $language_id, $data);
          }
        }
        // if (empty($value['tag']) && $this->config->get('mlseo_insertautotags')) {
          // $value['tag'] = $this->model_tool_seo_package->{'transform'.ucfirst($type)}($this->config->get('mlseo_'.$type.'_tag_pattern'), $language_id, $data);
        // }
        
        if (isset($value['description'])) {
          $value['description'] = $value['description'];
        }
        
        if (isset($value['name'])) {
          $value['name'] = $value['name'];
        }
        
        foreach ($value as $desc_col => $desc_val) {
          $description_query .= "`" . $this->db->escape($desc_col) . "` = '" . $this->db->escape($desc_val) . "',";
        }
        
        $rowExists = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_".$this->db->escape($type)."_description WHERE ".$this->db->escape($type)."_id = '" . (int)$item_id . "' AND store_id = '" . (int)$store_id . "' AND language_id = '" . (int)$language_id . "'")->row;
        
        if (!empty($rowExists)) {
          $this->db->query("UPDATE " . DB_PREFIX . "seo_".$this->db->escape($type)."_description SET " . rtrim($description_query, ',') . " WHERE ".$this->db->escape($type)."_id = '" . (int)$item_id . "' AND store_id = '" . (int)$store_id . "' AND language_id = '" . (int)$language_id . "'");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "seo_".$this->db->escape($type)."_description SET product_id = '" . (int)$item_id . "', store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', " . rtrim($description_query, ','));
        }
      }
    }
  }
  
}