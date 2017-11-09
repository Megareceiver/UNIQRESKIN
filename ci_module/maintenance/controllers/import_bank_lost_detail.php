<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// import_bank_lost_detail
class MaintenanceImportBankLostDetail {
    function __construct() {

    }

    function index(){
        $file = "GLAccountTransactions All GL (no output input tax).xls";
        $type_check = input_get('type');
        if( $type_check== ST_BANKDEPOSIT){
            $this->bank_deposit($file,$type_check);
        } elseif( $type_check== ST_BANKPAYMENT) {
            $this->bank_payment($file,$type_check);
        }

    }

    private function load_sheet($file=NULL,$sheetActive=0){
        $file_dir = realpath(ROOT."/company/import/")."/";

        if( !$file OR !file_exists($file = $file_dir.$file))
            return false;
        $foundInCells = array();

        $excel = get_instance()->load_library('phpexcel',true);
        $objectReader = PHPExcel_IOFactory::createReader('Excel5');
        $objectReader->setReadDataOnly(true);

        $objPHPExcel = $objectReader->load($file);


        //         $objWorksheet = $objPHPExcel->getActiveSheet();
        $objWorksheet = $objPHPExcel->setActiveSheetIndex($sheetActive);
        return $objWorksheet;

    }

    private function bank_deposit($file=NULL,$type=ST_BANKDEPOSIT){
        $db = get_instance()->db;
        $detail_count = 'SELECT COUNT(d.id) FROM bank_trans_detail AS d WHERE d.type = tran.type AND d.trans_no = tran.trans_no';

        $db->select("tran.type, tran.trans_no AS tran_no, tran.amount, tran.ref, ($detail_count) AS detail_count",false)->from('bank_trans AS tran');
        $db->where("($detail_count) < 1");
        $db->where('tran.type',$type);
        $data = $db->get();

        $trans_import = array();
        if( $data->num_rows() > 0 ) foreach ($data->result() AS $row){
            $trans_import[$row->tran_no] = array('amount_db'=>$row->amount,'amount_import'=>0,'ref'=>trim($row->ref),'detail'=>array());
        }

        $objWorksheet = $this->load_sheet($file,2);
        $row_index = $objWorksheet->getHighestRow();

        //$tran_import = array();
        for($row =2; $row <= $row_index; $row++){
            $gl_acc = $objWorksheet->getCell("A$row")->getValue();
            $tran_no = $objWorksheet->getCell("B$row")->getValue();

            $ref = $objWorksheet->getCell("C$row")->getValue();
            $tran_no = intval($tran_no);
            if( array_key_exists($tran_no, $trans_import) AND trim($ref)==$trans_import[$tran_no]['ref'] ){

                $trans_import[$tran_no]['detail'][] = array(
                    'trans_no'=>$tran_no,
                    'account_code'=>$gl_acc,

                    'amount'=>floatval($objWorksheet->getCell("E$row")->getValue())

                );
                $trans_import[$tran_no]['amount_import'] +=floatval($objWorksheet->getCell("E$row")->getValue());
            }
        }

        if( count($trans_import) >0 AND input_get('import') ) foreach ($trans_import AS $tran_no =>$transaction){

            if( count($transaction['detail']) >0 ) foreach ($transaction['detail'] AS $tran_detail){

                $db->insert('bank_trans_detail',array(
                        'type'=>$type,
                        'account_code'=>$tran_detail['account_code'],
                        'amount'=>$tran_detail['amount'],
                        'trans_no'=>$tran_no,

                ));

            }

        }

        bug($trans_import);die('aaa');
    }


    private function bank_payment($file=NULL,$type=ST_BANKPAYMENT){
        $db = get_instance()->db;
        $detail_count = 'SELECT COUNT(d.id) FROM bank_trans_detail AS d WHERE d.type = tran.type AND d.trans_no = tran.trans_no';

        $db->select("tran.type, tran.trans_no AS tran_no, tran.amount, tran.ref, ($detail_count) AS detail_count",false)->from('bank_trans AS tran');
        $db->where("($detail_count) < 1");
        $db->where('tran.type',$type);
        $data = $db->get();

        $trans_import = array();
        if( $data->num_rows() > 0 ) foreach ($data->result() AS $row){
            $trans_import[$row->tran_no] = array('amount_db'=>$row->amount,'amount_import'=>0,'ref'=>trim($row->ref),'detail'=>array());
        }

        $objWorksheet = $this->load_sheet($file,1);
        $row_index = $objWorksheet->getHighestRow();

        //$tran_import = array();
        for($row =2; $row <= $row_index; $row++){
            $gl_acc = $objWorksheet->getCell("A$row")->getValue();
            $tran_no = $objWorksheet->getCell("B$row")->getValue();

            $ref = $objWorksheet->getCell("C$row")->getValue();
            $tran_no = intval($tran_no);
            if( array_key_exists($tran_no, $trans_import) AND trim($ref)==$trans_import[$tran_no]['ref'] ){

                $trans_import[$tran_no]['detail'][] = array(
                    'trans_no'=>$tran_no,
                    'account_code'=>$gl_acc,

                    'amount'=>floatval($objWorksheet->getCell("E$row")->getValue())

                );
                $trans_import[$tran_no]['amount_import'] +=floatval($objWorksheet->getCell("E$row")->getValue());
            }
        }

        if( count($trans_import) >0 AND input_get('import') ) foreach ($trans_import AS $tran_no =>$transaction){

            if( count($transaction['detail']) >0 ) foreach ($transaction['detail'] AS $tran_detail){

                $db->insert('bank_trans_detail',array(
                    'type'=>$type,
                    'account_code'=>$tran_detail['account_code'],
                    'amount'=>$tran_detail['amount'],
                    'trans_no'=>$tran_no,

                ));

            }

        }

        bug($trans_import);die('aaa');
    }

}
