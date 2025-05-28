<?php
class ModelGkdExportDriverFilter extends Model {
  private $langIdToCode = array();
  
  public function getItems($data = array(), $count = false) {
    $data['mode'] = isset($data['mode']) ? $data['mode'] : 'product';
    
    $lgquery = $this->db->query("SELECT DISTINCT language_id, code FROM " . DB_PREFIX . "language WHERE status = 1")->rows;
      
    foreach ($lgquery as $lang) {
      $this->langIdToCode[$lang['language_id']] = substr($lang['code'], 0, 2);
    }
    
    $select = ($count) ? "COUNT(DISTINCT f.filter_id) AS total" : "*";
    
    if ($data['mode'] == 'product') {
      $sql = "SELECT ".$select." FROM " . DB_PREFIX . "product_filter pf LEFT JOIN " . DB_PREFIX . "filter f ON (f.filter_id = pf.filter_id)";
    } else {
      $sql = "SELECT ".$select." FROM " . DB_PREFIX . "filter f";
    }
    
    // Where
    $sql .= " WHERE 1";
    
    if (!empty($data['filter_name'])) {
			$sql .= " AND fd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
    
    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY f.sort_order ASC";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

    foreach ($query->rows as &$row) {
      $row += $this->getGroupDescription($row['filter_group_id']);
      $row += $this->getDescription($row['filter_id']);
    }
    
		return $query->rows;
	}
  
  public function getGroupDescription($filter_group_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "filter_group_description WHERE filter_group_id = '" . (int)$filter_group_id . "' ORDER BY language_id ASC");
    
    $res = array();
    //var_dump($query->rows );die;
    foreach ($query->rows as &$row) {
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'filter_group_id'))) {
          if (isset($this->langIdToCode[$row['language_id']])) {
            $res['group_'.$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
          }
        }
      }
      //$res['group_name_'.$this->langIdToCode[$row['language_id']]] = $row['name'];
    }
    
		return $res;
	}
  
  public function getDescription($filter_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "filter_description fd WHERE filter_id = '" . (int)$filter_id . "' ORDER BY language_id ASC");
    
    $res = array();
    
    foreach ($query->rows as &$row) {
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'filter_id', 'filter_group_id'))) {
          if (isset($this->langIdToCode[$row['language_id']])) {
            $res[$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
          }
        }
      }
    }
    
		return $res;
	}
  
  public function getTotalItems($data = array()) {
    return $this->getItems($data, true);
  }
}