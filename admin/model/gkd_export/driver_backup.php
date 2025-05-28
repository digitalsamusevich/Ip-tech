<?php
class ModelGkdExportDriverBackup extends Model {
  
  public function getItems($data = array(), $count = false) {
    $items = array();
    
    $currentLimit = 0;
    
    $ignore = 'IGNORE ';
    
    if (empty($data['backup_tables'])) {
      $data['backup_tables'] = $this->getTables();
    }
    
    $exportPrefix = DB_PREFIX;
    
    if (isset($data['prefix'])) {
      $exportPrefix = $data['prefix'];
    }
    
    $move_to_table = false;
    
    if (!empty($this->session->data['obue_table'])) {
      $move_to_table = $this->session->data['obue_table'];
    }
    
    foreach ($data['backup_tables'] as $table) {
      $deleteMode = false;
      $exportTable = preg_replace('/^'.DB_PREFIX.'/', $exportPrefix, $table);
      
      if ($move_to_table) {
        if ($table != $move_to_table) {
          continue;
        } else {
          $move_to_table = false;
        }
      }
      
      if ($this->session->data['obue_table'] != $table) {
        $this->session->data['obue_processed'] = 0;
      }
      
      $this->session->data['obue_table'] = $table;
      
			if (DB_PREFIX) {
				if (strpos($table, DB_PREFIX) === false) {
					$status = false;
				} else {
					$status = true;
				}
			} else {
				$status = true;
			}

			if ($status) {
        $items[] = "\n\n";
        
        $where = ' WHERE 1';
        
        if ($table == DB_PREFIX . 'order') {
          if (!empty($data['filter_interval'])) {
            $where .= " AND date_added >= '" . $this->db->escape(date('Y-m-d H:i:s', strtotime($data['filter_interval']))) . "'";
          }
          
          if (!empty($data['store_id'])) {
            $where .= " AND store_id = '" . (int) $data['store_id'] . "'";
          }
        }
        
        // apply condition
        if (!empty($data['conditions'])) {
          $dbColsQuery = $this->db->query("SHOW COLUMNS FROM `" . $this->db->escape($table) . "`")->rows;
          
          foreach ($data['conditions'] as $func_values) {
            foreach ($dbColsQuery as $dbCol) {
              if ($dbCol['Field'] == $func_values['db_field']) {
                if ($func_values['comparator'] == 'is_equal') {
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` = '".$this->db->escape($func_values['value'])."'";
                } else if ($func_values['comparator'] == 'is_equal_list') {
                  $not_in = array();
                  
                  foreach (explode('|', $func_values['value']) as $v) {
                    $not_in[] = $this->db->escape($v);
                  }
                  
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` IN ('".implode( "', '" , array_map('addslashes', $not_in))."')";
                } else if ($func_values['comparator'] == 'is_not_equal_list') {
                  $not_in = array();
                  
                  foreach (explode('|', $func_values['value']) as $v) {
                    $not_in[] = $this->db->escape($v);
                  }
                  
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` NOT IN ('".implode( "', '" , array_map('addslashes', $not_in))."')";
                } else if ($func_values['comparator'] == 'is_not_equal') {
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` <> '".$this->db->escape($func_values['value'])."'";
                } else if ($func_values['comparator'] == 'is_greater') {
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` > '".$this->db->escape($func_values['value'])."'";
                } else if ($func_values['comparator'] == 'is_lower') {
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` < '".$this->db->escape($func_values['value'])."'";
                } else if ($func_values['comparator'] == 'contain') {
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` LIKE '%".$this->db->escape($func_values['value'])."%'";
                } else if ($func_values['comparator'] == 'not_contain') {
                  $where .= " AND `".$this->db->escape($func_values['db_field'])."` NOT LIKE '%".$this->db->escape($func_values['value'])."%'";
                }
                
                if (!empty($data['truncate']) && empty($this->session->data['obue_processed'])) {
                  $deleteMode = true;
                }
              }
            }
          }
        }
        
        if (!empty($deleteMode)) {
          $items[] = 'DELETE FROM `' . $exportTable . '`' . $where . ';' . "\n\n";
        } else if (!empty($data['truncate']) && empty($this->session->data['obue_processed'])) {
          $items[] = 'TRUNCATE TABLE `' . $exportTable . '`;' . "\n\n";
        }
        
        $query = $this->db->query("SELECT COUNT(*) AS count FROM `" . $this->db->escape($table) . "`" . $where)->row;
        
        $currentTotal = $query['count'];
        
        $limits = '';
        
        if (isset($this->session->data['obue_processed']) || isset($data['limit'])) {
          $limits = " LIMIT " . (int)$this->session->data['obue_processed'] . "," . (int)$data['limit'];
        }
        
				$query = $this->db->query("SELECT * FROM `" . $this->db->escape($table) . "` " . $where . $limits);

				foreach ($query->rows as $result) {
          if ($currentLimit >= (int)$data['limit']) {
            return $items;
          }
          
          $this->session->data['obue_processed']++;
          
          // if (isset($result['store_id']) && $result['store_id'] == 0) {
            // $currentLimit++;
            // continue;
          // }
          
					$fields = '';

					foreach (array_keys($result) as $value) {
						$fields .= '`' . $value . '`, ';
					}
          
					$values = '';

					foreach (array_values($result) as $value) {
						$value = str_replace(array("\x00", "\x0a", "\x0d", "\x1a"), array('\0', '\n', '\r', '\Z'), $value);
						$value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
						$value = str_replace('\\', '\\\\',	$value);
						$value = str_replace('\'', '\\\'',	$value);
						$value = str_replace('\\\n', '\n',	$value);
						$value = str_replace('\\\r', '\r',	$value);
						$value = str_replace('\\\t', '\t',	$value);

						$values .= '\'' . $value . '\', ';
					}

					$items[] = 'INSERT ' . $ignore . 'INTO `' . $exportTable . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
          $currentLimit++;
				}
        
        if ($currentLimit >= (int)$data['limit']) {
          return $items;
        }
			}
      
      // table change, reset processed count
      // if ($this->session->data['obue_processed'] >= $currentTotal) {
        // $this->session->data['obue_processed'] = 0;
      // }
		}
    
		return $items;
	}
  
  public function getTotalItems($data = array()) {
    //return $this->getItems($data, true);
    
    if (empty($data['backup_tables'])) {
      $data['backup_tables'] = $this->getTables();
    }
    
    $total = 0;
		foreach ($data['backup_tables'] as $table) {
			if (DB_PREFIX) {
				if (strpos($table, DB_PREFIX) === false) {
					$status = false;
				} else {
					$status = true;
				}
			} else {
				$status = true;
			}

			if ($status) {
				$query = $this->db->query("SELECT COUNT(*) AS count FROM `" . $this->db->escape($table) . "`")->row;
        
        if (!empty($query['count'])) {
          $total += $query['count'];
        }
			}
		}

		return $total;
  }
  
  public function getTables() {
		$table_data = array();

		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (utf8_substr($result['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) == DB_PREFIX) {
				if (isset($result['Tables_in_' . DB_DATABASE])) {
					$table_data[] = $result['Tables_in_' . DB_DATABASE];
				}
			}
		}

		return $table_data;
	}
}