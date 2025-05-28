<?php
class ControllerFeedRozetkaBluetti extends Controller {
private $eol = "\n";

    public function index() {
		
        $products = $this->getProducts();
		
        header('Content-type: text/html; charset=utf-8');
		$yml  = '<?xml version="1.0" encoding="utf-8"?>' . $this->eol;
		$yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $this->eol;
		$yml .= '<yml_catalog>';
		$yml .= '<shop>';
		$yml .= '<name>Grandstream/Snom/Jabra/OpenVox</name>
                 <company>OV Украина</company>
                 <url>https://ip-tech.com.ua</url>
                 <email>info@ip-tech.com.ua</email>
                 <currencies>
                 <currency id="UAH" rate="1"/>
                 </currencies>
                 <categories>
                 <category id="80253">Компьютеры и ноутбуки</category>
                 <category id="80111" parent_id="80253">Сетевое оборудование</category>
                 <category id="80197" parent_id="80111">IP-телефония</category>
                 <category id="4655763" parent_id="80111">Ретрансляторы Wi-Fi сигнала</category>
                 <category id="156631" parent_id="80111">Антенны и кабели</category>
                 <category id="156790" parent_id="80111">IP-камеры</category>
                 <category id="4627949">Смартфоны, ТВ и электроника</category>
                 <category id="80257" parent_id="4627949">Телефоны, MP3, GPS</category>
                 <category id="80027" parent_id="80257">Наушники</category>
                 <category id="4627851">Товары для бизнеса</category>
                 <category id="4628117" parent_id="4627851">Системы охраны и безопасности</category>
                 <category id="259632" parent_id="4628117">Домофоны</category>
                 <category id="259633" parent_id="259632">Видеодомофоны</category>
                 <category id="80263" parent_id="80257">Аксессуары для мобильных телефонов и смартфонов</category>
                 <category id="80032" parent_id="80263">Гарнитуры bluetooth</category>
                 <category id="202253" parent_id="80257">Аксессуары для наушников</category>
                 <category id="80195" parent_id="80111">Беспроводные точки доступа</category>
                 <category id="4668163" parent_id="80253">Оборудование для конференций и видеоконференций</category>
                 </categories>';
		$yml .= '<offers>' . $this->eol;
		
	
        foreach($products as $product) {
            if ($product['price'] == 0) {
                $available = 'false';
            } else {
                $available = 'true';
            }

            $yml .= '<offer id="' . $product['product_id'] . '" available="'. $available .'">' . $this->eol;

            $seoUrl = $this->getSeoUrl($product['product_id']);
            $yml .= '<url>';
            $yml .= HTTPS_SERVER . $seoUrl;
            $yml .= '</url>' . $this->eol;
			
            $special = $this->getSpecialPrice($product['product_id']);
            if ($special) {
                $yml .= '<price>';
                if ($product['rate'] != 0) {
                    $yml .= $special * $product['rate'];
                } else {
                    $yml .= $this->currency->format($special, $product['currency_code'], '', '');
                }
                $yml .= '</price>' . $this->eol;

                $yml .= '<price_old>';
                if ($product['rate'] != 0) {
                    $yml .= $product['price'] * $product['rate'];
                } else {
                    $yml .= $this->currency->format($product['price'], $product['currency_code'], '', '');
                }
                $yml .= '</price_old>' . $this->eol;
            } else {
                $yml .= '<price>';
                if ($product['rate'] != 0) {
                    $yml .= $product['price'] * $product['rate'];
                } else {
                    $yml .= $this->currency->format($product['price'], $product['currency_code'], '', '');
                }
                $yml .= '</price>' . $this->eol;
            }

            $yml .= '<currencyId>';
            $yml .= 'UAH';
            $yml .= '</currencyId>' . $this->eol;

            $yml .= '<categoryId>';
            $yml .= '';
            $yml .= '</categoryId>' . $this->eol;

            $yml .= '<picture>';
            $yml .= HTTPS_SERVER . 'image/' . $product['image'];
            $yml .= '</picture>' . $this->eol;

            $yml .= '<delivery>';
            $yml .= 'true';
            $yml .= '</delivery>' . $this->eol;

            $yml .= '<name>';
            $yml .= 'Зарядна станція Bluetti AC200MAX 2048Wh, 568889mAh, 2200W (PB930906)';
            $yml .= '</name>' . $this->eol;

            $yml .= '<vendor>';
            $yml .= $this->getManufacture($product['manufacturer_id']);
            $yml .= '</vendor>' . $this->eol;

            $yml .= '<description><![CDATA[';
            $yml .= strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'));
            $yml .= ']]></description>' . $this->eol;

			/*
            $productParams = $this->getProductParams($product['product_id']);
            foreach($productParams as $productParam) {
                $yml .= '<param name="' . $productParam['param'] . '">' . $productParam['value'] . '</param>';
            }
			*/
			$yml .= '<param name="Індикація">З дисплеєм</param>';
			$yml .= '<param name="Вихідна потужність (загальна), Вт">2200</param>';
			$yml .= '<param name="Ємність батареї, мА*год">2048</param>';
			$yml .= '<param name="Вихідна сила струму, А">3</param>';
			$yml .= '<param name="Вихідна напруга, В">230</param>';
			$yml .= '<param name="Час заряджання від мережі">5.5-6 годин</param>';
			$yml .= '<param name="Кількість зарядок пристроїв">3500+ (до 80%), 6000+ (до 50%)</param>';
			$yml .= '<param name="Вага">28.1кг</param>';
			
            $yml .= '<param name="Колір">Сірий</param>';
            $yml .= '<param name="Гарантія">24</param>';
            $yml .= '<param name="Країна-виробник товару">США</param>';
			
			$yml .= '<param name="Призначення"><![CDATA[Для всього будинку<br>
			Для великої побутової техніки<br>
			Для дрібної побутової техніки<br>
			Для повербанків<br>
			Фотоапаратів<br>
			Для електроніки<br>]]></param>';
			
			$yml .= "<param name='Вихідні інтерфейси'><![CDATA[4 x USB Type A<br>
			4 х Розетка 220 В<br>
			DC-роз'єм<br>
			USB Type C<br>
			Гніздо прикурювача<br>]]></param>";
			
			$yml .= "<param name='Тип заряджання станції'><![CDATA[Мережі<br>
			Заряджання від автомобіля<br>
			Заряджання від сонячної панелі<br>
			Постійний струм<br>]]></param>";
			

            $yml .= '<stock_quantity>';
            $yml .= '20';
            $yml .= '</stock_quantity>' . $this->eol;

            $yml .= '</offer>' . $this->eol;
        }
        $yml .= '</offers>';
        $yml .= '</shop>';
        $yml .= '</yml_catalog>';

        $this->response->addHeader('Content-Type: application/xml');
        $this->response->setOutput($yml);
    }

    public function getProducts() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id WHERE p.product_id = 13408 AND language_id = 2");
        return $query->rows;
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
  
    public function getManufacture($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = " . $id);
        return $query->row['name'];
    }

    public function getName($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = " . $id);
        return $query->row['name'];
    }

    public function getSeoUrl($id) {
        $str = "product_id=" . $id;
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query='" . $str . "'");
        return $query->row['keyword'];
    }
}