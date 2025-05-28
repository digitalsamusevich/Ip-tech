<?php
class ModelGkdExportDriverCustomer extends Model {
  
  public function getItems($data = array(), $count = false) {
    $select = ($count) ? 'COUNT(DISTINCT c.customer_id) AS total' : "*, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group";
    
    // hide sensible info for demo user
    if (!defined('GKD_CRON') && ($this->user->getUserName() == 'demo' || !$this->user->hasPermission('modify', 'module/universal_import')) && !$count) {
      $select .= ", '******' AS password, '*******@mail.com' AS email, '' AS salt, '' AS token, '' AS code, '' AS ip";
    }
    
    $sql = "SELECT ".$select." FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id)";
    
    // Where
    $sql .= " WHERE cgd.language_id = '" . (int)$data['filter_language'] . "'";
    
    if (!empty($data['filter_status'])) {
      $sql .= " AND c.status = '" . (int)$data['filter_status'] . "'";
    }
    
    if (!empty($data['filter_approved'])) {
      $sql .= " AND c.approved = '" . (int)$data['filter_status'] . "'";
    }
    
    if (!empty($data['filter_newsletter'])) {
      $sql .= " AND c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
    }
    
		if (!empty($data['filter_name'])) {
			$sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY c.customer_id";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql)->rows;
    
    // max number of addresses
    $address_count = 1;
    
    $max_address = $this->db->query("SELECT count(address_id) as 'count' FROM " . DB_PREFIX . "address GROUP BY customer_id ORDER BY `count` DESC LIMIT 1")->row;

    if (!empty($max_address['count'])) {
      $address_count = $max_address['count'];
    }
    
    // custom fields
		$account_custom_fields = $address_custom_fields = array();

		$filter_data = array(
			'sort'  => 'cf.sort_order',
			'order' => 'ASC'
		);

    if (version_compare(VERSION, '2', '>=')) {
      if (version_compare(VERSION, '2.1', '>=')) {  
        $this->load->model('customer/custom_field');
        $cf_query = $this->model_customer_custom_field->getCustomFields($filter_data);
      } else {
        $this->load->model('sale/custom_field');
        $cf_query = $this->model_sale_custom_field->getCustomFields($filter_data);
      }
      
      foreach ($cf_query as $custom_field) {
        if ($custom_field['location'] == 'account') {
          $account_custom_fields[] = $custom_field['custom_field_id'];
        } else if ($custom_field['location'] == 'address') {
          $address_custom_fields[] = $custom_field['custom_field_id'];
        }
      }
    }
    
    foreach($query as $key => $cust) {
      if (!empty($cust['custom_field'])) {
        $cust_fields = json_decode($cust['custom_field'], true);
      }
      
      foreach ($account_custom_fields as $cfid) {
        if (isset($cust_fields[$cfid])) {
          $query[$key]['account_custom_field_'.$cfid] = is_array($cust_fields[$cfid]) ? implode('|', $cust_fields[$cfid]) : $cust_fields[$cfid];
        } else {
          $query[$key]['account_custom_field_'.$cfid] = '';
        }
      }
      
      unset($query[$key]['custom_field']);
      
      $addresses = $this->getAddresses($cust['customer_id'], $cust['address_id'], $address_count, $address_custom_fields);
      foreach($addresses as $address) {
        // $query[$key] = array_merge($query[$key], $address);
        $query[$key] = $query[$key] + $address;
      }
      
      if (!empty($data['export_fields'])) {
        $query[$key] = array_merge(array_flip($data['export_fields']), array_intersect_key($query[$key], array_flip($data['export_fields'])));
      }
    }
    
		return $query;
	}
  
  public function getAddress($address_id, $addressCount, $default_id, $address_custom_fields) {
    $country = '';
    $iso_code_2 = '';
    $iso_code_3 = '';
    $address_format = '';
    $zone = '';
    $zone_code = '';
    
    if ($address_id) {
      $address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "'")->row;

      if (!empty($address_query)) {
        $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query['country_id'] . "'");

        if ($country_query->num_rows) {
          $country = $country_query->row['name'];
          $iso_code_2 = $country_query->row['iso_code_2'];
          $iso_code_3 = $country_query->row['iso_code_3'];
          $address_format = $country_query->row['address_format'];
        }

        $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query['zone_id'] . "'");

        if ($zone_query->num_rows) {
          $zone = $zone_query->row['name'];
          $zone_code = $zone_query->row['code'];
        }
      }
    } else {
      $address_query = array();
    }
    
      $custom_fields = '';
      
      $addressCount = $addressCount ? $addressCount++ : '';
      
			$values = array(
				// 'address_id'     => $address_query['address_id'],
				// 'customer_id'    => $address_query['customer_id'],
				'address'.$addressCount.'_firstname'      => isset($address_query['firstname']) ? $address_query['firstname'] : '',
				'address'.$addressCount.'_lastname'       => isset($address_query['lastname']) ? $address_query['lastname'] : '',
				'address'.$addressCount.'_company'        => isset($address_query['company']) ? $address_query['company'] : '',
				'address'.$addressCount.'_address_1'         => isset($address_query['address_1']) ? $address_query['address_1'] : '',
				'address'.$addressCount.'_address_2'         => isset($address_query['address_2']) ? $address_query['address_2'] : '',
				'address'.$addressCount.'_postcode'       => isset($address_query['postcode']) ? $address_query['postcode'] : '',
				'address'.$addressCount.'_city'           => isset($address_query['city']) ? $address_query['city'] : '',
				// 'address'.$addressCount.'zone_id'        => $address_query['zone_id'],
				'address'.$addressCount.'_zone'           => $zone,
				'address'.$addressCount.'_zone_code'      => $zone_code,
				// 'address'.$addressCount.'country_id'     => $address_query['country_id'],
				'address'.$addressCount.'_country'        => $country,
				'address'.$addressCount.'_iso_code_2'     => $iso_code_2,
				'address'.$addressCount.'_iso_code_3'     => $iso_code_3,
				'address'.$addressCount.'_default'     => $address_id == $default_id ? '1' : '',
				// 'address'.$addressCount.'_format' => $address_format,
				//'address'.$addressCount.'_custom_fields'   => $custom_fields
			);
      
      if (!empty($address_query['custom_field'])) {
        $cust_fields = json_decode($address_query['custom_field'], true);
      }
      
      foreach ($address_custom_fields as $cfid) {
        if (isset($cust_fields[$cfid])) {
          $values['address'.$addressCount.'_custom_field_'.$cfid] = is_array($cust_fields[$cfid]) ? implode('|', $cust_fields[$cfid]) : $cust_fields[$cfid];
        } else {
          $values['address'.$addressCount.'_custom_field_'.$cfid] = '';
        }
      }
      
      return $values;
		
	}

	public function getAddresses($customer_id, $default_id, $address_count, $address_custom_fields) {
		$address_data = array();

		$query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'")->rows;

		for ($i = 0;  $i < $address_count; $i++) {
			$address_info = $this->getAddress((isset($query[$i]['address_id']) ? $query[$i]['address_id'] : false), $i, $default_id, $address_custom_fields);
      
			if ($address_info) {
				$address_data[$i] = $address_info;
			}
		}

		return $address_data;
	}
  
  public function getTotalItems($data = array()) {
    return $this->getItems($data, true);
  }
}