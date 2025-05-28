<?php
class ModelExtensionModuleFXSitemap extends Model{

private $is_multi = false;

private $language_id = false;

private $exclude = false;
    
	public function isMulti($set = '') {
		
		if ($set == true) $this->config->set('fx_sitemap_multistore', true);
		
		//var_dump($set);
		
		if ($set === false) $this->config->set('fx_sitemap_multistore', false);
	
		return $this->config->get('fx_sitemap_multistore');
	}
	
	public function lang($id = false) {
		
		if ($id === false) return false;		
		
		$this->language_id = is_int($id) ? $id : (int)$this->config->get('config_language_id');		
	}
	
	public function getLanguageId($language) {
		
		$query = $this->db->query("SELECT language_id FROM " . DB_PREFIX . "language WHERE code = '" . $language . "'");
		
		$language_id = !empty($query->row['language_id']) ? $query->row['language_id'] : $this->language_id;
		
		return $language_id;
		
	}
	
/*-------------------------------------------------------*/	
    
	public function getProducts($data = array()) {

		$query = "SELECT product_id, date_added, date_modified FROM " . DB_PREFIX . "product WHERE status = '1' LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
		
		$query_m = "SELECT p.product_id, p.date_added, p.date_modified FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
		
		if ($this->isMulti()) $query = $query_m;
		
		if ($this->exclude()) $query = "SELECT p.product_id, p.date_added, p.date_modified FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) WHERE p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pe.pid IS NULL LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
		
		$result = $this->db->query($query);
	
		return $result->rows;
	}	
	
	public function getProductsTotal($data = array()) {

		$query = "SELECT COUNT(product_id) AS total FROM " . DB_PREFIX . "product WHERE status = '1'";	
		
		$query_m = "SELECT COUNT(p.product_id) AS total FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->isMulti()) $query = $query_m;
		
		if ($this->exclude()) $query = "SELECT COUNT(p.product_id) AS total FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) WHERE p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pe.pid IS NULL";
		
		$result = $this->db->query($query);
	
		return $result->row['total'];
	}
	
	public function getProductsExpress($data = array()) {
		
		if((float)VERSION >= 3) return $this->Express3($data);

		$query = "SELECT u.keyword, p.date_added, p.date_modified, p.product_id FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('product_id=', p.product_id)) ";
		
		if ($this->isMulti()) $query .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) ";
		
		if ($this->exclude()) $query .= " LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) ";		
		
		$query .= " WHERE p.status = '1'";		
		
		if ($this->isMulti()) $query .= " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->exclude()) $query .= " AND pe.pid IS NULL";

		$query .= 	" LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
		
		
		$result = $this->db->query($query);
	
		return $result->rows;
	}
	
	public function Express3($data = array()) {
		
		$query = "SELECT u.keyword, p.date_added, p.date_modified, p.product_id 
			FROM " . DB_PREFIX . "product p 	
			INNER JOIN " . DB_PREFIX . "seo_url u ON (u.query = CONCAT('product_id=', p.product_id))";			
		
		if ($this->exclude()) $query .= " LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) ";
		
		$query .= "	WHERE p.status = '1'";
		
		if ($this->isMulti()) $query .= " AND u.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->language_id !== false) $query .= " AND u.language_id = '" . $this->language_id . "'";
		
		if ($this->exclude()) $query .= " AND pe.pid IS NULL";
		
		$query .= " LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
		
		$result = $this->db->query($query);
	
		return $result->rows;
	}

	public function getProductsUltra($data = array()) {

		if((float)VERSION >= 3) return $this->Ultra3($data);
		
		$query = "SELECT DISTINCT p.product_id, u.keyword, p.date_added, p.date_modified, 
			(SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id=p.product_id ORDER BY main_category DESC LIMIT 1) as category_id 	
			FROM " . DB_PREFIX . "product p
			INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('product_id=', p.product_id))";
		
		if ($this->isMulti()) $query .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) ";
		
		if ($this->exclude()) $query .= " LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) ";		
		
		$query .= " WHERE p.status = '1'";		
		
		if ($this->isMulti()) $query .= " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->exclude()) $query .= " AND pe.pid IS NULL";

		$query .= 	" LIMIT ".(int)$data['start'].", ".(int)$data['limit'];

		if ($this->config->get('config_seo_url_type') != 'seo_pro' && !$this->config->get('config_seo_pro')) $query = str_replace(' ORDER BY main_category DESC', '', $query);
		
		$result = $this->db->query($query);
		
		return $result->rows;	
	}

	public function Ultra3($data = array()) {			
	
		$query = "SELECT DISTINCT u.keyword, p.date_added, p.date_modified, p.product_id,
			(SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id=p.product_id ORDER BY main_category DESC LIMIT 1) as category_id 
			FROM " . DB_PREFIX . "product p 		
			INNER JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)		
			INNER JOIN " . DB_PREFIX . "seo_url u ON (u.query = CONCAT('product_id=', p.product_id))";
			
		if ($this->exclude()) $query .= " LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) ";
		
		$query .= "	WHERE p.status = '1'";
		
		if ($this->isMulti()) $query .= " AND u.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->language_id !== false) $query .= " AND u.language_id = '" . $this->language_id . "'";
		
		if ($this->exclude()) $query .= " AND pe.pid IS NULL";
		
		$query .= " LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
		
		if ($this->config->get('config_seo_url_type') != 'seo_pro' && !$this->config->get('config_seo_pro'))  $query = str_replace(' ORDER BY main_category DESC', '', $query);
		
		$result = $this->db->query($query);
		
		return $result->rows;	
	}

