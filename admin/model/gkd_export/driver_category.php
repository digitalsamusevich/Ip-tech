<?php
class ModelGkdExportDriverCategory extends Model {
  private $langIdToCode = array();
  private $stores = array();
  private $front_url;
  private $url_alias_table;
  
  public function getItems($data = array(), $count = false, $asArray = false) {
    $rows = array();
    
    $this->url_alias_table = version_compare(VERSION, '3', '>=') ? 'seo_url' : 'url_alias';
    
    $select = ($count) ? 'COUNT(DISTINCT c.category_id) AS total' : '*';
    
    // seo urls
    if (!class_exists('GkdUrl')) {
      require_once(DIR_SYSTEM.'library/gkd_url.php');
    }
    
    $this->load->model('setting/store');
		$this->stores = array();
		$this->stores[0] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name'),
			'url'     => HTTP_CATALOG,
      'ssl' => HTTPS_CATALOG,
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$action = array();

			$this->stores[$store['store_id']] = $store;
		}
    
    // store_id for use with URL and multistore, set to 0 if empty
    if (!empty($data['filter_store'])) {
      $store_id = $data['filter_store'];
    } else {
      $store_id = 0;
    }
    
    $store = $this->stores[$store_id];
    
    $filter_language = isset($data['filter_language']) ? $data['filter_language'] : '';
    
    if (!empty($filter_language)) {
      $this->config->set('config_language_id', $filter_language);
    }
    
    $this->front_url = new GkdUrl($this->registry, $store['url'], $store['ssl']);
    
    $description_field = '';
    if ($store_id && $this->config->get('mlseo_multistore')) {
      $description_field = 'seo_';
    }
    
    $sql = "SELECT ".$select." FROM " . DB_PREFIX . "category c";
    
