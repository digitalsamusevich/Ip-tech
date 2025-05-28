<?php

class ControllerCheckoutSimpleCheckout extends Controller {
	public function index() {
		
		if(!$this->cart->getProducts()){
			$json['success'] = false;
				
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return false;
		}
		
		$address = $this->request->post['address'];
		$city = $this->request->post['city'];
		$comment = $this->request->post['comment'];
		$email = $this->request->post['email'];
		$firstname = $this->request->post['firstname'];
		$phone = $this->request->post['phone'];
		$secondname = $this->request->post['secondname'];
		switch ($this->request->post['payment']) {
            case 'Услугой (Денежный перевод) от НоваяПочта':
                $payment_method = 'Послугою (Грошовий переказ) від НоваПошта';
                break;
            case 'Безналичный (с НДС)':
                $payment_method = 'Безналичный (с НДС)';
                break;
            case 'Безналичный (без НДС)':
                $payment_method = 'Безналичный (без НДС)';
                break;
            default:
                $payment_method = $this->request->post['payment'];
        }
		//$payment_method = $this->request->post['payment'];

        $payment_firm = '';
        $payment_edrpou = '';

		if (isset($this->request->post['pay_firm']))
		{
            $payment_firm = $this->request->post['pay_firm'];
            $payment_edrpou = $this->request->post['edrpou'];
		}

			$order_data = array();

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			$this->load->model('setting/extension');
			$this->load->model('catalog/product');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);

			$order_data['totals'] = $totals;

			$this->load->language('checkout/checkout');

			$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$order_data['store_id'] = $this->config->get('config_store_id');
			$order_data['store_name'] = $this->config->get('config_name'.$this->config->get('config_language_id'));

