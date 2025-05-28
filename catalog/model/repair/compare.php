<?php
class ModelRepairCompare extends Model {
    
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
    
    public function getItems($column, $table, $table_compare) {

        if($table != 'url_alias' && $table_compare != 'url_alias'){
            $sword = $this->db->query("SELECT COUNT(`" . $column . "`) FROM `" . $table . "`");
            $sword = $sword->row["COUNT(`" . $column . "`)"];
            
            $shield = $this->db->query("SELECT COUNT(`" . $column . "`) FROM `" . $table_compare . "`");
            $shield = $shield->row["COUNT(`" . $column . "`)"];

          
            if($shield != 0 && $sword != 0){
                if($table == 'product'){
                    $sql = "SELECT `" . $column . "` , `model` FROM " . $table . "  WHERE `" . $column . "` NOT IN (SELECT `" . $column . "` FROM " . $table_compare . ")";
                } else if($table == 'category'){
                   $sql = "SELECT c." . $column . ", cd.name AS model FROM " . $table . " c LEFT JOIN category_description cd ON (c." . $column . " = cd." . $column . ") WHERE c." . $column . " NOT IN (SELECT pc." . $column . " FROM " . $table_compare . " pc)";
                } else {
                    $sql = "SELECT `" . $column . "` FROM " . $table . "  WHERE `" . $column . "` NOT IN (SELECT `" . $column . "` FROM " . $table_compare . ")";
                }
                $query = $this->db->query($sql);

                return $query->rows;
            }

        } else if($table == 'url_alias'){

            $sql = "SELECT a.url_alias_id, a.keyword AS model FROM `" . $table . "` a LEFT JOIN `" . $table_compare . "` t ON (t." . $table_compare . "_id = REPLACE(a.query, '" . $table_compare . "_id=', '')) WHERE t." . $table_compare . "_id is null AND a.query LIKE '%" . $table_compare . "%'";
            
            $query = $this->db->query($sql);
            return $query->rows;

        } else if($table_compare == 'url_alias'){
            
            if($table == 'product' || $table == 'category'){
                $sql = "SELECT t." . $column . ", td.name AS model FROM " . $table . " t LEFT JOIN " . $table . "_description td ON (td." . $column . " = t." . $column. ") LEFT JOIN url_alias a ON (a.query = CONCAT('" . $column . "', '=' , t." . $column . ")) WHERE a.query is null";
            } else {
                $sql = "SELECT t." . $column . " FROM " . $table . " t LEFT JOIN url_alias a ON (a.query = CONCAT('" . $column . "', '=' , t." . $column . ")) WHERE a.query is null";
            }

            $query = $this->db->query($sql);
            return $query->rows;

        } else {
            return 'empty';
        }  
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
    
    

    
   
}