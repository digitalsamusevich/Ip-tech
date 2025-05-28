<?php
class ModelGkdImportAttribute extends Model {
  
  public function addAttribute($data) {
    $insert_id = '';
    if (!empty($data['attribute_id'])) {
       $insert_id = "attribute_id = '".(int) $data['attribute_id']."', ";
    }
    
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET  ".$insert_id." attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$attribute_id = $this->db->getLastId();

		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		return $attribute_id;
	}
  
  public function editAttribute($attribute_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_id = '" . (int)$attribute_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "'");

		foreach ($data['attribute_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
	}
  
  public function updateAttribute($attribute_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_id = '" . (int)$attribute_id . "'");
    
		foreach ($data['attribute_description'] as $language_id => $value) {
      if ($value['name'] === '') {
        continue;
      }
      
      $attr_id = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "' AND language_id = '" . (int)$language_id . "'")->row;
      
      if (empty($attr_id['attribute_id'])) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
      } else {
        $this->db->query("UPDATE " . DB_PREFIX . "attribute_description SET name = '" . $this->db->escape($value['name']) . "' WHERE attribute_id = '" . (int)$attribute_id . "' AND language_id = '" . (int)$language_id . "'");
      }
		}
	}
  
  public function addAttributeGroup($data) {
    $insert_id = '';
    if (!empty($data['attribute_group_id'])) {
      $insert_id = "attribute_group_id = '".(int) $data['attribute_group_id']."', ";
    }
    
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET ".$insert_id." sort_order = '" . (int)$data['sort_order'] . "'");

		$attribute_group_id = $this->db->getLastId();

		foreach ($data['attribute_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		return $attribute_group_id;
	}
  
  public function editAttributeGroup($attribute_group_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "attribute_group SET sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

		foreach ($data['attribute_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
	}
  
  public function updateAttributeGroup($attribute_group_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "attribute_group SET sort_order = '" . (int)$data['sort_order'] . "' WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");

		foreach ($data['attribute_group_description'] as $language_id => $value) {
      if ($value['name'] === '') {
        continue;
      }
      
      $attr_group_id = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . (int)$attribute_group_id . "' AND  language_id = '" . (int)$language_id . "'")->row;
      
      if (empty($attr_group_id['attribute_group_id'])) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
      } else {
        $this->db->query("UPDATE " . DB_PREFIX . "attribute_group_description SET name = '" . $this->db->escape($value['name']) . "' WHERE attribute_group_id = '" . (int)$attribute_group_id . "' AND language_id = '" . (int)$language_id . "'");
      }
		}
	}
}
