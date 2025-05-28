<?php
class ModelGkdExportDriverManufacturer extends Model {
  private $langIdToCode = array();
  private $stores = array();
  private $front_url;
  private $url_alias_table;
  
  public function getItems($data = array(), $count = false, $asArray = false) {
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
    
    $lgquery = $this->db->query("SELECT DISTINCT language_id, code FROM " . DB_PREFIX . "language WHERE status = 1")->rows;
    
    foreach ($lgquery as $lang) {
      $this->langIdToCode[$lang['language_id']] = substr($lang['code'], 0, 2);
    }
      
    $select = "*";
    
    if ($count) {
      $select = 'COUNT(DISTINCT m.manufacturer_id) AS total';
    } else {
      if (empty($data['param_image_path'])) {
        $select = "*, CONCAT('".HTTP_CATALOG."image/', m.image) as image";
      }
    }
    
    $sql = "SELECT ".$select." FROM " . DB_PREFIX . "manufacturer m";
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id)";
    }
    
    // Where
    $sql .= " WHERE 1";
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " AND m2s.store_id = '" . (int)$data['filter_store'] . "'";
    }
    
    if (!empty($data['filter_status'])) {
      $sql .= " AND m.status = '" . (int)$data['filter_status'] . "'";
    }
    
		if (!empty($data['filter_name'])) {
			$sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY m.manufacturer_id";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

    foreach ($query->rows as &$row) {
      //$row['store'] = $store['name'];
      $row['store'] = $this->getManufacturerStores($row['manufacturer_id'], $asArray);
      $row += $this->getManufacturerDescription($row['manufacturer_id'], $store_id);
    }
    
		return $query->rows;
	}
  
  public function getManufacturerStores($manufacturer_id, $asArray = false) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    
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
  
  public function getManufacturerDescription($manufacturer_id, $store_id) {
    $res = array();
    
    if (!$this->config->get('mlseo_enabled')) {
      return $res;
    }
    
    $seoDescription = array();
    $seo_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND store_id = '".(int) $store_id."' ORDER BY language_id ASC")->rows;
    foreach ($seo_query as $seo_desc) {
      $seoDescription[$seo_desc['language_id']] = $seo_desc;
    }
    
    foreach ($this->langIdToCode as $lang_id => $lang_code) {
      foreach (array('name', 'description', 'meta_title', 'meta_description', 'meta_keyword', 'seo_h1', 'seo_h2', 'seo_h3') as $key) {
        if (isset($seoDescription[$lang_id][$key]) && trim(strip_tags($seoDescription[$lang_id][$key]))) {
          $res[$key.'_'.$lang_code] = $seoDescription[$lang_id][$key];
        } else {
          $res[$key.'_'.$lang_code] = '';
        }
      }
    }
    
		return $res;
	}
  
  public function getTotalItems($data = array()) {
    return $this->getItems($data, true);
  }
}