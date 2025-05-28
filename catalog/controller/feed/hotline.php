<?php
class ControllerFeedHotline extends Controller {
private $eol = "\n";

    public function index() {

        $products = $this->getProducts();

        header('Content-type: text/html; charset=utf-8');
		$yml  = '<?xml version="1.0" encoding="utf-8"?>' . $this->eol;
		$yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $this->eol;
        $yml .= '<price>' . $this->eol;
        $yml .= '<date>' .  date('Y-m-d H:i') .  '</date>' . $this->eol;

        $yml .= '<delivery id="1" type="address" carrier="SLF" inCheckout="true" time="1" region="65*" />' . $this->eol;
        $yml .= '<delivery id="2" type="warehouse" inCheckout="true" time="1" carrier="NP" region="01*-94*" />' . $this->eol;


		$yml .= '<categories>' . $this->eol;
		$yml .= '<category>
                 <id>1</id>
                 <name>ТВ, Аудио, Видео, Фото</name>
                 </category>
                 <category>
                 <id>2</id>
                 <parentId>1</parentId>
                 <name>Наушники, гарнитуры</name>
                 </category>
                 <category>
                 <id>3</id>
                 <name>Компьютеры, Сети</name>
                 </category>
                 <category>
                 <id>4</id>
                 <parentId>3</parentId>
                 <name>Оборудование для конференц-связи</name>
                 </category>
                 <category>
                 <id>5</id>
                 <parentId>3</parentId>
                 <name>Коммутаторы</name>
                 </category>
                 <category>
                 <id>6</id>
                 <parentId>3</parentId>
                 <name>Wi-Fi маршрутизаторы и точки доступа</name>
                 </category>
                 <category>
                 <id>7</id>
                 <parentId>3</parentId>
                 <name>VoIP-шлюзы</name>
                 </category>
                 <category>
                 <id>8</id>
                 <parentId>3</parentId>
                 <name>IP-телефоны</name>
                 </category>
                 <category>
                 <id>9</id>
                 <parentId>3</parentId>
                 <name>Кабели витая пара, патч-корды</name>
                 </category>
                 <category>
                 <id>10</id>
                 <parentId>3</parentId>
                 <name>Медиаконвертеры</name>
                 </category>
                 <category>
                 <id>11</id>
                 <parentId>3</parentId>
                 <name>Межсетевые экраны (Firewall)</name>
                 </category>
                 <category>
                 <id>12</id>
                 <parentId>3</parentId>
                 <name>Сетевое оборудование (разное)</name>
                 </category>
                 <category>
                 <id>13</id>
                 <parentId>1</parentId>
                 <name>Аксессуары для наушников и гарнитур</name>
                 </category>
                 <category>
                 <id>14</id>
                 <name>Офис</name>
                 </category>
                 <category>
                 <id>15</id>
                 <parentId>14</parentId>
                 <name>Средства связи</name>
                 </category>
                 <category>
                 <id>16</id>
                 <parentId>15</parentId>
                 <name>Мини-АТС (базовые блоки)</name>
                 </category>
                 ' . $this->eol;
		$yml .= '</categories>' . $this->eol;
		$yml .= '<items>' . $this->eol;
		foreach($products as $product) {
		    $yml .= '<item>' . $this->eol;

            $yml .= '<id>';
            $yml .= $product['product_id'];
            $yml .= '</id>' . $this->eol;

            $yml .= '<categoryId>';
            $yml .= $product['category'];
            $yml .= '</categoryId>' . $this->eol;

            $yml .= '<code>';
            $yml .= $product['model'];
            $yml .= '</code>' . $this->eol;

            $yml .= '<vendor>';
            $yml .= $this->getManufacture($product['manufacturer_id']);
            $yml .= '</vendor>' . $this->eol;

            $yml .= '<name>';
            $yml .= $this->getName($product['product_id']);
            $yml .= '</name>' . $this->eol;

            $yml .= '<description><![CDATA[';
            $yml .= $product['description'];
            $yml .= ']]></description>' . $this->eol;

            $yml .= '<url>';
            $yml .= HTTPS_SERVER . $product['model'];
            $yml .= '</url>' . $this->eol;

            $yml .= '<image>';
            $yml .= HTTPS_SERVER . 'image/' . $product['image'];
            $yml .= '</image>' . $this->eol;

            $yml .= '<priceRUAH>';
            $special = $this->getSpecialPrice($product['product_id']);
            if ($special) {
                $yml .= $this->currency->format($special, $product['currency_code'], '', '');
            } else {
                $yml .= $this->currency->format($product['price'], $product['currency_code'], '', '');
            }
            $yml .= '</priceRUAH>' . $this->eol;

            $yml .= '<priceRUSD>';
            $yml .= round($product['price']);
            $yml .= '</priceRUSD>' . $this->eol;

            $yml .= '<stock>';
            if ($product['quantity'] > 0) {
                $yml .= 'В наличии';
            } else {
                $yml .= 'Нет';
            }
            $yml .= '</stock>' . $this->eol;

            $yml .= '<guarantee>';
            $yml .= $product['guarantee'];
            $yml .= '</guarantee>' . $this->eol;

            $yml .= '<param name="Страна изготовления">';
            $yml .= $product['manufacture'];
            $yml .= '</param>' . $this->eol;

            $yml .= '<param name="Оригинальность">';
            $yml .= $product['original'];
            $yml .= '</param>' . $this->eol;

            $yml .= '<condition>';
            $yml .= 0;
            $yml .= '</condition>' . $this->eol;

            $yml .= '<custom>';
            $yml .= 1;
            $yml .= '</custom>' . $this->eol;

		    $yml .= '</item>' . $this->eol;
		}

		$yml .= '</items>' . $this->eol;

        $yml .= '</price>';
        $this->response->addHeader('Content-Type: application/xml');
        $this->response->setOutput($yml);
    }

    public function getProducts() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_hotline ph ON p.product_id = ph.product_id WHERE ph.publish=true");
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