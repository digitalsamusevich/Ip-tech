<?php
class ModelGkdImportCategory extends Model {
  
  public function addCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}
    
    if (isset($data['code'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET code = '" . $this->db->escape($data['code']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
      if (version_compare(VERSION, '2', '>=')) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
      } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
      }
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
    if (version_compare(VERSION, '3', '>=')) {
      if (isset($data['category_seo_url'])) {
        foreach ($data['category_seo_url'] as $store_id => $language) {
          foreach ($language as $language_id => $keyword) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "' AND store_id = '" . (int)$store_id . "' AND language_id = '" . (int)$language_id . "'");

            if (!empty($keyword)) {
              $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
            }
          }
        }
      }
		} else {
      if (!empty($data['keyword'])) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "'");

        $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
      }
    }
		
		// Set which layout to use with this category
    if (isset($data['category_layout'])) {
      if (version_compare(VERSION, '2', '>=')) {
        foreach ($data['category_layout'] as $store_id => $layout_id) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
        }
      } else {
        foreach ($data['category_layout'] as $store_id => $layout) {
          if ($layout['layout_id']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
          }
        }
      }
    }

		$this->cache->delete('category');

		return $category_id;
	}
  
	public function editCategory($category_id, $data) {
    $category_data = array('parent_id', 'top', 'column', 'sort_order', 'status', 'image', 'code');
    
    $main_query = '';
    
    foreach ($category_data as $item_col) {
      if (isset($data[$item_col])) {
        $main_query .= "`" . $item_col . "` = '" . $this->db->escape($data[$item_col]) . "',";
      }
    }
    
		$this->db->query("UPDATE " . DB_PREFIX . "category SET " . $main_query . " date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

    if (isset($data['category_description'])) {

      foreach ($data['category_description'] as $language_id => $desc_values) {
        $description_query = '';
        
        if ($this->config->get('mlseo_enabled') && !empty($desc_values['seo_keyword'])) {
          $seo_kw = @html_entity_decode($desc_values['seo_keyword'], ENT_QUOTES, 'UTF-8');
          
          if ($seo_kw) {
            $this->load->model('tool/seo_package');
            $seo_kw = $this->model_tool_seo_package->filter_seo($seo_kw, 'category', $category_id, $language_id);
          }
          
          if (version_compare(VERSION, '3', '>=')) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "' AND store_id = 0");
            $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET query = 'category_id=" . (int)$category_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($seo_kw) . "', store_id = 0");
          } else if ($this->config->get('mlseo_ml_mode')) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', language_id = '" . (int)$language_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
          } else {
            $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($seo_kw) . "'");
          }
        }
        
        foreach ($desc_values as $desc_col => $desc_val) {
          $description_query .= $desc_col . " = '" . $this->db->escape($desc_val) . "',";
        }
        
        $description_query = rtrim($description_query, ',');
        
        $rowExists = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "'")->row;
        
        if (!empty($rowExists)) {
          $this->db->query("UPDATE " . DB_PREFIX . "category_description SET " . $description_query . " WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "'");
        } else {
          $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', " . $description_query);
        }
      }
    }

		// MySQL Hierarchical Data Closure Table Pattern
    if (isset($data['parent_id'])) {
      $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

      if ($query->rows) {
        foreach ($query->rows as $category_path) {
          // Delete the path below the current one
          $this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

          $path = array();

          // Get the nodes new parents
          $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

          foreach ($query->rows as $result) {
            $path[] = $result['path_id'];
          }

          // Get whats left of the nodes current path
          $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

          foreach ($query->rows as $result) {
            $path[] = $result['path_id'];
          }

          // Combine the paths with a new level
          $level = 0;

          foreach ($path as $path_id) {
            $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

            $level++;
          }
        }
      } else {
        // Delete the path below the current one
        $this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

        // Fix for records with no paths
        $level = 0;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

        foreach ($query->rows as $result) {
          $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

          $level++;
        }

        $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
      }
		}

		if (isset($data['category_filter'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['category_store'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['category_layout'])) {
      $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
    
    if (!$this->config->get('mlseo_enabled')) {
      // v3.x
      if (isset($data['product_seo_url'])) { 
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "'");
        
        foreach ($data['product_seo_url']as $store_id => $language) {
          foreach ($language as $language_id => $keyword) {
            if (!empty($keyword)) {
              $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
            }
          }
        }
      // v2.x
      } else if (isset($data['keyword'])) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id . "'");

        if ($data['keyword']) {
          $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }
      }
		}
	}
  
}
