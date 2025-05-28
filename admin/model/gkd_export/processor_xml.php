<?php
class ModelGkdExportProcessorXml extends Model {
  
  public function getFile($file, $create = false) {
    if ($create) {
      $fh = fopen($file, 'w');
    } else {
      $fh = fopen($file, 'a');
    }
    
    return $fh;
  }
  
  public function closeFile($fh) {
    fclose($fh);
  }
  
  public function getTotalItems($config) {
    return $this->{'model_gkd_export_driver_'.$config['export_type']}->getTotalItems($config);
  }
  
  public function writeHeader($fh, $config) {
    fwrite($fh, '<?xml version="1.0"?>'."\n".
                '<itemlist>'."\n".
                '<title>XML Export - '.date($this->language->get('datetime_format')).'</title>');
  }
  
  public function writeBody($fh, $config) {
    $products = $this->{'model_gkd_export_driver_'.$config['export_type']}->getItems($config, false, true);

    $row = 0;
    
    foreach ($products as $product) {
      $output = "\n".'<item>';
      
      foreach ($product as $k => $v) {
        if ($v !== '') {
          if (in_array($k, array('additional_images', 'product_filter', 'product_attribute', 'product_category', 'product_discount', 'product_special', 'product_option', 'related_product_id', 'related_product_name', 'store'))) {
            switch ($k) {
              case 'additional_images': $subnode = 'image'; break;
              case 'product_filter': $subnode = 'filter'; break;
              case 'product_attribute': $subnode = 'attribute'; break;
              case 'product_category': $subnode = 'category'; break;
              case 'product_discount': $subnode = 'discount'; break;
              case 'product_special': $subnode = 'special'; break;
              case 'product_option': $subnode = 'option'; break;
              case 'related_product_id': $subnode = 'id'; break;
              case 'related_product_name': $subnode = 'name'; break;
              case 'store': $subnode = 'store'; $k = 'stores'; break;
            }
            
            if (is_array($v)) {
              $output .= "\n\t".'<'.$k.'>';
              
              foreach ($v as $arrKey => $arrValues) {
                if (is_array($arrValues)) {
                  if ($k == 'product_discount_') {
                    $subnode = str_replace(' ', '_', $arrKey);
                  }
                  
                  $output .= "\n\t\t".'<'.$subnode.'>';
                  
                  foreach ($arrValues as $subKey => $subVal) {
                    if (is_scalar($subVal)) {
                      $output .= "\n\t\t\t".'<'.$subKey.'>'.str_replace('&', '&amp;', html_entity_decode($subVal, ENT_QUOTES, 'UTF-8')).'</'.$subKey.'>';
                    }
                  }
                  
                  $output .= "\n\t\t".'</'.$subnode.'>';
                } else if (is_scalar($arrValues)) {
                  if (in_array($k, array('product_category'))) {
                    $output .= "\n\t\t".'<'.$subnode.' id="'.(int) $arrKey.'">'.str_replace('&', '&amp;', html_entity_decode($arrValues, ENT_QUOTES, 'UTF-8')).'</'.$subnode.'>';
                  } else {
                    $output .= "\n\t\t".'<'.$subnode.'>'.str_replace('&', '&amp;', html_entity_decode($arrValues, ENT_QUOTES, 'UTF-8')).'</'.$subnode.'>';
                  }
                }
              }
              
              $output .= "\n\t".'</'.$k.'>';
            } else {
              $values = explode('|', $v);
              $output .= "\n\t".'<'.$k.'>';
              foreach ($values as $value) {
                $output .= "\n\t\t".'<'.$subnode.'>'.str_replace(array('&', '<![CDATA[', ']]>'), array('&amp;', '', ''), html_entity_decode($value, ENT_QUOTES, 'UTF-8')).'</'.$subnode.'>';
              }
              $output .= "\n\t".'</'.$k.'>';
            }
          } else {
            $v = html_entity_decode($v, ENT_QUOTES, 'UTF-8');
            
            if (strpos($v, '<') !== false || strpos($v, '&') !== false || strpos($v, '<![CDATA[') !== false || strpos($v, ']]>') !== false) {
              $output .= "\n\t".'<'.$k.'><![CDATA['.str_replace(array('&', '<![CDATA[', ']]>'), array('&amp;', '', ''), $v).']]></'.$k.'>';
            } else if (strpos($v, '<') !== false || strpos($v, '&') !== false) {
              $output .= "\n\t".'<'.$k.'><![CDATA['.str_replace('&', '&amp;', $v).']]></'.$k.'>';
            } else {
              $output .= "\n\t".'<'.$k.'>'.str_replace('&', '&amp;', $v).'</'.$k.'>';
            }
          //$output .= '<'.$k.'><![CDATA[' . htmlentities($v, ENT_QUOTES, 'UTF-8', 0) . ']]></'.$k.'>';
          }
        } else {
          $output .= "\n\t".'<'.$k.'/>';
        }
      }
      
      $output .= "\n".'</item>';
      
      fwrite($fh, $output);
      
      $row++;
    }
    
    // return false when no more products
    return count($products);
  }
  
  public function writeFooter($fh) {
    fwrite($fh, '</itemlist>');
  }
  
}