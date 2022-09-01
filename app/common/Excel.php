<?php

namespace app\common;

use PHPExcel_IOFactory;
use PHPExcel;

class Excel{
    private $name = null;
    private $excel = null;
    private $index = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','S','Y','Z'];
    public function __construct($name) {
        $this->name = $name;
        $this->excel = new \PHPExcel();
        iconv('UTF-8', 'gb2312', $this->name); //针对中文名转码
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle($this->name); //设置表名
        $this->excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);
    }

    public function moneyScheduleExcel($data, $field, $offest = 2) {
        $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $this->excel->getActiveSheet()->setCellValue('A1', '发布日期');
        $this->excel->getActiveSheet()->setCellValue('B1', '博主');
        $this->excel->getActiveSheet()->setCellValue('C1', '平台');
        $this->excel->getActiveSheet()->setCellValue('D1', '品牌');
        $this->excel->getActiveSheet()->setCellValue('E1', '价格');
        $this->excel->getActiveSheet()->setCellValue('E2', '成交价');
        $this->excel->getActiveSheet()->setCellValue('F2', '代下单星图价');
        $this->excel->getActiveSheet()->setCellValue('G2', '折扣力度');
        $this->excel->getActiveSheet()->setCellValue('H2', '刊例价');
        $this->excel->getActiveSheet()->setCellValue('I1', '收款账户');
        $this->excel->getActiveSheet()->setCellValue('J1', '收款时间');
        $this->excel->getActiveSheet()->setCellValue('K1', '备注');
        $this->excel->getActiveSheet()->setCellValue('L1', '接单人员');
        $this->excel->getActiveSheet()->mergeCells('A1:A2');
        $this->excel->getActiveSheet()->mergeCells('B1:B2');
        $this->excel->getActiveSheet()->mergeCells('C1:C2');
        $this->excel->getActiveSheet()->mergeCells('D1:D2');
        $this->excel->getActiveSheet()->mergeCells('E1:H1');
        $this->excel->getActiveSheet()->mergeCells('I1:I2');
        $this->excel->getActiveSheet()->mergeCells('J1:J2');
        $this->excel->getActiveSheet()->mergeCells('K1:K2');
        $this->excel->getActiveSheet()->mergeCells('L1:L2');
        $this->excel->getActiveSheet()->getProtection()->setSheet(true);
        $this->setExcelData($data, $field, $offest);
        $this->excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->name . '.xls"');
        header('Cache-Control: max-age=0');
        $res_excel = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $res_excel->save('php://output');
    }

    public function setExcelData($data, $field, $offest) {
        $count = count($data);
        if ($count > 0) {
            foreach ($data as $key => $val) {
                foreach ($field as $com => $index) {
                    $this->excel->getActiveSheet()->setCellValue($this->index[$com] . (string)($offest+$key), $val[$index]);
                }

            }
        }
    }

    public function setExcelAttribute($field) {
        $i = 0;
        foreach ($field as $key => $column) {
            $index = $this->index[$i];
            $this->excel->getActiveSheet()->getColumnDimension($index)->setWidth(15);
            $this->excel->getActiveSheet()->setCellValue($index . '1', $column);
            $i++;
        }
    }

    public function writeExcel($data, $field) {
        $this->setExcelAttribute($field);
        $count = count($data);
        if ($count > 0) {
            foreach ($data as $key => $val) {
                $i = 0;
                foreach ($field as $com => $index) {
                    $this->excel->getActiveSheet()->setCellValue($this->index[$i] . (string)(2+$key), $val[$com]);
                    $i++;
                }
            }
        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->name . '.xls"');
        header('Cache-Control: max-age=0');
        $res_excel = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $res_excel->save('php://output');
    }


}