<?php
class ControllerExtensionModulefxsitemap extends Controller {
	private $error = array();

	public function index() {
	
		$this->load->language('extension/module/fx_sitemap');		
		$this->document->setTitle('#FX Sitemap⁶');
		$this->load->model('setting/setting');		
		$host = $this->config->get('config_ssl') ? HTTPS_CATALOG : HTTP_CATALOG;
		$host = $this->config->get('config_secure') ? str_replace('http:/', 'https:/', $this->config->get('config_ssl')) : $this->config->get('config_url');		
        $text_strings = array(
            'heading_title',
            'text_edit',
        	'text_no',
        	'text_yes',
        	'button_save',
        	'button_cancel',        	
			'text_success',
        	'text_modules',
        	'text_defalt',
        	'produsts',        	
			'text_key',        	
			'categories',        	
			'brands',        	
			'other',        	
			'status',        	
			'service',
			'text_only_seo_url',
			'text_postfix',
			'text_sort',
			'information',
         );

        foreach ($text_strings as $text) {
            $data[$text] = $this->language->get($text);
        }
		
		$list = array (					
			'sitemap_on',			
			'log',			
			'products_on',			
			'categories_on',			
			'brands_on',			
			'blog_on',			
			'blog_route',			
			'article_on',			
			'article_route',			
			'news_on',			
			'news_route',			
			'records_on',			
			'records_route',			
			'ocfilter_on',			
			'mfp_on',			
			'vier_on',			
			'slash',			
			'product_changefreq',
			'product_priority',
			'product_lastmod',
			'category_changefreq',
			'category_priority',			
			'category_lastmod',
			'brands_changefreq',			
			'brands_priority',			
			'brands_lastmod',	
			'only_seo_url',	
			'filterpro_on',
			'ultra',
			'express',
			'express_cat',
			'text_postfix',
			'categories_from_db',
			'add_file_on',
			'exclude_file_on',
			'multi',
			'limit',
			'informations_on',
			'multistore',
			'multilang',
			'sort',
			'home_on',
			'special_on',
			'contact_on',
			'brands_category_on',
			'http_header',
			'add_to_multi_on',
		);
		
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		
			$postdata = $this->request->post;
			
			
			
			file_put_contents(DIR_CONFIG.'add.sitemap', $postdata['add_file']);
			file_put_contents(DIR_CONFIG.'delete.sitemap', $postdata['exclude_file']);
			file_put_contents(DIR_CONFIG.'add_to_multi.sitemap', $postdata['add_to_multi']);
			
			unset($postdata['add_file']);
			unset($postdata['exclude_file']);
			unset($postdata['add_to_multi']);
			
			foreach ($list as $key) {
				if (!isset($postdata[$key])) $postdata[$key] = '';
			}
			
			$settings = array('fx_sitemap_settings' => json_encode($postdata, true));

			
			$this->model_setting_setting->editSetting('fx_sitemap', $settings);
        	$this->session->data['success'] = $this->language->get('text_success');
			
			if ((float)VERSION < 1.9){
				$this->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else if ((float)VERSION < 2.3){
				$this->response->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else if ((float)VERSION < 2.4){
				$this->response->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else{
				$this->response->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
        }
        
		
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();		
		$data['user_token'] = $this->session->data['user_token'];
        $data['breadcrumbs'] = array();				
		if ((float)VERSION < 2.3)	{
			$data['breadcrumbs'][] = array(
					'text' => '<i class="fa fa-home"></i>',
					'href' => '/admin/?user_token=' . $this->session->data['user_token'],
			);
			$data['breadcrumbs'][] = array(
					'text' => '<i class="fa fa-puzzle-piece fa-fw"></i>',
					'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL') ,
			);
			$data['breadcrumbs'][] = array(
					'text' => '#FX Sitemap',
					'href' => $this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL') ,
			);
		} else if ((float)VERSION == 2.3){					
			$data['breadcrumbs'][] = array(
					'text' => '<i class="fa fa-home"></i>',
					'href' => '/admin/?user_token=' . $this->session->data['user_token'],
			);
			$data['breadcrumbs'][] = array(
					'text' => '<i class="fa fa-puzzle-piece fa-fw"></i>' ,
					'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], 'SSL') ,
			);
			$data['breadcrumbs'][] = array(
					'text' => '#FX Sitemap',
					'href' => $this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL') ,
			);
		} else {					
			$data['breadcrumbs'][] = array(
					'text' => '<i class="fa fa-home"></i>',
					'href' => '/admin/?user_token=' . $this->session->data['user_token'],
			);
			$data['breadcrumbs'][] = array(
					'text' => '<i class="fa fa-puzzle-piece fa-fw"></i>',
					'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);
			$data['breadcrumbs'][] = array(
					'text' => 'FX Sitemap⁴',
					'href' => $this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL') ,
			);
		}
					
		$data['action'] = $data['breadcrumbs'][2]['href'];
		$data['cancel'] = $data['breadcrumbs'][1]['href'];				
		$data['changefreqs'] = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');		

		$languages = $this->model_localisation_language->getLanguages();		
		
		
		$data['link'] = 'index.php?route=' . ((float)VERSION < 2.3 ? '' : 'extension/') . 'feed/' . 'fx_sitemap';		
		
		$postfix = $this->config->get('config_seo_url_postfix') ? $this->config->get('config_seo_url_postfix') : '';
		
		$settings = json_decode($this->config->get('fx_sitemap_settings'), true);
		
		$data['default'] = isset($settings['default']) ? $settings['default'] : $host;				
		$data['key'] = isset($settings['key']) ? $settings['key'] : rand();
		$data['postfix'] = isset($settings['postfix']) ? $settings['postfix'] : $postfix;
		$data['category_priority'] = isset($settings['category_priority']) ? $settings['category_priority'] : '0.9';
		$data['product_priority'] = isset($settings['product_priority']) ? $settings['product_priority'] : '0.8';
		
		$data['log_file'] = $this->getfiledata(DIR_LOGS.'fx_sitemap.log');
		$data['add_file'] = $this->getfiledata(DIR_CONFIG.'add.sitemap');
		$data['exclude_file'] = $this->getfiledata(DIR_CONFIG.'delete.sitemap');
		$data['add_to_multi'] = $this->getfiledata(DIR_CONFIG.'add_to_multi.sitemap');
		
		foreach ($list as $key) {
			if (!isset($data[$key])) $data[$key] = isset($settings[$key]) ? $settings[$key] : '';
		}
		
		if ((float)VERSION < 2){
			$this->data = $this->data + $data;
			$this->load->model('design/layout');
			$this->data['layouts'] = $this->model_design_layout->getLayouts();
			$this->template = 'extension/module/fx_sitemap.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
			$this->response->setOutput($this->render());
		}
		else if ((float)VERSION < 3){
				$data['header'] = $this->load->controller('common/header');
				$data['column_left'] = $this->load->controller('common/column_left');
				$data['footer'] = $this->load->controller('common/footer');
				$this->response->setOutput($this->load->view('extension/module/fx_sitemap.tpl', $data));
		}
		else{
				$data['header'] = $this->load->controller('common/header');
				$data['column_left'] = $this->load->controller('common/column_left');
				$data['footer'] = $this->load->controller('common/footer');
				$this->config->set('template_engine', 'template');
				$this->response->setOutput($this->load->view('extension/module/fx_sitemap', $data));
		}
	}	
	
	protected function getfiledata($file){
		
		if (file_exists($file)) return file_get_contents($file);
		
		return '';
	}

	
	public function savedata() {
	
		$this->load->language('extension/module/fx_sitemap');
		
        $text_strings = array(	
        	'text_success'
         );

        foreach ($text_strings as $text) {
            $data[$text] = $this->language->get($text);
        }

		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		
		$languages = $this->model_localisation_language->getLanguages();

			
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {						
			$settings = array('fx_sitemap_settings' => json_encode($this->request->post));
        	$this->model_setting_setting->editSetting('fx_sitemap', $settings);
        	$this->session->data['success'] = $this->language->get('text_success');
			
			if ((float)VERSION < 1.9){
				$this->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else if ((float)VERSION < 2.3){
				$this->response->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else if ((float)VERSION < 2.4){
				$this->response->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else{
				$this->response->redirect($this->url->link('extension/module/fx_sitemap', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
        }
		
	}

    protected function validate() {
    	if (!$this->user->hasPermission('modify', 'extension/module/fx_sitemap') && !$this->user->hasPermission('modify', 'extension/module/fx_sitemap')) {
    		$this->error['warning'] = $this->language->get('error_permission');
    	}
    
    	return !$this->error;
    }


    public function uninstall() {
    	$this->load->model('extension/event');
    	$this->model_extension_event->deleteEvent('fx_sitemap');
    	 
    }
}