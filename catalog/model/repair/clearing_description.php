<?php
class ModelRepairClearingDescription extends Model {
    
    public function getProduct($product_id) {
        
      
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description pd LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id) WHERE pd.product_id ='" . $product_id . "'");
        //$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description pd LEFT JOIN product p ON (p.product_id = pd.product_id) WHERE pd.product_id ='" . $product_id . "'");
        
        if ($query->num_rows) {
            return array(
                'product_id'       => $query->row['product_id'],
                'name'             => $query->row['name'],
                'description'      => $query->row['description'],
                'meta_title'       => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'model'            => $query->row['model']
            );
        } else {
            return false;
        }
    }
    
    public function getProducts() {
        $sql = "SELECT `product_id` FROM `" . DB_PREFIX . "product_description` WHERE product_id > 0 ORDER BY `product_id` ASC";

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
        }
        return $product_data;
    }
    
    public function getCategory($product_id) {

        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' LIMIT 1");

        $category_id = $query->rows[0]['category_id'];

        $categories = $this->db->query("SELECT path_id FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");

        $category = array();
        foreach ($categories->rows as $row) {
            array_push($category, $row['path_id']);
        }

        return $category;

    }
    
    public function getCategoryInfo($category_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function updateProducts($product_id, $language, $product) {
        $json = array();
        $update = "UPDATE `" . DB_PREFIX . "product_description` SET `name` = '" . $this->db->escape($product['name']) . "', `meta_title` = '" . $this->db->escape($product['meta_title']) . "', `meta_description` = '" . $this->db->escape($product['meta_description']) . "', `description` = '" . $this->db->escape($product['description']) . "' WHERE `product_id` = '" . $product_id . "' AND `language_id` = '" . $language . "'  LIMIT 1";
        
        $this->db->query($update);
        $json['update'] = $update;
        return $json;
    }

   
}