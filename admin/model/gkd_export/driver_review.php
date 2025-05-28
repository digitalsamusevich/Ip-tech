<?php
class ModelGkdExportDriverReview extends Model {
  private $langIdToCode = array();
  
  public function getItems($data = array(), $count = false) {
    $select = ($count) ? "COUNT(DISTINCT r.review_id) AS total" : "p.model, r.*";
    
    $sql = "SELECT ".$select." FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id)";
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
    }
    
    // Where
    $sql .= " WHERE 1";
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " AND p2s.store_id = '" . (int)$data['filter_store'] . "'";
    }
    
    if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
    
    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY r.review_id ASC";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
  
  public function getTotalItems($data = array()) {
    return $this->getItems($data, true);
  }
}