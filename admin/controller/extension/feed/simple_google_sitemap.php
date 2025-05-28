<?php

class ControllerExtensionFeedSimpleGoogleSitemap extends Controller {

    private $version = '2.1';
    private $error = array();
    private $token_var;
    private $extension_var;
    private $prefix;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->token_var = (version_compare(VERSION, '3.0', '>=')) ? 'user_token' : 'token';
        $this->extension_var = (version_compare(VERSION, '3.0', '>=')) ? 'marketplace' : 'extension';
        $this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'feed_' : '';
    }

    public function install() {

    }

    public function uninstall() {

    }

    public function index() {
        $data = $this->load->language('extension/feed/simple_google_sitemap');

        $heading_title = preg_replace('/^.*?\|\s?/ius', '', $this->language->get('heading_title'));
        $data['heading_title'] = $heading_title;
        $this->document->setTitle($heading_title);

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting($this->prefix . 'simple_google_sitemap', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['apply'])) {
                $this->response->redirect($this->url->link('extension/feed/simple_google_sitemap', $this->token_var . '=' . $this->session->data[$this->token_var], true));
            } else {
                $this->response->redirect($this->url->link($this->extension_var . '/extension', $this->token_var . '=' . $this->session->data[$this->token_var] . '&type=feed', true));
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_var . '=' . $this->session->data[$this->token_var], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_var . '/extension', $this->token_var . '=' . $this->session->data[$this->token_var] . '&type=feed', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $heading_title,
            'href' => $this->url->link('extension/feed/simple_google_sitemap', $this->token_var . '=' . $this->session->data[$this->token_var], true)
        );

        $data['prefix'] = $this->prefix;
        $data['token_var'] = $this->token_var;
        $data[$this->token_var] = $this->session->data[$this->token_var];
        $data['action'] = $this->url->link('extension/feed/simple_google_sitemap', $this->token_var . '=' . $this->session->data[$this->token_var], true);
        $data['cancel'] = $this->url->link($this->extension_var . '/extension', $this->token_var . '=' . $this->session->data[$this->token_var] . '&type=feed', true);
        $data['text_info'] = sprintf($this->language->get('text_info'), $this->version);

        if (isset($this->request->post[$this->prefix . 'simple_google_sitemap_status'])) {
            $data[$this->prefix . 'simple_google_sitemap_status'] = $this->request->post[$this->prefix . 'simple_google_sitemap_status'];
        } else {
            $data[$this->prefix . 'simple_google_sitemap_status'] = $this->config->get($this->prefix . 'simple_google_sitemap_status');
        }
        if (isset($this->request->post[$this->prefix . 'simple_google_sitemap_image'])) {
            $data[$this->prefix . 'simple_google_sitemap_image'] = $this->request->post[$this->prefix . 'simple_google_sitemap_image'];
        } else {
            $data[$this->prefix . 'simple_google_sitemap_image'] = $this->config->get($this->prefix . 'simple_google_sitemap_image');
        }
        if (isset($this->request->post[$this->prefix . 'simple_google_sitemap_log'])) {
            $data[$this->prefix . 'simple_google_sitemap_log'] = $this->request->post[$this->prefix . 'simple_google_sitemap_log'];
        } else {
            $data[$this->prefix . 'simple_google_sitemap_log'] = $this->config->get($this->prefix . 'simple_google_sitemap_log');
        }
        
        $site = $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG;
        $data['feed'] = $site . 'index.php?route=extension/feed/simple_google_sitemap';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/feed/simple_google_sitemap', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/feed/simple_google_sitemap')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }


        return !$this->error;
    }
}
