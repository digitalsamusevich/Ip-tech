<?php
class ControllerRepairImages extends Controller {
	public function index() {   
        $this->response->setOutput($this->load->view('repair/images'));
    }
    
    
    public function getImages() {
        
        $page = $this->request->post['page'];
        

        if($page != 0){
            $limit_from = ($page - 1) * 50;
            $limit = 50;
        } else {
            $limit_from = 0;
            $limit = 0;
        }
        
        $json['page'] = $page;
        $json['limit_from'] = $limit_from;
        $json['limit_to'] = $limit;

        
        $this->load->model('repair/images');
        $results = $this->model_repair_images->getImages($limit_from, $limit);
        
    
        $count = 0;
        foreach ($results as $key => $result) {
            $json['product_id'][] = $result['product_id'];
            $json['image'][] = $result['image'];
            $json['image2'][] = $result['image2'];
            $json['image3'][] = $result['image3'];
            
            if(isset($result['image'])){
                $count += count($result['image']);

                if(isset($result['image2'])){
                    $count += count($result['image2']);
                }

                if(isset($result['image3'])){
                    $count += count($result['image3']);
                }
            }
        }

        $json['count'] = $count;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    
    public function getPicturesInDesc() {
        $page = $this->request->post['page'];

        if($page != 0){
            $limit_from = ($page - 1) * 1500;
            $limit = $page * 1500;
        } else {
            $limit_from = 0;
            $limit = 0;
        }

        $json = array();
        
        $this->load->model('repair/images');
        $results = $this->model_repair_images->getPicturesInDesc($limit_from, $limit);
        

        foreach ($results as $key => $result) {
            if(preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i', html_entity_decode($result['description']), $img, PREG_SET_ORDER)){
                    for($i=0; $i < count($img); $i++){
                        $json['product_id'][] = $result['product_id'];
                        $json['picture'][] = $img[$i][1];
                    }
                }
        } 
                
        $json['count'] = count($json['picture']);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        
    }
    
    public function repairInDesc() {
        
    }
    
    public function findImgBadQuality() {
       
    }
    
    public function findImgByBigSize() {
        
    }
    
    public function optimize() {

    }
    
    public function showAllImg() {

    }
    
    public function showImg() {

    }
    
    public function download() {
        
        $product_id = $this->request->post['product_id'];
        $url = $this->request->post['url'];
            
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        
        $categories_id = $this->model_catalog_product->getCategory($product_id);
        $product_info = $this->model_catalog_product->getProduct($product_id);
        
        $categories = array();
        foreach ($categories_id as $path_id) {
            $category_info = $this->model_catalog_category->getCategory($path_id);
            array_push($categories, $this->translit($category_info['name']));
        }
        
        $json['catalog_path'] = implode("/" , $categories);
        
        
        $dir = DIR_IMAGE. 'catalog/' . $json['catalog_path']. '/';
        
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    
        
        $type = substr($url, -4);
        
        $path = $dir . $product_info['model'] . $type;
        
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
        $image = curl_exec($curl);
        curl_close($curl);
        
        
        if (file_exists($path)) {
            $i = 0;
            $filelist = scandir($dir);
            $hashes = array();
        
            foreach ($filelist as $key => $link) {
                if(is_dir($dir.$link)){
                    unset($filelist[$key]);
                } else {
                    array_push($hashes, md5(file_get_contents($dir.$filelist[$key])));
                }
            }
            
            $cache_dir = DIR_IMAGE. 'catalog/cache_/';

            if (!file_exists($cache_dir)) {
                mkdir($cache_dir, 0777, true);
            }

            $cache = $cache_dir.md5(rand()).$type;

            $fp = fopen($cache, 'x');
            fwrite($fp, $image);
            fclose($fp);

            if (!in_array(md5(file_get_contents($cache)), $hashes)){
                $path = $dir . $product_info['model'] . '-' . count($hashes) . $type;
                $fp = fopen($path ,'x');
                fwrite($fp, $image);
                fclose($fp);
            }
            
            $json['hash'] = $hashes;

        } else {
            $fp = fopen($path,'x');
            fwrite($fp, $image);
            fclose($fp);
        }
        
        $json['path'] = str_replace(DIR_IMAGE, '', $path);
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }
    
    public function translit($s) {
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
