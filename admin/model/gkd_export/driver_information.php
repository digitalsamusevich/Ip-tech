<?php
class ModelGkdExportDriverInformation extends Model {
  private $langIdToCode = array();
  private $stores = array();
  
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
    
    $select = ($count) ? 'COUNT(DISTINCT i.information_id) AS total' : '*';
    
    $description_field = '';
    if ($store_id && $this->config->get('mlseo_multistore')) {
      $description_field = 'seo_';
    }
    
    $sql = "SELECT ".$select." FROM " . DB_PREFIX . "information i";

    if (isset($data['filter_language']) && $data['filter_language'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . $description_field . "information_description id ON (i.information_id = id.information_id)";
    }
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id)";
    }
    
    // WHERE
    // languages
    if (isset($data['filter_language']) && $data['filter_language'] !== '') {
      $sql .= " WHERE id.language_id = '" . (int)$data['filter_language'] . "'";
    } else {
      $lgquery = $this->db->query("SELECT DISTINCT language_id, code FROM " . DB_PREFIX . "language WHERE status = 1")->rows;
      
      foreach ($lgquery as $lang) {
        $this->langIdToCode[$lang['language_id']] = substr($lang['code'], 0, 2);
      }
      
      $sql .= " WHERE 1";
    }
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " AND i2s.store_id = '" . (int)$data['filter_store'] . "'";
      
      if (isset($data['filter_language']) && $data['filter_language'] !== '' && $store_id && $this->config->get('mlseo_multistore')) {
        $sql .= " AND id.store_id = '" . (int)$data['filter_store'] . "'";
      }
    }
    
    if (!empty($data['filter_status'])) {
      $sql .= " AND i.status = '" . (int)$data['filter_status'] . "'";
    }
    
		if (!empty($data['filter_name'])) {
			$sql .= " AND id.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
    
    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY i.information_id";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
      
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

    foreach ($query->rows as &$row) {
      //$row['store'] = $store['name'];
      $row['store'] = $this->getInformationStores($row['information_id']);
      
      if (isset($data['filter_language']) && $data['filter_language'] === '') {
        $row += $this->getInformationDescription($row['information_id'], $store_id);
      }
    }
		return $query->rows;
	}
  
  public function getInformationDescription($information_id, $store_id) {
    if ($store_id && $this->config->get('mlseo_multistore')) {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_information_description WHERE information_id = '" . (int)$information_id . "' AND store_id = '".(int) $store_id."' ORDER BY language_id ASC");
    } else {
      $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "' ORDER BY language_id ASC");
    }
    
    $res = array();
    
    foreach ($query->rows as &$row) {
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'information_id'))) {
          if (isset($this->langIdToCode[$row['language_id']])) {
            $res[$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
          }
        }
      }
    }
    
		return $res;
	}

  public function getInformationStores($information_id, $asArray = false) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");
    
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