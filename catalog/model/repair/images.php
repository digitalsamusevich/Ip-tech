<?php
class ModelRepairImages extends Model {
    
    public function getImage($product_id) {
        $query = $this->db->query("SELECT product_id, image, image2, image3 FROM " . DB_PREFIX . "product WHERE product_id ='" . $product_id . "'");
        if ($query->num_rows) {
            return array(
                'product_id'       => $query->row['product_id'],
                'image'       => $query->row['image'],
                'image2'      => $query->row['image2'],
                'image3'      => $query->row['image3']
            );
        } else {
            return false;
        }
    }
    
    public function getPicture($product_id) {
        $query = $this->db->query("SELECT product_id, description FROM " . DB_PREFIX . "product_description WHERE product_id ='" . $product_id . "'");
        if ($query->num_rows) {
            return array(
                'product_id'   => $query->row['product_id'],
                'description'  => $query->row['description']
            );
        } else {
            return false;
        }
    }

    public function getImages($limit_from, $limit) {
        $sql = "SELECT `product_id` FROM `product`";
        
    
        $sql .= " ORDER BY product_id DESC";
        
        if($limit != 0){
            $sql .= " LIMIT " . $limit_from . ", " . $limit;
        }

        
        $product_data = array();
        $query = $this->db->query($sql);
        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getImage($result['product_id']);
        }
        return $product_data;
    }
    
    
    public function getPicturesInDesc($limit_from, $limit) {
        $sql = "SELECT `product_id` FROM `product_description`";


        if($limit != 0){
            $sql .= " LIMIT " . $limit_from . ", " . $limit;
        }

        
        $product_data = array();
        $query = $this->db->query($sql);
        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getPicture($result['product_id']);
        }
        return $product_data;
    }
   
}