/*-------------------------------------------------------*/	
	public function getCategories() {
		$query = "SELECT category_id, date_added, date_modified FROM " . DB_PREFIX . "category WHERE status = '1'";
		
		$query_m = "SELECT c.category_id, c.date_added, c.date_modified FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);

		return $query->rows;
	}
	
	public function getCategoriesLite() {		
		$query = "SELECT category_id, date_modified FROM " . DB_PREFIX . "category WHERE status = '1'";				
		$query_m = "SELECT c.category_id, date_modified FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1'";				
		
		if ($this->isMulti()) $query = $query_m;				
		
		$query = $this->db->query($query);		
		
		return $query->rows;	
		
	}
	
	public function getCategoriesExpress($data = array()) {
		
		if((float)VERSION >= 3) return $this->getCategoriesExpress3($data);

		$query = "SELECT u.keyword, c.date_added, c.date_modified, c.category_id FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('category_id=', c.category_id)) WHERE c.status = '1'";
		
		$query_m = "SELECT u.keyword, c.date_added, c.date_modified, c.category_id FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('category_id=', c.category_id)) INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);
	
		return $query->rows;
	}
	
	public function getCategoriesExpress3($data = array()) {
		
		$query = "SELECT u.keyword, c.date_added, c.date_modified, c.category_id 
			FROM " . DB_PREFIX . "category c 	
			INNER JOIN " . DB_PREFIX . "seo_url u ON (u.query = CONCAT('category_id=', c.category_id))			
			WHERE c.status = '1'";
		
		if ($this->isMulti()) $query .= " AND u.store_id = '" . (int)$this->config->get('config_store_id')  . "'";
		
		if ($this->language_id !== false) $query .= " AND u.language_id = '" . $this->language_id . "'";
		
		$query = $this->db->query($query);
	
		return $query->rows;
	}
	
	public function getCategoriesStore() {
		
		$query = "SELECT c.category_id, c2s.store_id, c.date_added, c.date_modified FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.status = '1'";

		$query = $this->db->query($query);
		
		return $query->rows;
	}
	
	public function getCategoriesTotal() {
		$query = "SELECT COUNT(category_id) AS total FROM " . DB_PREFIX . "category WHERE status = '1'";
		
		$query_m = "SELECT COUNT(c.category_id) AS total FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);

		return $query->row['total'];
	}
	
	public function getCatDB() {

		$query = "SELECT category_id, url, date_modified, is_seo FROM " . DB_PREFIX . "fx_sitemap_categories";
		
		$query_m = "SELECT category_id, url, date_modified, is_seo FROM " . DB_PREFIX . "fx_sitemap_categories WHERE store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);

		return $query->rows;
	}
	
	public function getCatDBTotal() {
		$query = "SELECT COUNT(category_id) FROM " . DB_PREFIX . "fx_sitemap_categories";
		
		$query_m = "SELECT COUNT(category_id) FROM " . DB_PREFIX . "fx_sitemap_categories WHERE store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);

		return $query->rows;
	}
	
	public function CreateCatDB() {	
		$query = "CREATE TABLE  `" . DB_PREFIX . "fx_sitemap_categories` (
		  `category_id` mediumint(8) NOT NULL,
		  `url` varchar(255) NOT NULL,
		  `is_seo` tinyint(1) NOT NULL,
		  `date_modified` date NOT NULL,
		  `store_id` smallint(3) NOT NULL,";
		
		if((float)VERSION >= 3){		
			$query .= " `language_id` smallint(2) NOT NULL,
			  KEY `store_id` (`store_id`),
			  KEY `language_id` (`language_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
		} else {
			$query .= "
			  KEY `store_id` (`store_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
		}
		
		$query = $this->db->query($query);	
		
		return true;
	}
	
	public function PasteCatDB($data){	
		
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "fx_sitemap_categories'");
		if($test->num_rows < 1) $this->CreateCatDB();
		
		
		$this->db->query("TRUNCATE TABLE " . DB_PREFIX . "fx_sitemap_categories");
		
		$i = 0;
		
		if((float)VERSION >= 3) return $this->PasteCatDB3($data);
		

		foreach ($data as $category){			
			$this->db->query("INSERT INTO " . DB_PREFIX . "fx_sitemap_categories 
			(`category_id`, `url`, `is_seo`, `date_modified`, `store_id`) 
			VALUES (" . (int)$category['category_id'] . ", '" . $this->db->escape($category['url']) . "', '" . $category['is_seo'] . "', '" . $category['date'] . "', '" . $category['store_id'] . "' )");
			$i++;
		}
		
		return $i;
	}
	
	public function PasteCatDB3($data){
		
		$i = 0; //count($data);

		foreach ($data as $category){			
			$this->db->query("INSERT INTO " . DB_PREFIX . "fx_sitemap_categories 
			(`category_id`, `url`, `is_seo`, `date_modified`, `store_id`, `language_id`) 
			VALUES (" . (int)$category['category_id'] . ", '" . $this->db->escape($category['url']) . "', '" . $category['is_seo'] . "', '" . $category['date'] . "', '" . $category['store_id'] . "', '" . $category['language_id'] . "' )");
			$i++;
		}
		
		return $i;
	}
	
	public function setCategoryDate($category_id){
	
		$p = $this->db->query("SELECT p.date_modified as date FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = '" . (int)$category_id . "' ORDER by p.date_modified DESC LIMIT 1");	
		
		if($p->num_rows < 1) return Array();
		
		$c = $this->db->query("SELECT date_modified as date FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");
		
		
		if($c->num_rows < 1 || $p->row['date'] > $c->row['date']) $this->db->query("UPDATE " . DB_PREFIX . "category SET date_modified = '" . $p->row['date'] . "' WHERE category_id = '" . (int)$category_id . "'");
		
		return true;
	}

/*-------------------------------------------------------*/	
	
	public function getManufacturers() {
		
		$query = "SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer";
		
		$query_m = "SELECT m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);			
		
		return $query->rows;
	}

	public function getManufacturersTotal() {
		
		$query = "SELECT COUNT(manufacturer_id) AS total FROM " . DB_PREFIX . "manufacturer";
		
		$query_m = "SELECT COUNT(m.manufacturer_id) AS total FROM " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);	
		
		return $query->row['total'];
	}
		
	public function getManufacturersExpress($data = array()) {
		
		if((float)VERSION >= 3) return $this->getManufacturersExpress3($data);

		$query = "SELECT u.keyword, m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('manufacturer_id=', m.manufacturer_id))";
		
		$query_m = "SELECT u.keyword, m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('manufacturer_id=', m.manufacturer_id)) INNER JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);
	
		return $query->rows;
	}
	
	public function getManufacturersExpress3($data = array()) {
		
		$query = "SELECT u.keyword, m.manufacturer_id
			FROM " . DB_PREFIX . "manufacturer m
			INNER JOIN " . DB_PREFIX . "seo_url u ON (u.query = CONCAT('manufacturer_id=', m.manufacturer_id))";
		
		if ($this->isMulti()) $query .= " AND u.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->language_id !== false) $query .= " AND u.language_id = '" . $this->language_id . "'";
		
		$query = $this->db->query($query);
	
		return $query->rows;
	}

/*-------------------------------------------------------*/		
	public function getAllNews() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "news'");
		if($test->num_rows < 1) return Array();
		
		$test = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "news LIKE 'status'");
		$status = $test->num_rows < 1 ? "" : " WHERE status = '1'";
	
		$query = $this->db->query("SELECT news_id FROM " . DB_PREFIX . "news " . $status);	
		return $query->rows;
	}
	
	public function getAllNewsTotal() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "news'");
		if($test->num_rows < 1) return 0;
		
		$test = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "news LIKE 'status'");
		$status = $test->num_rows < 1 ? "" : " WHERE status = '1'";
	
		$query = $this->db->query("SELECT COUNT(news_id) AS total FROM " . DB_PREFIX . "news " . $status);	
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
	
	public function getAllBlog() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "blog'");
		if($test->num_rows < 1) return Array();
		
		$test = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "blog LIKE 'status'");
		$status = $test->num_rows < 1 ? "" : " WHERE status = '1'";
		
		$query = $this->db->query("SELECT blog_id FROM " . DB_PREFIX . "blog " . $status);
		return $query->rows;
	}
	
	public function getAllBlogTotal() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "blog'");
		if($test->num_rows < 1) return 0;
		
		$test = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "blog LIKE 'status'");
		$status = $test->num_rows < 1 ? "" : " WHERE status = '1'";
		
		$query = $this->db->query("SELECT COUNT(blog_id) AS total FROM " . DB_PREFIX . "blog " . $status);
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
	
	public function getMFP() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "mfilter_url_alias'");
		if($test->num_rows < 1) return Array();
		
		$query = $this->db->query("SELECT path, alias FROM " . DB_PREFIX . "mfilter_url_alias");
		return $query->rows;
	}
	
	public function getMFPTotal() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "mfilter_url_alias'");
		if($test->num_rows < 1) return 0;
		
		$query = $this->db->query("SELECT COUNT(path) AS total FROM " . DB_PREFIX . "mfilter_url_alias");
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
	
	public function getOCFilter() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ocfilter_page'");
		if($test->num_rows < 1) return Array();
		
		$query = $this->db->query("SELECT category_id, keyword, params FROM " . DB_PREFIX . "ocfilter_page WHERE status = '1'");
		return $query->rows;
	}
	
	public function getOCFilterTotal() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ocfilter_page'");
		if($test->num_rows < 1) return 0;
		
		$query = $this->db->query("SELECT COUNT(keyword) AS total FROM " . DB_PREFIX . "ocfilter_page WHERE status = '1'");
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
	
	public function getVier() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "filter_vier_hand_links'");
		if($test->num_rows < 1) return Array();
		
		$query = $this->db->query("SELECT link FROM " . DB_PREFIX . "filter_vier_hand_links");
		return $query->rows;
	}
	
	public function getVierTotal() {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "filter_vier_hand_links'");
		if($test->num_rows < 1) return 0;
		
		$query = $this->db->query("SELECT COUNT(link) AS total FROM " . DB_PREFIX . "filter_vier_hand_links");
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
	
	public function getInformations() {
		
		$query = "SELECT information_id FROM " . DB_PREFIX . "information WHERE status = '1'";
		
		$query_m = "SELECT i.information_id FROM " . DB_PREFIX . "information i INNER JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);	
		
		return $query->rows;
	}
	
	public function getInformationsTotal() {
		
		$query = "SELECT COUNT(information_id) AS total FROM " . DB_PREFIX . "information WHERE status = '1'";
		
		$query_m = "SELECT COUNT(i.information_id) AS total FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);	
		
		return $query->row['total'];
	}
	
	public function getInformationsExpress($data = array()) {
		
		if((float)VERSION >= 3) return $this->getInformationsExpress3($data);

		$query = "SELECT u.keyword, i.information_id FROM " . DB_PREFIX . "information i INNER JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('information_id=', i.information_id)) WHERE i.status = '1'";
		
		$query_m = "SELECT u.keyword, i.information_id FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "url_alias u ON (u.query = CONCAT('information_id=', i.information_id)) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'";
		
		if ($this->isMulti()) $query = $query_m;
		
		$query = $this->db->query($query);
	
		return $query->rows;
	}
	
	public function getInformationsExpress3($data = array()) {
		
		$query = "SELECT u.keyword, i.information_id 
			FROM " . DB_PREFIX . "information i
			INNER JOIN " . DB_PREFIX . "seo_url u ON (u.query = CONCAT('information_id=', i.information_id))			
			WHERE i.status = '1'";
		
		if ($this->isMulti()) $query .= " AND u.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->language_id !== false) $query .= " AND u.language_id = '" . $this->language_id . "'";
		
		$query = $this->db->query($query);
	
		return $query->rows;
	}
	
	public function getInformation($data = array()) {	
		
		$table = (float)VERSION < 3 ? 'url_alias' : 'seo_url';
		
		$query = "SELECT i.information_id 
			FROM " . DB_PREFIX . "information i INNER JOIN " . DB_PREFIX . $table . " u ON (u.query = CONCAT('information_id=', i.information_id))			
			WHERE i.status = '1' AND u.keyword IS NOT NULL LIMIT 1";
		
		
		$query = $this->db->query($query);
	
		return $query->row['information_id'];
	}

