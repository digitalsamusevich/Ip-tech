<?php
class ModelExtensionheadermenu extends Model {
	public function install() {
	$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."headermenu` (
  `headermenu_id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `level1` varchar(255) NOT NULL,
  `level2` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `column` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`headermenu_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."headermenu_description` (
  `headermenu_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	public function uninstall() {
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."headermenu`");
	$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."headermenu_description`");
	}
	
	public function addheadermenu($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "headermenu SET link = '" .$data['link'] . "', level1 = '" .$data['level1'] . "', level2 = '" .$data['level2'] . "', `column` = '" .$data['column'] . "',status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$headermenu_id = $this->db->getLastId(); 
		
		foreach ($data['headermenu_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "headermenu_description SET headermenu_id = '" . (int)$headermenu_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "'");
		}

		$this->cache->delete('headermenu');
	}
	
	public function editheadermenu($headermenu_id, $data) {
		
		$this->db->query("UPDATE " . DB_PREFIX . "headermenu SET link = '" .$data['link'] . "', level1 = '" .$data['level1'] . "' , level2 = '" .$data['level2'] . "', `column` = '" .$data['column'] . "',status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE headermenu_id = '" . (int)$headermenu_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "headermenu_description WHERE headermenu_id = '" . (int)$headermenu_id . "'");
		
		foreach ($data['headermenu_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "headermenu_description SET headermenu_id = '" . (int)$headermenu_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "'");
		}
		
		$this->cache->delete('headermenu');
	}
	
	public function deleteheadermenu($headermenu_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "headermenu WHERE headermenu_id = '" . (int)$headermenu_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "headermenu_description WHERE headermenu_id = '" . (int)$headermenu_id . "'");
		
		

		$this->cache->delete('headermenu');
	}	
	public function getheadermenu($headermenu_id) {
	
			$sql = "SELECT * FROM " . DB_PREFIX . "headermenu where headermenu_id='".$headermenu_id."' ";
			$query = $this->db->query($sql);
			return $query->row;
		
	}
		

	
	public function getheadermenus($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "headermenu i LEFT JOIN " . DB_PREFIX . "headermenu_description id ON (i.headermenu_id = id.headermenu_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
			$sort_data = array(
				'id.title',				
				'i.link'
			);		
		
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY id.title";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
		
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}		

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}	
			
			$query = $this->db->query($sql);
			
			return $query->rows;
		} else {
			$headermenu_data = $this->cache->get('headermenu.' . (int)$this->config->get('config_language_id'));
		
			if (!$headermenu_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "headermenu i LEFT JOIN " . DB_PREFIX . "headermenu_description id ON (i.headermenu_id = id.headermenu_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");
	
				$headermenu_data = $query->rows;
			
				$this->cache->set('headermenu.' . (int)$this->config->get('config_language_id'), $headermenu_data);
			}	
	
			return $headermenu_data;			
		}
	}
	
	public function getheadermenus1($data = array()) {
		
			$sql = "SELECT * FROM " . DB_PREFIX . "headermenu i LEFT JOIN " . DB_PREFIX . "headermenu_description id ON (i.headermenu_id = id.headermenu_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' and i.level1!=''";
		
			$sort_data = array(
				'id.title',				
				'i.link'
			);		
		
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY id.title";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
		
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}		

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}	
			
			$query = $this->db->query($sql);
			
			return $query->rows;
					
		
	}
	
	public function getheadermenuDescriptions($headermenu_id) {
		$headermenu_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "headermenu_description WHERE headermenu_id = '" . (int)$headermenu_id . "'");

		foreach ($query->rows as $result) {
			$headermenu_description_data[$result['language_id']] = array(
				'title'       => $result['title']
				
				);
		}
		
		return $headermenu_description_data;
	}
	
	
		
	public function getTotalheadermenus() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "headermenu");
		
		return $query->row['total'];
	}	
	
	
}
?>