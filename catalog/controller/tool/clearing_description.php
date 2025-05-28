<?php
class ControllerRepairClearingDescription extends Controller {
	public function index() {
        $this->response->setOutput($this->load->view('repair/clearing_description'));
      
	 }
    
    public function getItems() {
        $this->load->model('repair/clearing_description');

        $results = $this->model_repair_clearing_description->getProducts();

        $json = array();
        foreach ($results as $key => $result) {
            $json['items'][] = $key;
        }

        $json['total_items'] = count($json['items']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function clear() {

            $json = array();

            if(!isset($this->request->post['current_key'])){
                $json['current_key'] =  0;
            } else {
                $json['current_key'] = $this->request->post['current_key'];
            }
    
        
            if(!isset($_SESSION['time'])){
                $_SESSION['time'] = time();
            } 
            
            $this->load->model('repair/clearing_description');
        
            $json['next_id'] = $this->request->post['next_id'];
            $json['total'] = $this->request->post['total'];
            $json['total_items'] = $this->request->post['total_items'];

        
            if(isset($this->request->post['desc'])){
                $json['total'] = $json['total'] + 1; 
                $json['product_id'] = $this->request->post['product_id'];
                $description = $this->request->post['desc'];

                $json['desc'] = html_entity_decode($description);
                //$json['query'] = $this->model_repair_clearing_description->updateProducts($json['product_id'], $description);
            }
        
            $json['current_key'] = $json['current_key'] + 1;
        
        
            if($json['next_id'] != false){
            $result = $this->model_repair_clearing_description->getProduct($json['next_id'], $json['current_key']);
        
            $json['product_id'] = $result['product_id'];
        
            $json['description'] = html_entity_decode($result['description']); 

            preg_match_all('/<img.*?src\s*="([^"]+)".*?>/', $json['description'], $match);
            $json['match'] = $match;
            $img_arr = $match[0];        
            $src_arr = $match[1];
            
            $json['images'] = array();
              
            foreach($src_arr as $key => $image){
                $json['images'][$key] = $this->download($json['product_id'], $image);
                
                if($json['images'][$key]['status'] == 'remove'){
                    $json['description'] = str_replace($img_arr[$key], '', $json['description']);
                } else {
                    $json['description'] = str_replace($src_arr[$key], $json['images'][$key]['path'], $json['description']);
                }
            }
    
            $json['name'] = mb_substr($result['name'], 0, 80, 'UTF-8');
            $json['model'] = mb_substr($result['model'], 0, 80, 'UTF-8');
            } 
        
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        
    }

    
    public function stop() {
        $time = time() - $_SESSION['time'];
        $total_wrap = time() - $_SESSION['time'];
        
        echo "<div id='time'> Time: <span>" . gmdate("H:i:s", $time) . "</span></div>";
        echo "<span id='total'>Total:" . $_SESSION['total'] . "&nbsp;&nbsp [0-". $total_wrap . "]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        
        
        unset($_SESSION['current_key']);
        unset($_SESSION['info']);
        unset($_SESSION['total']);
        unset($_SESSION['refresh']);
        unset($_SESSION['time']);
        unset($_SESSION['total_refresh']);
        unset($_SESSION['product_id']);
        unset($_SESSION['resending']);
        unset($_SESSION['all_key']);
    }
    
    
    private function download($product_id, $src) {

        $json = array();

        $json['product_id'] = $product_id;
        $json['src'] = $src;

        if(preg_match('/http/i', $src)){
            $src = $src;
        } else {
            $src = 'http://www.ip-tech.com.ua'.$src;
        }

        $curl = curl_init($src);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($curl, CURLOPT_TIMEOUT, 5);
        
        $image = curl_exec($curl);
            
        if($image == false) {
            $httpCode = 404;
            $imageType = false;
        } else {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $httpType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
           
            
            $imageType = preg_match('/image/', $httpType) == 0 ? false : true;
            $json['info']['httpType'] = $httpType;
        }
        
        curl_close($curl);
        

        if($httpCode != 200 || $imageType == false) {
            $json['info']['httpCode'] = $httpCode;
            $json['status'] = 'remove';
        } else {
            $info_image = imagecreatefromstring($image);

            $width = imagesx($info_image);
            $height = imagesy($info_image);

            $json['size']['width'] = $width;
            $json['size']['height'] = $height;


            if($width == 1 || $height == 1) {
                $json['status'] = 'remove';
            } else {

                $this->load->model('catalog/category');
                $this->load->model('catalog/product');


                $categories_id = $this->model_repair_clearing_description->getCategory($product_id);
                $product_info = $this->model_repair_clearing_description->getProduct($product_id);

                $categories = array();
                foreach ($categories_id as $path_id) {
                    $category_info = $this->model_repair_clearing_description->getCategoryInfo($path_id);
                    array_push($categories, $this->translit($category_info['name']));
                }
                
                array_push($categories, 'desc');

                $json['catalog_path'] = implode("/" , $categories);


                $dir = DIR_IMAGE. 'catalog/' . $json['catalog_path']. '/';

                if (!is_dir($dir)) {
                    $json['info']['dir'] = 'create';
                    mkdir($dir, 0777, true);
                }

                $allow_types = array('.jpeg', '.jpg', '.gif', '.png', '.bmp');
                
                $explode = explode('.', $src);
                $type = '.'.end($explode);
                
                if(preg_match('/\?/', $type) != 0){
                    $get = explode('?', $type); 
                    $get = $get[1];
                    $type = str_replace($get, '', $type);
                    $type = str_replace('?', '', $type);
                }
                
                if (!in_array($type, $allow_types)){
                    $json['status'] = 'remove';
                } else {

                    $path = $dir . $this->translit($product_info['model']) . $type;

                    $json['status'] = 'download';

                    if(file_exists($dir)) {

                        $filelist = scandir($dir);
                        $hashes = array();
                        $paths = array();

                        foreach ($filelist as $key => $link) {
                            if(is_dir($dir.$link)){
                                unset($filelist[$key]);
                            } else {
                                $handle = fopen($dir.$filelist[$key], "rb");
                                array_push($paths, $dir.$filelist[$key]);
                                $in_handle = '';

                                while (!feof($handle)) {
                                    $in_handle .= fread($handle, 8192);
                                }

                                array_push($hashes, sha1($in_handle));
                            }
                        }

                        $cache = tmpfile();
                        fwrite($cache, $image);
                        fseek($cache, 0);
                        $in_cache = '';
                        while (!feof($cache)) {
                            $in_cache .= fread($cache, 8192);
                        }

                        $hash_c = sha1($in_cache);

                        $json['hash'] = $hash_c;

                        if (!in_array($hash_c, $hashes)){
                            $json['status'] = 'download';
                            if(count($hashes) == 0){
                                $path = $dir . $this->translit($product_info['model']) . $type;  
                            } else {
                                $path = $dir . $this->translit($product_info['model']) . '-' . count($hashes) . $type;  
                            }
                        } else {
                            $json['status'] = 'exists';
                            $k = array_search($hash_c, $hashes);
                            $path = $paths[$k];  
                        }


                        fclose($cache); 

                        $json['hashes'] = $hashes;
                    }

                    if($json['status'] == 'download'){
                        $fp = fopen($path ,'x');
                        fwrite($fp, $image);
                        fseek($fp, 0);
                        fclose($fp);
                    }
                    $json['path'] = str_replace(DIR_IMAGE, '/image/', $path);
                };
                
                unset($cache);
                unset($hash_c);
                unset($image);
            };
        };

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

        return $json;


    }

    private function translit($s) {
        $s = (string) $s;
        $s = strip_tags($s);
        $s = str_replace(array("\n", "\r"), " ", $s);
        $s = preg_replace("/\s+/", ' ', $s);
        $s = trim($s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
        $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
        $s = str_replace(" ", "-", $s); 
        return $s;
    }
}