			if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
			} else {
				if ($this->request->server['HTTPS']) {
					$order_data['store_url'] = HTTPS_SERVER;
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
			}
			
			$this->load->model('account/customer');

		
		if (isset($this->session->data['guest']['customer_group_id'])) {
			$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
		} else {
			$order_data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

				$order_data['customer_id'] = 0;
				$order_data['firstname'] = $firstname;
				$order_data['lastname'] = $secondname;
				$order_data['email'] = $email;
				$order_data['telephone'] = $phone;


			$order_data['payment_firstname'] = $firstname;
			$order_data['payment_lastname'] = $secondname;
			$order_data['payment_company'] = $payment_firm;
			$order_data['payment_address_1'] = $address;
			$order_data['payment_address_2'] = '';
			$order_data['payment_city'] = $city;
			$order_data['payment_postcode'] = '';
			$order_data['payment_zone'] = '';
			$order_data['payment_zone_id'] = '';
			$order_data['payment_country'] = '';
			$order_data['payment_country_id'] = '';
			$order_data['payment_address_format'] = '';
			$order_data['payment_custom_field'] = array();

				$order_data['shipping_firstname'] = $firstname;
				$order_data['shipping_lastname'] = $secondname;
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = $address;
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = $city;
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_country'] = '';
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_custom_field'] = array();
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';
				
				$order_data['payment_method'] = $payment_method;
				$order_data['payment_code'] = $payment_edrpou;
				$order_data['comment'] = $comment;
				$order_data['shipping_code'] = '';
				$order_data['shipping_code'] = '';


			$data['products'] = $order_data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$data['products'][] = $order_data['products'][] = array(
					'product_id' => $product['product_id'],
					'image'      => $order_data['store_url']  . 'image/' . $product['image'],
					'url'        => $order_data['store_url'] . $product['model'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->model_catalog_product->getCurrencyCode($product['product_id'])),
					'total'      => $this->currency->format($product['total'],$this->model_catalog_product->getCurrencyCode($product['product_id'])),
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				);
			}
			$order_data['total'] = $total_data['total'];

			if (isset($this->request->cookie['tracking'])) {
				$order_data['tracking'] = $this->request->cookie['tracking'];

				$subtotal = $this->cart->getSubTotal();

				// Affiliate
				$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

				if ($affiliate_info) {
					$order_data['affiliate_id'] = $affiliate_info['customer_id'];
					$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
				}

				// Marketing
				$this->load->model('checkout/marketing');

				$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

				if ($marketing_info) {
					$order_data['marketing_id'] = $marketing_info['marketing_id'];
				} else {
					$order_data['marketing_id'] = 0;
				}
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
			}
			
			$order_data['order_status_id'] = 2;

			$order_data['language_id'] = 2;
			$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
			$order_data['currency_code'] = $this->model_catalog_product->getCurrencyCode($product['product_id']);
			$order_data['currency_value'] = $this->currency->getValue($this->model_catalog_product->getCurrencyCode($product['product_id']));
			$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$order_data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$order_data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['accept_language'] = '';
			}

			$this->load->model('checkout/order');

			$order_data['order_id'] = $this->model_checkout_order->addOrder($order_data);
			
		$order_info = $this->model_checkout_order->getOrder($order_data['order_id']);
			
		// Load the language for any mails that might be required to be sent out
		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		// HTML Mail
		$data['title'] = sprintf($language->get('text_subject'), $order_data['store_name'], $order_data['order_id']);
		
		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		
		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_data['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}
			

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_data['store_url']);
		$data['text_link'] = $language->get('text_link');
		$data['text_download'] = $language->get('text_download');
		$data['text_order_detail'] = $language->get('text_order_detail');
		$data['text_instruction'] = $language->get('text_instruction');
		$data['text_order_id'] = $language->get('text_order_id');
		$data['text_date_added'] = $language->get('text_date_added');
		$data['text_payment_method'] = $language->get('text_payment_method');
		$data['text_shipping_method'] = $language->get('text_shipping_method');
		$data['text_email'] = $language->get('text_email');
		$data['text_telephone'] = $language->get('text_telephone');
		$data['text_ip'] = $language->get('text_ip');
		$data['text_order_status'] = $language->get('text_order_status');
		$data['text_payment_address'] = $language->get('text_payment_address');
		$data['text_shipping_address'] = $language->get('text_shipping_address');
		$data['text_product'] = $language->get('text_product');
		$data['text_model'] = $language->get('text_model');
		$data['text_quantity'] = $language->get('text_quantity');
		$data['text_price'] = $language->get('text_price');
		$data['text_total'] = $language->get('text_total');
		$data['text_footer'] = $language->get('text_footer');

		$data['logo'] = $order_data['store_url']  . 'image/' . $this->config->get('config_logo2');
		$data['store_name'] = $order_data['store_name'];
		$data['store_url'] = $order_data['store_url'];
		$data['customer_id'] = $order_data['customer_id'];
		$data['link'] = $order_data['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_data['order_id'];


		$data['order_id'] = $order_data['order_id'];
		$data['payment_method'] = $order_data['payment_method'];

        if (isset($this->request->post['pay_firm']))
        {
            $data['payment_firm'] = $payment_firm;
            $data['payment_edrpou'] = $payment_edrpou;
        }

		$data['shipping_method'] = $order_data['shipping_method'];
		$data['email'] = $order_data['email'];
		$data['telephone'] = $order_data['telephone'];
		$data['ip'] = $order_data['ip'];

        $data['comment'] = $comment;

        $format = '{firstname} {lastname}' . "\n" . "\n" . '{address_1}' . "\n" . "\n" . '{city}';


		$find = array(
			'{firstname}',
			'{lastname}',
			'{address_1}',
			'{city}'
		);

		$replace = array(
			'firstname' => $order_data['payment_firstname'],
			'lastname'  => $order_data['payment_lastname'],
			'address_1' => $order_data['payment_address_1'],
			'city'      => $order_data['payment_city']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		if ($order_data['shipping_address_format']) {
			$format = $order_data['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . "\n" . '{address_1}' . "\n" . "\n" . '{city}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{address_1}',
			'{city}'
		);

		$replace = array(
			'firstname' => $order_data['shipping_firstname'],
			'lastname'  => $order_data['shipping_lastname'],
			'address_1' => $order_data['shipping_address_1'],
			'city'      => $order_data['shipping_city']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		$data['firstname'] = $order_data['shipping_firstname'];
        $data['lastname'] = $order_data['shipping_lastname'];
        $data['address'] = $order_data['shipping_address_1'];
        $data['city'] = $order_data['shipping_city'];

			
		$data['totals'] = array();
		
		
		$order_totals = $this->model_checkout_order->getOrderTotals($order_data['order_id']);

		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}
			
			
		$this->load->model('setting/setting');
		
		$from = $this->model_setting_setting->getSettingValue('config_email', 0);
		$from2 = 'rs@ip-tech.com.ua';

		if (!$from) {
			$from = $this->config->get('config_email');
		}

		
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($email);
		$mail->setFrom($from);
		$mail->setSender(html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order_data['store_name'], $order_data['order_id']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('mail/order_add', $data));
		$mail->send();
		
		
		// Админу

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo('info@ip-tech.com.ua');
		//$mail->setTo('kostyafilon@yahoo.com');

		$mail->setFrom($from);
		$mail->setSender(html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order_data['store_name'], $order_data['order_id']), ENT_QUOTES, 'UTF-8'));
		//$mail->setHtml($this->load->view('mail/order_add', $data));
		$mail->setHtml($this->load->view('mail/order_alert', $data));
		$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				$email = trim($email);
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}

			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
			
		$json['success'] = true;
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}