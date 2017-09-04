<?php
namespace Common\Lib;
	
class SpExcel {

    //支持导出的excel格式类型, excel5: office2003及以下, excel2007: office2007及以上
    private $allowedExportType = array('excel5', 'excel2007');

    /**
     * @param $file
     * @param $fieldFormat
     * @param $fieldCheck
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function read($file, $fieldFormat, $fieldCheck) {
		require(dirname(__FILE__).'/ExcelClasses/PHPExcel.class.php');
		$extension = substr(strrchr($file, '.'), 1);
		if (file_exists($file)) {
			$reader = $extension == 'xlsx'? \PHPExcel_IOFactory::createReader('Excel2007') : \PHPExcel_IOFactory::createReader('Excel5');
			$phpExcelHandler = $reader->load($file);
			$sheet = $phpExcelHandler->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumm = $sheet->getHighestColumn();
			$highestColumm= \PHPExcel_Cell::columnIndexFromString($highestColumm);//字母列转换为数字列 如:AA变为27
			
			//起始获取行
			$beginIdx = 2;
			$arr = array();
			for($row = $beginIdx; $row <= $highestRow; $row ++){//行数是以第1行开始
			    for($column = 0; $column < $highestColumm; $column++) {//列数是以第0列开始
			        $columnName = \PHPExcel_Cell::stringFromColumnIndex($column);
			        $cell = $sheet->getCellByColumnAndRow($column, $row);
			        $value = (string)$cell->getValue();
			        if($fieldFormat[$column]) $arr[$row - $beginIdx][ $fieldFormat[$column] ] = $value;
			    }
			}
			return $arr;
		}
	}

    /**
     * @param string $data 导出必需基础信息, 包括: 字段匹配(title), 数据(body), 配置(config, 可选)
     * 导出基础信息示例: 
        'title' => array(
            'name' => array(
                'desc'      => '姓名',
                'width'     => 30   //该单元格宽度, 可选, 不设置则自适应
            ),
            'gender' => array(
                'desc'      => '性别'
            ),
            'age' => array(
                'desc'      => '年龄'
            ),
            'hobby' => array(
                'desc'      => '爱好',
                'width'     => 50
            )
        );
        'body' => array(
            array(
                'name'      => 'ltotal',
                'gender'    => 'male',
                'age'       => 39,
                'hobby'     => 'badminton'
            ),
            array(
                'name'      => '凯恩',
                'gender'    => 'male',
                'age'       => 59,
                'hobby'     => 'wwe'
            ),
            ......
        );
     * 'config' => array(
            'width'         => 25,  //全局单元格宽, 可选
            'height'        => 50,  //全局行高, 可选
            'export_type'   => 'excel2007', //导出类型, excel2007之前版本:excel5; excel2007及之后版本:excel2007
        );
     * @param string $title 导出工作表名
     * @param string $fileName 导出文件名
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @author ltotal
     */
    public function write($data = '', $sheetName = '排排网数据记录', $fileName = '排排网导出文件') {
        require(dirname(__FILE__).'/ExcelClasses/PHPExcel.class.php');

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("ltotal")
                    ->setLastModifiedBy("ltotal")
                    ->setTitle("Office 2007 XLSX Document")
                    ->setSubject("Office 2007 XLSX Document")
                    ->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
                    ->setKeywords("office 2007 openxml php")
                    ->setCategory("Export result file");

        extract($data);
        if(!$title || !$body) return false;
        $len = count($title);
        $lastLetter = \PHPExcel_Cell::stringFromColumnIndex($len - 1);

        //全局单元格宽
        if( isset($config['width']) ) {
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth($config['width']);
        }

        //全局行高
        if( isset($config['height']) ) {
            $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($config['height']);
        }

        //判断导出格式
        $exportType = 'excel2007';
        if( isset($config['export_type']) && $config['export_type']) {
            $config['export_type'] = strtolower($config['export_type']);
            if( in_array($config['export_type'], $this->allowedExportType) ) {
                $exportType = $config['export_type'];
            }
        }
        $exportHeader = array();
        if('excel2007' == $exportType) {
            $exportHeader = array(
                'content_type'      => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'content_suffix'    => 'xlsx',
                'content_writer'    => 'Excel2007'
            );
        }else {
            $exportHeader = array(
                'content_type'      => 'application/vnd.ms-excel',
                'content_suffix'    => 'xls',
                'content_writer'    => 'Excel5'
            );
        }

        //设置表头颜色
        $objPHPExcel->getActiveSheet(0)->getStyle("A1:{$lastLetter}1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet(0)->getStyle("A1:{$lastLetter}1")->getFill()->getStartColor()->setARGB('FFCAE8EA');

        //设置边框
        $objPHPExcel->getActiveSheet()->getStyle("A1:{$lastLetter}1")->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $index = 0;
        $titleKeys = array_keys($title);
        foreach($title as $k => $v) {
            $tLetter = \PHPExcel_Cell::stringFromColumnIndex($index);
            if($v['width']) {
                if('auto' == $v['width']) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($tLetter)->setAutoSize(true);
                }else {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($tLetter)->setWidth($v['width']);
                }
            }else if(!$config['width'] && !$config['height']) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($tLetter)->setAutoSize(true);
            }
            //填充表头
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("{$tLetter}1", $v['desc']);
            $index ++;
        }
        
