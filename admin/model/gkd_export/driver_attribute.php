<?php
class ModelGkdExportDriverAttribute extends Model {
  private $langIdToCode = array();
  
  public function getItems($data = array(), $count = false) {
    $data['mode'] = isset($data['mode']) ? $data['mode'] : 'product';
    
    $filter_lang = false;
    
    if (isset($data['filter_language']) && $data['filter_language'] !== '') {
      $filter_lang = $data['filter_language'];
    }
    
    $lgquery = $this->db->query("SELECT DISTINCT language_id, code FROM " . DB_PREFIX . "language WHERE status = 1")->rows;
    
    foreach ($lgquery as $lang) {
      $this->langIdToCode[$lang['language_id']] = substr($lang['code'], 0, 2);
    }
    
    $select = ($count) ? "COUNT(DISTINCT a.attribute_id) AS total" : "a.*";
    
    if ($data['mode'] == 'product') {
      $sql = "SELECT pa.product_id, ".$select." FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (a.attribute_id = pa.attribute_id)";
    } else {
      $sql = "SELECT ".$select." FROM " . DB_PREFIX . "attribute a";
    }
    
    // Where
    $sql .= " WHERE 1";
    
    if (!empty($data['attribute_name'])) {
			$sql .= " AND fd.name LIKE '%" . $this->db->escape($data['attribute_name']) . "%'";
		}
    
    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    if ($data['mode'] == 'product') {
      // disabled - all values required
      //$sql .= " GROUP BY a.attribute_id";
    }
    
    if ($data['mode'] == 'product') {
      $sql .= " ORDER BY pa.product_id, a.attribute_id, a.sort_order ASC";
    } else {
      $sql .= " ORDER BY a.attribute_id, a.sort_order ASC";
    }
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

    foreach ($query->rows as &$row) {
      // $row += $this->getGroupDescription($row['attribute_group_id'], (isset($row['language_id']) ? $row['language_id'] : false));
      // $row += $this->getDescription($row['attribute_id'], (isset($row['language_id']) ? $row['language_id'] : false));
      $row += $this->getGroupDescription($row['attribute_group_id'], $filter_lang);
      $row += $this->getDescription($row['attribute_id'], $filter_lang);
      if ($data['mode'] == 'product') {
        $row += $this->getValues($row['attribute_id'], $filter_lang);
      }
    }
    
		return $query->rows;
	}
  
  public function getGroupDescription($attribute_group_id, $language_id = false) {
    $filter_lang = '';
    
    if ($language_id) {
      $filter_lang = "AND language_id = '".(int) $language_id."'";
    }
    
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . (int)$attribute_group_id . "' ".$filter_lang." ORDER BY language_id ASC");
    
    $res = array();
    //var_dump($query->rows );die;
    foreach ($query->rows as &$row) {
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'attribute_group_id'))) {
          if ($language_id) {
            $res['group_'.$key] = $val;
          } else {
            if (isset($this->langIdToCode[$row['language_id']])) {
              $res['group_'.$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
            }
          }
        }
      }
      //$res['group_name_'.$this->langIdToCode[$row['language_id']]] = $row['name'];
    }
    
		return $res;
	}
  
  public function getDescription($attribute_id, $language_id = false) {
    $filter_lang = '';
    
    if ($language_id) {
      $filter_lang = "AND language_id = '".(int) $language_id."'";
    }
    
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description fd WHERE attribute_id = '" . (int)$attribute_id . "' ".$filter_lang." ORDER BY language_id ASC");
    
    $res = array();
    
    foreach ($query->rows as &$row) {
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'attribute_id', 'attribute_group_id'))) {
          if ($language_id) {
            $res[$key] = $val;
          } else {
            if (isset($this->langIdToCode[$row['language_id']])) {
              $res[$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
            }
          }
        }
      }
    }
    
		return $res;
	}
  
  public function getValues($attribute_id, $language_id = false) {
    $filter_lang = '';
    
    if ($language_id) {
      $filter_lang = "AND language_id = '".(int) $language_id."'";
    }
    
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute fd WHERE attribute_id = '" . (int)$attribute_id . "' ".$filter_lang." ORDER BY language_id ASC");
    
    $res = array();
    
    foreach ($query->rows as &$row) {
      foreach ($row as $key => $val) {
        if (!in_array($key, array('language_id', 'attribute_id', 'product_id'))) {
          if ($language_id) {
            $res[$key] = $val;
          } else {
            if (isset($this->langIdToCode[$row['language_id']])) {
              $res[$key.'_'.$this->langIdToCode[$row['language_id']]] = $val;
            }
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