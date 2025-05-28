<?php
class ModelGkdExportProcessorPhpexcel extends Model {
  
  public function __construct($registry) {
		parent::__construct($registry);
    require_once(DIR_SYSTEM.'library/PHPExcel/PHPExcel.php');
  }
  
  public function getFile($file, $create = false) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
      PHPExcel_Settings::setPdfRenderer('mPDF', DIR_SYSTEM . 'library/mpdf/');
    }
    
    return $file;
  }
  
  public function closeFile($file) {}
  
  public function getTotalItems($config) {
    return $this->{'model_gkd_export_driver_'.$config['export_type']}->getTotalItems($config);
  }
  
  public function writeHeader($file, $config) {
    $PHPExcel = new PHPExcel();
    $PHPExcel->getProperties()->setCreator('Universal Import/Export Pro');
    $sheet = $PHPExcel->getActiveSheet();

    $sheet->setTitle(ucfirst($config['export_type']));

    $config['start'] = 0;
    $config['limit'] = 1;
    
    $columns = $this->{'model_gkd_export_driver_'.$config['export_type']}->getItems($config);
    
    $col = 0;
    
    if (isset($columns[0])) {
      foreach(array_keys($columns[0]) as $val) {
        $sheet->setCellValueByColumnAndRow($col++, 1, $val);
      }
    }
    
    // set headers to bold
    if ($columns && in_array(pathinfo($file, PATHINFO_EXTENSION), array('xls', 'xlsx'))) {
      $PHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($columns[0])).'1')->getFont()->setBold(true);
    }
    
    switch (pathinfo($file, PATHINFO_EXTENSION)) {
      case 'csv' : $filetype = 'CSV'; break;
      case 'xls' : $filetype = 'Excel5'; break;
      case 'xlsx' : $filetype = 'Excel2007'; break;
      case 'ods' : $filetype = 'OpenDocument'; break;
      case 'pdf' : $filetype = 'PDF'; break;
      case 'html' : $filetype = 'HTML'; break;
    }
    
    $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, $filetype);
    $objWriter->save($file);
  }
  
  public function writeBody($file, $config) {
    $items = $this->{'model_gkd_export_driver_'.$config['export_type']}->getItems($config);
    
    switch (pathinfo($file, PATHINFO_EXTENSION)) {
      case 'csv' : $filetype = 'CSV'; break;
      case 'xls' : $filetype = 'Excel5'; break;
      case 'xlsx' : $filetype = 'Excel2007'; break;
      case 'ods' : $filetype = 'OpenDocument'; break;
      case 'pdf' : $filetype = 'PDF'; break;
      case 'html' : $filetype = 'HTML'; break;
    }
    
    $PHPExcel = PHPExcel_IOFactory::load($file);
    $sheet = $PHPExcel->getActiveSheet();
    
    $row = $PHPExcel->getActiveSheet(0)->getHighestRow() + 1;
    //$row = $config['start'] + 2;
    
    foreach ($items as $item) {
      $col = 0;
      
      foreach($item as $val) {
        //$sheet->setCellValueByColumnAndRow($col++, $row, html_entity_decode($val, ENT_QUOTES));
        $sheet->setCellValueByColumnAndRowExplicit($col++, $row, html_entity_decode($val, ENT_QUOTES, 'UTF-8')); // fix big numbers scientific notation issue
      }
      
      $row++;
    }
    
    $xlsWriter = PHPExcel_IOFactory::createWriter($PHPExcel, $filetype);
    $xlsWriter->save($file);
    
    // return false when no more items
    return count($items);
  }
  
  public function writeFooter($fh) {}
}