/*-------------------------------------------------------*/	
    
	public function getCMSBlog($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "record'");
		if($test->num_rows < 1) return Array();
		
		
		$test = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "record LIKE 'index_page'");
		$status = $test->num_rows < 1 ? "" : "  AND index_page NOT LIKE '%noindex%'";

		$query = $this->db->query("SELECT record_id, date_modified FROM " . DB_PREFIX . "record WHERE status = '1'" . $status);
	
		return $query->rows;
	}	
	
	public function getCMSBlogTotal($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "record'");
		if($test->num_rows < 1) return 0;
		
		$test = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "record LIKE 'index_page'");
		$status = $test->num_rows < 1 ? "" : "  AND index_page NOT LIKE '%noindex%'";

		$query = $this->db->query("SELECT COUNT(record_id) AS total FROM " . DB_PREFIX . "record WHERE status = '1'" . $status);
	
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
    
	public function getArticles($pre = '') {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $pre . "article'");
		if($test->num_rows < 1) return Array();
		
		$query = $this->db->query("SELECT " . $pre . "article_id AS article_id FROM " . DB_PREFIX . $pre . "article WHERE status = '1'");
	
		return $query->rows;
	}	
	
	public function getArticlesTotal($pre = '') {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $pre . "article'");
		if($test->num_rows < 1) return 0;

		$query = $this->db->query("SELECT COUNT(" . $pre . "article_id) AS total FROM " . DB_PREFIX . $pre . "article WHERE status = '1'");
	
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
    
	public function getFilterPro($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "filterpro_seo'");
		if($test->num_rows < 1) {return Array();}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "filterpro_seo");
	
		return $query->rows;
	}	
	
	public function getFilterProTotal($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "filterpro_seo'");
		if($test->num_rows < 1) {return 0;}

		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "filterpro_seo");
	
		return $query->row['total'];
	}
