<?php
class ModelGkdImportCustomer extends Model {
  
	public function editCustomer($customer_id, $data) {
    $customer_data = array('customer_group_id', 'firstname', 'lastname', 'email', 'telephone', 'newsletter', 'status', 'safe', 'ip');
    
    if (!empty($config['extra'])) {
      $customer_data = array_merge($customer_data, $config['extra']);
    }
    
    $main_query = array();
    
    foreach ($customer_data as $item_col) {
      if (isset($data[$item_col])) {
        $main_query[] = "`" . $this->db->escape($item_col) . "` = '" . $this->db->escape($data[$item_col]) . "'";
      }
    }
    
    if (isset($data['custom_field'])) {
      $main_query[] = "`custom_field` = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode(array())) . "'";
    }
    
    if (count($main_query)) {
		  $this->db->query("UPDATE " . DB_PREFIX . "customer SET " . implode(',', $main_query) . " WHERE customer_id = '" . (int)$customer_id . "'");
    }

		if (isset($data['password'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . ((defined('GKD_UNIV_IMPORT') && isset($data['salt'])) ? $this->db->escape($salt = $data['salt']) : $this->db->escape($salt = token(9))) . "', password = '" . ((defined('GKD_UNIV_IMPORT') && isset($data['salt'])) ? $this->db->escape($data['password']) : $this->db->escape(sha1($salt . sha1($salt . sha1($data['password']))))) . "' WHERE customer_id = '" . (int)$customer_id . "'");
      //$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id = '" . (int)$customer_id . "'");
		}

		if (!empty($data['address'])) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");

			foreach ($data['address'] as $address) {
        $address_id = '';
        if (!empty($address['address_id'])) {
          $address_id = "address_id = '" . (int)$address['address_id'] . "',";
        }
        
        foreach (array('firstname', 'lastname', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country_id', 'zone_id', 'custom_field') as $field) {
          if (!isset($address[$field])) {
            $address[$field] = '';
          }
        }
        
				$this->db->query("INSERT INTO " . DB_PREFIX . "address SET ".$address_id." customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "', custom_field = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : json_encode(array())) . "'");

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
				}
			}
		}
		
		if (isset($data['affiliate'])) {
			$this->db->query("REPLACE INTO " . DB_PREFIX . "customer_affiliate SET customer_id = '" . (int)$customer_id . "', company = '" . $this->db->escape($data['company']) . "', website = '" . $this->db->escape($data['website']) . "', tracking = '" . $this->db->escape($data['tracking']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['affiliate'] . "', date_added = NOW()");
		}		
	}
  
}
