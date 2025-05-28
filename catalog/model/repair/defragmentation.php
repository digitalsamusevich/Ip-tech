<?php
class ModelRepairDefragmentation extends Model {
    
    public function getItem($item, $column, $table) {

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "" . $table . "` WHERE `" . $column ."` = '" . $item . "'");
      
        if ($query->num_rows) {
            return array(
                'item'       => $query->row[$column]
            );
        } else {
            return false;
        }
    }
    
    public function getItems($column, $table) {
        $sql = "SELECT `" . $column . "` FROM `" . $table . "`";
        
        $items_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $items_data[$result[$column]] = $this->getItem($result[$column], $column, $table);
        }
        
        return $items_data;
    }
    
    public function getTables($column_name) {
        $sql = "SELECT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='" . DB_DATABASE . "' AND COLUMN_NAME='" .  $column_name . "'";
   
        $tables_data = array();
        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $tables_data[] = $result;
        }
          
        return $tables_data;
    }
    
    public function getPrimary($column_name) {
        $sql = "SELECT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='" . DB_DATABASE . "' AND COLUMN_NAME='" .  $column_name . "' AND table_name in 
        (SELECT table_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '" . DB_DATABASE . "' AND COLUMN_KEY = 'PRI' AND COLUMN_NAME='" .  $column_name . "'
        AND EXTRA = 'auto_increment')";
  
        $query = $this->db->query($sql);

        return $query->row;
    }
    
    public function getColumnName() {
        $sql = "SELECT column_name from INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '" . DB_DATABASE . "' AND COLUMN_KEY = 'PRI' AND DATA_TYPE = 'int' AND EXTRA = 'auto_increment'";

        $column_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $column_data[] = $result;
        }

        return $column_data;
    }
    
   
    
    public function defragmentation($item_id, $new_id, $column, $tables, $total) {
        $json = array();
        
        foreach ($tables as $key => $table) {
            $select = "SELECT * FROM `" . DB_PREFIX . "" . $tables[$key]['value'] . "` WHERE " . $column . " = '" . $new_id . "'";
            
            $check_id = $this->db->query($select);
            
            $json['SELECT'][$tables[$key]['value']] = $check_id;
            

            if($check_id->num_rows == 0 && $new_id != $item_id){
                $update = "UPDATE `" . DB_PREFIX . "" . $tables[$key]['value'] . "` SET `" . $column . "` = '" . $new_id . "' WHERE `" . $column . "` = '" . $item_id . "'";
                    
                $this->db->query($update);
        
                $json['UPDATE'][$tables[$key]['value']] = $update;
                
            } else {
                    $select_item = "SELECT * FROM `" . DB_PREFIX . "" . $tables[$key]['value'] . "` WHERE " . $column . " = '" . $item_id . "'";
                    
                    $select_items = $this->db->query($select_item);

                    $json['SELECT_ITEMS'][$tables[$key]['value']] = $select_items;
                    
                
                    if($select_items->row){
                        $delete = "DELETE FROM `" . DB_PREFIX . "" . $tables[$key]['value'] . "` WHERE " . $column . " = '" . $new_id . "'";
                        $this->db->query($delete);
                        $json['DELETE'][] = $delete;
                        
                        
                        foreach ($select_items->rows as $rows => $column_rows) {
                            $insert = "INSERT INTO `" . DB_PREFIX . "" . $tables[$key]['value'] ."`(";

                            foreach ($column_rows as $row => $column_row) {
                                $insert .= "`" . $row . "`, ";
                            }

                            $insert = substr($insert, 0, -2);
                            $insert .= ") VALUES (";


                            foreach ($column_rows as $row => $column_row) {
                                if($row == $column){
                                    $insert .= "'" .$new_id . "', ";
                                } else {
                                    $insert .= "'" .$column_row . "', ";
                                }
                            }

                            $insert = substr($insert, 0, -2);
                            $insert .= ");";

                            $this->db->query($insert);
                            $json['INSERT'][] = "" . $insert . "";  
                       };
                       
                }
            }
            
            if($new_id == $total){
                $delete = "DELETE FROM `" . DB_PREFIX . "" . $tables[$key]['value'] . "` WHERE " . $column . " > '" . $new_id . "'";
                $this->db->query($delete);
                $json['DELETE'][] = $delete;
            }
        }
        
 
        if($column == 'product_id'){
            $check_id = $this->db->query("SELECT `related_id` FROM `" . DB_PREFIX . "product_related` WHERE `related_id` = '" . $new_id . "'");

            if($check_id->num_rows == 0 && $new_id != $item_id){
                $this->db->query("UPDATE `product_related` SET `related_id` = '" . $new_id . "' WHERE `related_id` = '" . $item_id . "'");
            } 
            
        }
        
        if($column == 'category_id'){
            $check_id = $this->db->query("SELECT `parent_id` FROM `" . DB_PREFIX . "category` WHERE `parent_id` = '" . $new_id . "'");

            if($check_id->num_rows == 0 && $new_id != $item_id){
                $this->db->query("UPDATE `category` SET `parent_id` = '" . $new_id . "' WHERE `parent_id` = '" . $item_id . "'");
            }

            $check_id = $this->db->query("SELECT `path_id` FROM `" . DB_PREFIX . "category_path` WHERE `path_id` = '" . $new_id . "'");

            if($check_id->num_rows == 0 && $new_id != $item_id){
                $this->db->query("UPDATE `category_path` SET `path_id` = '" . $new_id . "' WHERE `path_id` = '" . $item_id . "'");
            }
        }
        

        if($column == 'product_id' || $column == 'category_id' || $column == 'information_id' || $column == 'manufacturer_id'){
            $check_id = $this->db->query("SELECT `query` FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $column . "=" . $new_id . "'");

            if($check_id->num_rows == 0 && $new_id != $item_id){
                $this->db->query("UPDATE `url_alias` SET `query` = '" . $column . "=" . $new_id . "' WHERE `query` = '" . $column . "=" . $item_id . "'");
            }
        }
        return $json;
        
    }
    
    

    
   
}