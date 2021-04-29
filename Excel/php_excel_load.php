<?php
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

function retrieve_datas($image_path){
  $return_array = array();
  $objPHPExcel = PHPExcel_IOFactory::load($image_path);
  foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    $worksheetTitle     = $worksheet->getTitle();
    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $nrColumns = ord($highestColumn) - 64;
    $return_array['worksheet_title'] = $worksheetTitle;
    $return_array['cols_count'] = $nrColumns;
    $return_array['row_max'] = $highestRow;
    $return_array['col_max'] = $highestColumn;
    $i = 0;
    for ($row = 1; $row <= $highestRow; ++ $row) {
      for ($col = 0; $col < $highestColumnIndex; ++ $col) {
        $cell = $worksheet->getCellByColumnAndRow($col, $row);
        $val = $cell->getValue();
        $dataType = PHPExcel_Cell_DataType::dataTypeForValue($val);
        if($row == 1){
          $return_array['head'][] = $val;
        }
        if($i != 0){
          $return_array['data'][$row][] = $val;
          $return_array['row_number'][$i][] = $row;
        }
        
        $return_array['data_type'][$i][] = $dataType;
      }
      $i++;
    }
  }
  $return_array['head'] = array_filter($return_array['head']);
  return $return_array;

}

function retrieve_images($image_path,$file_name,$path=''){
  if($path != '') $path = $path . '/';
  $objPHPExcel = PHPExcel_IOFactory::load($image_path);
  $i = 0;
  $count = 1;
  $objWorksheet = $objPHPExcel->getActiveSheet();
  $image_lists = array();
  foreach ($objWorksheet->getDrawingCollection() as $drawing) {
    $imageContents = '';
    $string = $drawing->getCoordinates();
    $coordinate = PHPExcel_Cell::coordinateFromString($string);
    $row_count = $coordinate[1];
    if ($drawing instanceof PHPExcel_Worksheet_MemoryDrawing) {
      ob_start();
      call_user_func(
          $drawing->getRenderingFunction(),
          $drawing->getImageResource()
      );
      $imageContents = ob_get_contents();
      ob_end_clean();
      switch ($drawing->getMimeType()) {
        case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_PNG :
                $extension = 'png'; break;
        case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_GIF:
                $extension = 'gif'; break;
        case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_JPEG :
                $extension = 'jpg'; break;
      }
    } 
    else {
      $zipReader = fopen($drawing->getPath(),'r');
      $imageContents = '';
      while (!feof($zipReader)) {
          $imageContents .= fread($zipReader,1024);
      }
      fclose($zipReader);
      $extension = $drawing->getExtension();
    }
    if(strlen(trim($imageContents)) > 0){
      $file_name_full =  $file_name . '_' .$row_count. '.'.$extension;
      $image_lists['full_path'][$row_count] = $path . $file_name_full;
      $image_lists['image_name'][$row_count] = $file_name_full;
      $myFileName = $path . $file_name_full;
      // echo '<img src="'.$myFileName.'">';
      file_put_contents($myFileName,$imageContents);
    }
    else{
      $image_lists['full_path'][] = '';
      $image_lists['image_name'][] = '';
    }
    $count++;
  }

  return $image_lists;

}

function retrieve_images_with_id($image_path,$file_name,$external_data_names,$path=''){
  if($path != '' && substr($path, -1) != '/') $path = $path . '/';
  $objPHPExcel = PHPExcel_IOFactory::load($image_path);
  $i = 0;
  $count = 1;
  $objWorksheet = $objPHPExcel->getActiveSheet();
  $image_lists = array();
  $pre_file_name = $file_name;
  foreach ($objWorksheet->getDrawingCollection() as $drawing) {
    $imageContents = '';
    $string = $drawing->getCoordinates();
    $coordinate = PHPExcel_Cell::coordinateFromString($string);
    $row_count = $coordinate[1];
    $file_name = $pre_file_name . '_' . $external_data_names[$row_count];
    if ($drawing instanceof PHPExcel_Worksheet_MemoryDrawing) {
      ob_start();
      call_user_func(
          $drawing->getRenderingFunction(),
          $drawing->getImageResource()
      );
      $imageContents = ob_get_contents();
      ob_end_clean();
      switch ($drawing->getMimeType()) {
        case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_PNG :
                $extension = 'png'; break;
        case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_GIF:
                $extension = 'gif'; break;
        case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_JPEG :
                $extension = 'jpg'; break;
      }
    } 
    else {
      $zipReader = fopen($drawing->getPath(),'r');
      while (!feof($zipReader)) {
          $imageContents .= fread($zipReader,1024);
      }
      fclose($zipReader);
      $extension = $drawing->getExtension();
    }
    
    if(strlen(trim($imageContents)) > 0){

      $image_lists['full_path'][$row_count] = $path . $file_name .'.'.$extension;
      $image_lists['image_name'][$row_count] = $file_name .'.'.$extension;
      $myFileName = $path . $file_name .'.'.$extension;
      file_put_contents($myFileName,$imageContents);
      // echo '<img src="'.$myFileName.'">';
    }
    $count++;
    $i++;
  }

  return $image_lists;

}