/*-------------------------------------------------------*/	
    
	public function getImgOne($data = array()) {
		$query = $this->db->query(
		"SELECT p.product_id, p.date_added, p.date_modified, pd.name, p.image
		FROM " . DB_PREFIX . "product p
		LEFT JOIN " . DB_PREFIX . "product_description pd ON ( pd.product_id = p.product_id )
		WHERE p.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
		LIMIT ".(int)$data['start'].", ".(int)$data['limit']);
		
		return $query->rows;
	}
	
	public function getImg($data = array()) {
		$query = "SELECT p.product_id, p.date_added, p.date_modified, pd.name, CONCAT_WS( ',', p.image, GROUP_CONCAT( DISTINCT pi.image ) ) AS image
		FROM " . DB_PREFIX . "product p 
		LEFT JOIN " . DB_PREFIX . "product_image pi ON ( pi.product_id = p.product_id ) 
		LEFT JOIN " . DB_PREFIX . "product_description pd ON ( pd.product_id = p.product_id ) ";
		
		if ($this->isMulti()) $query .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) ";
		
		if ($this->exclude()) $query .= " LEFT JOIN " . $this->exclude . " pe ON (p.product_id = pe.pid) ";		
		
		$query .= " WHERE p.status = '1'";
		
		if ($this->isMulti()) $query .= " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if ($this->exclude()) $query .= " AND pe.pid IS NULL";
		
		$query .= " GROUP BY p.product_id LIMIT ".(int)$data['start'].", ".(int)$data['limit'];
	
		$result = $this->db->query($query);
		
		return $result->rows;
	}
	
	public function getImgTotal($data = array()) {
		$query = $this->db->query("SELECT COUNT(product_id) AS total FROM " . DB_PREFIX . "product_image GROUP BY product_id");
	
		return $query->row['total'];
	}
	

