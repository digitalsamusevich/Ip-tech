<?php
class ControllerFeedGoogle extends Controller {
private $eol = "\n";

    public function index() {

        $products = $this->getProducts();
        //var_dump($products);
        header('Content-type: text/html; charset=utf-8');
		$yml  = '<?xml version="1.0" encoding="utf-8"?>' . $this->eol;
		$yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $this->eol;
		$yml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		$yml .= '<channel>' . $this->eol;

        foreach($products as $product) {
            $yml .= '<item>' . $this->eol;

            $yml .= '<g:id>';
            $yml .= $product['product_id'];
            $yml .= '</g:id>' . $this->eol;

           // $yml .= '<code>';
           // $yml .= $product['model'];
            //$yml .= '</code>' . $this->eol;

            $yml .= '<g:brand>';
            $yml .= $this->getManufacture($product['manufacturer_id']);
            $yml .= '</g:brand>' . $this->eol;

            $yml .= '<title>';
            $yml .= $this->getName($product['product_id']);
            $yml .= '</title>' . $this->eol;

            $yml .= '<description><![CDATA[';
            $yml .= $product['description'];
            $yml .= ']]></description>' . $this->eol;

            $yml .= '<link>';
            $yml .= HTTPS_SERVER . $product['model'];
            $yml .= '</link>' . $this->eol;

            $yml .= '<g:image_link>';
            $yml .= HTTPS_SERVER . 'image/' . $product['image'];
            $yml .= '</g:image_link>' . $this->eol;

            $yml .= '<g:price>';
            $yml .= $this->currency->format($product['price'], $product['currency_code'], '', '');
            $yml .= '</g:price>' . $this->eol;

            $special = $this->getSpecialPrice($product['product_id']);
            if ($special) {
                $yml .= '<g:sale_price>';
                $yml .= $this->currency->format($special, $product['currency_code'], '', '');
                $yml .= '</g:sale_price>';
            }

            $yml .= '<condition>';
            $yml .= 'new';
            $yml .= '</condition>' . $this->eol;

            $yml .= '<g:availability>';
            if ($product['quantity'] > 0) {
                $yml .= 'in_stock';
            } else {
                $yml .= 'out_stock';
            }
            $yml .= '</g:availability>' . $this->eol;

            $yml .= '</item>' . $this->eol;
        }

        $yml .= '</channel>';
        $yml .= '</rss>';

        $this->response->addHeader('Content-Type: application/xml');
        $this->response->setOutput($yml);
    }

    public function getProducts() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_hotline ph LEFT JOIN " . DB_PREFIX . "product p ON p.product_id = ph.product_id WHERE ph.publish_google=true");
        return $query->rows;
    }

    public function getManufacture($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = " . $id);
        return $query->row['name'];
    }

    public function getName($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = " . $id);
        return $query->row['name'];
    }

    public function getSpecialPrice($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = " . $id);
        if ($query->row) {
            if (date('Y-m-d') <= $query->row['date_end'] && date('Y-m-d') >= $query->row['date_start']){
                return $query->row['price'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}