    if (isset($data['filter_language']) && $data['filter_language'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . $description_field . "category_description cd ON (c.category_id = cd.category_id)";
    }
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)";
    }
    
    $lgquery = $this->db->query("SELECT DISTINCT language_id, code FROM " . DB_PREFIX . "language WHERE status = 1")->rows;
    
    foreach ($lgquery as $lang) {
      $this->langIdToCode[$lang['language_id']] = substr($lang['code'], 0, 2);
    }
    
    // WHERE
    // languages
    if (isset($data['filter_language']) && $data['filter_language'] !== '') {
      $sql .= " WHERE cd.language_id = '" . (int)$data['filter_language'] . "'";
    } else {
      $sql .= " WHERE 1";
    }
    
    if (!empty($data['filter_parent'])) {
      $sql .= " c.parent_id = '" . (int)$data['filter_parent'] . "'";
    }
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " AND c2s.store_id = '" . (int)$data['filter_store'] . "'";
      
      if (isset($data['filter_language']) && $data['filter_language'] !== '' && $store_id && $this->config->get('mlseo_multistore')) {
        $sql .= " AND cd.store_id = '" . (int)$data['filter_store'] . "'";
      }
    }
    
    if (!empty($data['filter_status'])) {
      $sql .= " AND c.status = '" . (int)$data['filter_status'] . "'";
    }
    
		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
    
    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY c.category_id";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

    foreach ($query->rows as &$row) {
      if ((!$this->config->get('mlseo_enabled') || (!$this->config->get('mlseo_multistore') && !$this->config->get('mlseo_ml_mode'))) && version_compare(VERSION, '3', '<')) {
        $seoQuery = $this->db->query("SELECT keyword FROM " . DB_PREFIX . $this->url_alias_table . " u WHERE query = 'category_id=".(int) $row['category_id']."' LIMIT 1")->row;
        $row['seo_keyword'] = isset($seoQuery['keyword']) ? $seoQuery['keyword'] : '';
      }
      
      //$row['store'] = $store['name'];
      $row['store'] = $this->getCategoryStores($row['category_id']);
      
      if (version_compare(VERSION, '1.5.6', '>=')) {
        $row['link'] = $this->front_url->link('product/category', 'path='.$row['category_id']);
      }
      
      if (!empty($data['filter_language'])) {
        $row['full_path'] = $this->getFullCategoryPath($row, $store_id, $data['filter_language']);
        $row['parent_name'] = $this->getParentName($row, $store_id, $data['filter_language']);
        
        if (version_compare(VERSION, '1.5.6', '>=')) {
          $row['filters_names'] = $this->getCategoryFilters($row['category_id'], true, $data['filter_language']);
        }
      }
      
      if (isset($data['filter_language']) && $data['filter_language'] === '') {
        foreach ($this->langIdToCode as $language_id => $language_code) {
          $row['full_path_'.$language_code] = $this->getFullCategoryPath($row, $store_id, $language_id);
          $row['parent_name_'.$language_code] = $this->getParentName($row, $store_id, $language_id);
          
          if (version_compare(VERSION, '1.5.6', '>=')) {
            $row['filters_names_'.$language_code] = $this->getCategoryFilters($row['category_id'], true, $language_id);
          }
        }
        
        $row += $this->getCategoryDescription($row['category_id'], $store_id);
      }
      
      if (version_compare(VERSION, '1.5.6', '>=')) {
        $row['filters_ids'] = $this->getCategoryFilters($row['category_id']);
      }
    }
    
		return $query->rows;
	}
  
  public function getCategoryFilters($category_id, $names = false, $language_id = false) {
		$category_filter_data = array();

    if ($names) {
      $query = $this->db->query("SELECT cf.filter_id, fgd.name as 'group', fd.name FROM " . DB_PREFIX . "category_filter cf
        LEFT JOIN " . DB_PREFIX . "filter f ON (cf.filter_id = f.filter_id)
        LEFT JOIN " . DB_PREFIX . "filter_description fd ON (cf.filter_id = fd.filter_id)
        LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id)
        LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fd.filter_group_id = fgd.filter_group_id)
        WHERE cf.category_id = '" . (int)$category_id . "'
        AND fd.language_id = '" . (int)$language_id . "'
        AND fgd.language_id = '" . (int)$language_id . "'
        ORDER BY f.sort_order, fg.sort_order");

      foreach ($query->rows as $result) {
        if (!empty($result['group']) && !empty($result['name'])) {
          $category_filter_data[$result['filter_id']] = $result['group'] . ':' . $result['name'];
        }
      }
    } else {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

      foreach ($query->rows as $result) {
        $category_filter_data[] = $result['filter_id'];
      }
		}

		return implode('|', $category_filter_data);
	}
  
  public function getCategoryDescription($category_id, $store_id) {
    if ($store_id && $this->config->get('mlseo_multistore')) {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_category_description WHERE category_id = '" . (int)$category_id . "' AND store_id = '".(int) $store_id."' ORDER BY language_id ASC");
    } else {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' ORDER BY language_id ASC");
    }
    
    $res = array();
    
    foreach ($query->rows as &$row) {
      if ($this->config->get('mlseo_enabled') || version_compare(VERSION, '3', '>=')) {
        if (version_compare(VERSION, '3', '>=') || ($this->config->get('mlseo_multistore') && $this->config->get('mlseo_ml_mode'))) {
          $seoQuery = $this->db->query("SELECT keyword FROM " . DB_PREFIX . $this->url_alias_table . " u WHERE query = 'category_id=".(int) $category_id."' AND u.language_id = '".(int) $row['language_id']."' AND store_id = '".(int) $store_id."' LIMIT 1")->row;
        } else if ($this->config->get('mlseo_multistore')) {
          $seoQuery = $this->db->query("SELECT keyword FROM " . DB_PREFIX . $this->url_alias_table . " u WHERE query = 'category_id=".(int) $category_id."' AND store_id = '".(int) $store_id."' LIMIT 1")->row;
        } else if ($this->config->get('mlseo_ml_mode')) {
          $seoQuery = $this->db->query("SELECT keyword FROM " . DB_PREFIX . $this->url_alias_table . " u WHERE query = 'category_id=".(int) $category_id."' AND u.language_id = '".(int) $row['language_id']."' LIMIT 1")->row;
        }
        
        if (isset($seoQuery)) {
          if (isset($this->langIdToCode[$row['language_id']])) {
            $res['seo_keyword_'.$this->langIdToCode[$row['language_id']]] = isset($seoQuery['keyword']) ? $seoQuery['keyword'] : '';
          }
        }
      }
      
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'category_id'))) {
          if (isset($this->langIdToCode[$row['language_id']])) {
            $res[$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
          }
        }
      }
    }
    
		return $res;
	}

  public function getFullCategoryPath($category, $store_id, $language_id) {
    $description_field = '';
    if ($store_id && $this->config->get('mlseo_multistore')) {
      $description_field = 'seo_';
    }
    
    if (!isset($category['name'])) {
      $category = $this->db->query("SELECT cd.name, c.category_id, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . $description_field . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . $category['category_id']. "' AND cd.language_id = '".(int) $language_id."'")->row;
    }
    
    if (!isset($category['name'])) {
      return '';
    }
    
    $path = '';
    
    $path = $category['name'];
    
    while (!empty($category['parent_id'])) {
      $category = $this->db->query("SELECT cd.name, c.category_id, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . $description_field . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . $category['parent_id']. "' AND cd.language_id = '".(int) $language_id."'")->row;
      if (isset($category['name'])) {
        $path = $category['name'] . '>' . $path;
      }
    }
    
    return $path;
	}
  
  public function getParentName($category, $store_id, $language_id) {
    if (empty($category['parent_id'])) {
      return '';
    }
    
    $description_field = '';
    if ($store_id && $this->config->get('mlseo_multistore')) {
      $description_field = 'seo_';
    }
    
    $parent_category = $this->db->query("SELECT cd.name, c.category_id, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . $description_field .  "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . $category['parent_id']. "' AND cd.language_id = '".(int) $language_id."'")->row;
    
    if (!empty($parent_category['name'])) {
      return $parent_category['name'];
    }
    
    return '';
	}
  
  public function getCategoryStores($category_id, $asArray = false) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");
    
    if ($asArray) {
      $return = array();
      foreach ($query->rows as $item) {
        $return[] = isset($this->stores[$item['store_id']]['name']) ? $this->stores[$item['store_id']]['name'] : $item['store_id'];
      }
      
      return $return;
    }
    
    $res = '';
    
    // get formatted string for CSV
    foreach ($query->rows as $item) {
      $res .= ($res !== '') ? '|' : '';
      $res .= isset($this->stores[$item['store_id']]['name']) ? $this->stores[$item['store_id']]['name'] : $item['store_id'];
    }
    
		return $res;
  }
  
  public function getTotalItems($data = array()) {
    return $this->getItems($data, true);
  }
}