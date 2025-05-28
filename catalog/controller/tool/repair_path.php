<?php
class ControllerToolRepairPath extends Controller {
	public function index() {
    
    }
    
	public function repairCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` WHERE `parent_id` = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category['category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$parent_id . "' ORDER BY `level` ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', `level` = '" . (int)$level . "'");

			$this->repairCategories($category['category_id']);
		}
	}
    
    public function repairImages() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_image`");

		foreach ($query->rows as $product_image) {
            
            if($product_image['image'] == null){
			 // Delete the path below the current one
			 $this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE `product_image_id` = '" . (int)$product_image['product_image_id'] . "'");
            }
		}
	}
}