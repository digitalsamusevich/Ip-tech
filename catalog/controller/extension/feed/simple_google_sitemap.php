<?php

class ControllerExtensionFeedSimpleGoogleSitemap extends Controller {

    private $error = array();
    private $prefix;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'feed_' : '';
    }

    public function index() {

        if ($this->config->get($this->prefix . 'simple_google_sitemap_status')) {
            $this->load->model('extension/feed/simple_google_sitemap');
            $this->load->model('tool/image');
            $args = array(
                'language_id' => (int) $this->config->get('config_language_id'),
                'store_id' => (int) $this->config->get('config_store_id')
            );

            $start = microtime(true);
            $output = '<?xml version="1.0" encoding="UTF-8"?>';
            $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'. PHP_EOL;

            $products = $this->model_extension_feed_simple_google_sitemap->getProducts($args);
            if ($products) {
                foreach ($products as $product) {
                    $output .= "<url>" . PHP_EOL;
                    $output .= "  <loc>" . $this->url->link('product/product', 'product_id=' . $product['product_id'], true) . "</loc>" . PHP_EOL;
                    $output .= "  <lastmod>" . $product['date_modified'] . "</lastmod>" . PHP_EOL;
                    $output .= "  <changefreq>weekly</changefreq>" . PHP_EOL;
                    $output .= "  <priority>1.0</priority>" . PHP_EOL;
                    if ($this->config->get($this->prefix . 'simple_google_sitemap_image') && !empty($product['image']) && is_file(DIR_IMAGE . $product['image'])) {
                        if ($this->config->get($this->prefix . 'simple_google_sitemap_image') == 1) {              
                            $image = @$this->model_tool_image->resize($product['image'], $this->config->get('theme_' . str_replace('theme_', '', $this->config->get('config_theme')) . '_image_popup_width'), $this->config->get('theme_' . str_replace('theme_', '', $this->config->get('config_theme')) . '_image_popup_height'));
                        } else {
                            $image = ($this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER) . 'image/' . $product['image'];
                        }
                        $output .= "  <image:image>" . PHP_EOL;
                        $output .= '    <image:loc>' . $image . "</image:loc>" . PHP_EOL;
                        $output .= '    <image:caption><![CDATA[' . $product['name']. "]]></image:caption>" . PHP_EOL;
                        $output .= '    <image:title><![CDATA[' . $product['name']. "]]></image:title>" . PHP_EOL;
                        $output .= "  </image:image>" . PHP_EOL;
                    }



                    $output .= "</url>" . PHP_EOL;
                }
            }

            $categories = $this->model_extension_feed_simple_google_sitemap->getCategories($args);
            if ($categories) {
                foreach ($categories as $category) {
                    $output .= "<url>" . PHP_EOL;
                    $output .= "  <loc>" . $this->url->link('product/category', 'path=' . $category['category_id'], true) . "</loc>" . PHP_EOL;
                    $output .= "  <lastmod>" . $category['date_modified'] . "</lastmod>" . PHP_EOL;
                    $output .= "  <changefreq>weekly</changefreq>" . PHP_EOL;
                    $output .= "  <priority>0.7</priority>" . PHP_EOL;
                    $output .= "</url>" . PHP_EOL;
                }
            }

            $manufacturers = $this->model_extension_feed_simple_google_sitemap->getManufacturers($args);
            if ($manufacturers) {
                foreach ($manufacturers as $manufacturer) {
                    $output .= "<url>" . PHP_EOL;
                    $output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'], true) . "</loc>" . PHP_EOL;
                    $output .= "  <changefreq>weekly</changefreq>" . PHP_EOL;
                    $output .= "  <priority>0.7</priority>" . PHP_EOL;
                    $output .= "</url>" . PHP_EOL;
                }
            }

            $informations = $this->model_extension_feed_simple_google_sitemap->getInformations($args);
            if ($informations) {
                foreach ($informations as $information) {
                    $output .= "<url>" . PHP_EOL;
                    $output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id'], true) . "</loc>" . PHP_EOL;
                    $output .= "  <changefreq>weekly</changefreq>" . PHP_EOL;
                    $output .= "  <priority>0.5</priority>" . PHP_EOL;
                    $output .= "</url>" . PHP_EOL;
                }
            }
                  
            $output .= "</urlset>" . PHP_EOL;

            $time = microtime(true) - $start;
            $this->log(sprintf('Sitemap was generated for %.4F s. ', $time) . 'Request from ' . $_SERVER["REMOTE_ADDR"] . ' ' . $_SERVER['HTTP_USER_AGENT']);

            $this->response->addHeader('Content-Type: application/xml');
            $this->response->setOutput($output);
        }
    }

    private function log($string) {
        if ($this->config->get($this->prefix . 'simple_google_sitemap_log')) {
            $log = new Log('simple_google_sitemap.log');
            $log->write($string);
        }
    }

}