        //从表头下的第2行开始填充内容
        $bodyIndex = 2;
        foreach($body as $k => $v) {
            $index = 0;
            $tLetter = '';
            foreach($titleKeys as $key) {
                $tLetter = \PHPExcel_Cell::stringFromColumnIndex($index);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("{$tLetter}{$bodyIndex}", $v[$key]);
                $index ++;
            }
            //为每行设置边框
            $objPHPExcel->getActiveSheet()->getStyle("A{$bodyIndex}:{$tLetter}{$bodyIndex}")->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $bodyIndex ++;
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle($sheetName);

        header('Content-Type: ' . $exportHeader['content_type']);
        header('Content-Disposition: attachment;filename="'.$fileName.'.'.$exportHeader['content_suffix'].'"');
        header('Cache-Control: max-age=0');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header ('Cache-Control: cache, must-revalidate');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, $exportHeader['content_writer']);
        $objWriter->save('php://output');
        /*
        $filePath = './Public/Export/'.date('Y/m/d');

        if(!is_dir($filePath)) {
            $uploader = new \Common\Lib\Upload;
            $uploader->createDir($filePath);
        }
        $objWriter->save($filePath.'/你好.xlsx');
        return $filePath;
        */
        exit;
	}

    /**
     * @param array $data 生成csv文件的数据源(二维数组)
     * @return bool
     */
    public function writeCsv($data = array(), $fileName = 'file') {
        extract($data);
        if(!$title || !$body) return false;
        
        header('Content-Type:text/html; charset=utf-8');
        header('Content-Type:application/force-download');
        header("content-Disposition:filename=$fileName.csv");

        $titleKeys = array_keys($title);

        foreach($title as $k => $v) {
            $value = iconv('utf-8', 'gbk', $v['desc']);
            echo $value . ',';
        }
        echo "\r\n";

        foreach($body as $k => $v) {
            foreach($titleKeys as $key) {
                $value = iconv('utf-8', 'gbk', $v[$key]);
                echo $value . ',';
            }
            echo "\r\n";
        }
    }

    /**
     * @param $rlt
     * @param $fieldMapping
     * @return mixed
     */
    public function formatter($rlt, $fieldMapping) {
		foreach($rlt as $k => $v) {
			$keys = array_keys($v);
			foreach($keys as $key) {
				if(isset($fieldMapping[$key])) {
					$tmp = $fieldMapping[$key];
					if($tmp['related']) {
						foreach($tmp['map'][$rlt[$k][$tmp['related']]] as $realVal => $val) {
							if($val == trim($v[$key])) {
								$rlt[$k][$key] = $realVal;
							}
						}
					}else {
						foreach($tmp as $realVal => $val) {
							if($val == $v[$key]) {
								$rlt[$k][$key] = $realVal;
							}
						}
					}
				}
			}
		}
		return $rlt;
	}

    /**
     * @param $rlt
     * @param $fieldMapping
     * @return mixed
     */
    public function unFormatter($rlt, $fieldMapping) {
		foreach($rlt as $k => $v) {
			$keys = array_keys($v);
			$keys = array_reverse($keys);
			foreach($keys as $key) {
				if(isset($fieldMapping[$key])) {
					$tmp = $fieldMapping[$key];
					if($tmp['related']) {
						if( isset($tmp['map'][$rlt[$k][$tmp['related']]][$v[$key]]) ) {
							$rlt[$k][$key] = $tmp['map'][$rlt[$k][$tmp['related']]][$v[$key]];
						}
					}else {
						if( isset($tmp[$v[$key]]) ) {
							$rlt[$k][$key] = $tmp[$v[$key]];
						}
					}
				}
			}
		}
		return $rlt;
	}
}