/*-------------------------------------------------------*/		
	
	
	public function db_exclude($table) {
		$this->exclude = DB_PREFIX . str_replace('db:', '', $table);		
		$this->config->set('fx_sitemap_exclude', $this->exclude);
	}
	
	public function exclude() {

		$this->exclude = $this->config->get('fx_sitemap_exclude');
		return $this->exclude;
	}
	

/*-------------------------------------------------------*/		
	
	public function getManufacturersOnCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT m.manufacturer_id FROM " . DB_PREFIX . "product_to_category AS pc LEFT JOIN " . DB_PREFIX . "product AS p ON p.product_id = pc.product_id LEFT JOIN " . DB_PREFIX . "manufacturer AS m ON m.manufacturer_id = p.manufacturer_id WHERE pc.category_id = " . (int)$category_id . " AND p.status = 1");
		return $query->rows;
	}
	
	public function PasteManCatDB($data){	
		
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "fx_manufacturers_on_categories'");
		if($test->num_rows < 1) $this->CreateManCatDB();
		
		
		$this->db->query("TRUNCATE TABLE " . DB_PREFIX . "fx_manufacturers_on_categories");
		
		$i = 0;		

		foreach ($data as $category){			
			$this->db->query("INSERT INTO " . DB_PREFIX . "fx_manufacturers_on_categories 
			(`category_id`, `manufacturers`, `num`)
			VALUES (" . (int)$category['category_id'] . ", '" . $category['manufacturers'] . "', " . $category['num'] . " )");
			$i++;
		}
		
		return $i;
	}
	
	public function getManCatDB($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "fx_manufacturers_on_categories'");
		if($test->num_rows < 1) return Array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "fx_manufacturers_on_categories");
	
		return $query->rows;
	}	
	
	public function getManCatDBTotal($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "fx_manufacturers_on_categories'");
		if($test->num_rows < 1) return 0;

		$query = $this->db->query("SELECT SUM(num) AS total FROM " . DB_PREFIX . "fx_manufacturers_on_categories");
	
		return $query->row['total'];
	}	
	
	
	public function CreateManCatDB() {	
		$query = "CREATE TABLE  `" . DB_PREFIX . "fx_manufacturers_on_categories` (
		  `category_id` int(11) NOT NULL,
		  `manufacturers` TEXT NOT NULL,
		  `num` mediumint(5) NOT NULL,
		   UNIQUE (`category_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
		
		$query = $this->db->query($query);	
		
		return true;
	}
	

/*-------------------------------------------------------*/	


	
	public function getPages($data = array()) {
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "fx_sitemap_lastmod'");
		if($test->num_rows < 1) return Array();
		
		$out = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "fx_sitemap_lastmod");
		
		foreach ($query->rows AS $row) {
			
			$out[$row['name']] = $row;
			
		}
	
		return $out;
	}
	
	public function CreatePagesDB() {	
		$query = "CREATE TABLE  `" . DB_PREFIX . "fx_sitemap_lastmod` (
		  `name` varchar(10) NOT NULL,
		  `md5` varchar(32) NOT NULL,
		  `date` date NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";		
		
		$query = $this->db->query($query);
		
		return true;
	}
	
	
	public function setPages($data){	
		
		$test = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "fx_sitemap_lastmod'");
		if($test->num_rows < 1) $this->CreatePagesDB();
		
		
		$this->db->query("TRUNCATE TABLE " . DB_PREFIX . "fx_sitemap_lastmod");
		

		foreach ($data as $page){			
			$this->db->query("INSERT INTO " . DB_PREFIX . "fx_sitemap_lastmod 
			(`name`, `md5`, `date`) 
			VALUES ('" . $page['name'] . "', '" . $page['md5'] . "', '" . $page['date'] . "' )");

		}
		
		return true;
	}
	
	
	public function setUrl($keyword, $lang = false, $store = false){
	
		$route = ((float)VERSION < 2.3 ? '' : 'extension/') . 'feed/' . 'fx_sitemap';

		if(!$lang){
		
			$test = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = '" . $route . "'");
		
			if (!$test->rows) $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (`query`, `keyword`) VALUES ('" . $route . "', '" . $keyword . "')");

		}else{
		
			$test = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE store_id = '" . $store . "' AND language_id = '" . $lang . "' AND query = '" . $route . "'");
		
			if (!$test->rows) $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (`store_id`, `language_id`, `query`, `keyword`) VALUES ('" . $store . "', '" . $lang . "', '" . $route . "', '" . $keyword . "')");
		
		}
		
		return true;
	}
	
	public function getUrl($lang = false, $store = false){
	
		$route = ((float)VERSION < 2.3 ? '' : 'extension/') . 'feed/' . 'fx_sitemap';
	
		if((float)VERSION < 3) {
		
			$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = '" . $route . "' LIMIT 1");
			
		}else{
		
			$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE store_id = '" . $store . "' AND language_id = '" . $lang . "' AND query = '" . $route . "' LIMIT 1");
			
		}

		if($query->num_rows < 1) return false;

		return $query->row['keyword'];
	}
	
}
?>