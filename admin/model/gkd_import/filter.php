<?php
class ModelGkdImportFilter extends Model {
	public function addFilter($data) {
    $insert_id = '';
    if (!empty($data['filter_id'])) {
       $insert_id = "filter_id = '".(int) $data['filter_id']."', ";
    }
    
		$this->db->query("INSERT INTO " . DB_PREFIX . "filter SET ".$insert_id." filter_group_id = '" . (int)$data['filter_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$filter_id = $this->db->getLastId();

		foreach ($data['filter_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_description SET filter_id = '" . (int)$filter_id . "', filter_group_id = '" . (int)$data['filter_group_id'] . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		return $filter_id;
	}

	public function editFilter($filter_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "filter SET filter_group_id = '" . (int)$data['filter_group_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE filter_id = '" . (int)$filter_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "filter_description WHERE filter_id = '" . (int)$filter_id . "'");

		foreach ($data['filter_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_description SET filter_id = '" . (int)$filter_id . "', filter_group_id = '" . (int)$data['filter_group_id'] . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
	}

  public function addFilterGroup($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "filter_group SET sort_order = '" . (int)$data['sort_order'] . "'");

		$filter_group_id = $this->db->getLastId();

		foreach ($data['filter_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_group_description SET filter_group_id = '" . (int)$filter_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		return $filter_group_id;
	}

	public function editFilterGroup($filter_group_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "filter_group SET sort_order = '" . (int)$data['sort_order'] . "' WHERE filter_group_id = '" . (int)$filter_group_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "filter_group_description WHERE filter_group_id = '" . (int)$filter_group_id . "'");

		foreach ($data['filter_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_group_description SET filter_group_id = '" . (int)$filter_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
	}
}
