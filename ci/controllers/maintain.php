<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Maintain extends ci {
	function __construct() {
	    global $ci;
	    $this->ci = $ci;
	    $this->db = $ci->db;
	}

	function index(){
	    page(_('System Maintain'));


        self::sale_credit_note_fix_0730();

        end_page();
	}

	function check_cheque($table){
	    die('checl');
        if($table){
            if ( !$this->db->field_exists('cheque', $table)){
                $this->db->query("ALTER TABLE `$table` ADD `cheque` varchar(50) NULL; ");
                bug( $this->db->last_query()); die;
            }
        }
	}

	private function sale_credit_note_fix_0730(){
		// TODO: fix credit note removed items

	    $select = 'tran.*, (select count(*) from debtor_trans_details where debtor_trans_no = tran.trans_no AND debtor_trans_type = tran.type) AS item_total ';
        $this->db->where(array('tran.type'=>11));
//         $this->db->where(array('tran.tran_date'=>'2015-07-24'));



        $trans = $this->db->select($select)->get('debtor_trans AS tran')->result();
        foreach ($trans AS $tran){
            if($tran->item_total <1){
//                 bug($tran);

                $order_items = $this->db->where(array('order_no'=>$tran->order_,'trans_type'=>ST_SALESORDER))->get('sales_order_details')->result();

                if( count($order_items) > 0 ) { foreach ($order_items AS $item){
                    $rever_credit_note = array(
                        'debtor_trans_no'=>$tran->trans_no,
                        'debtor_trans_type'=>ST_CUSTCREDIT,
                        'stock_id'=>$item->stk_code,
                        'description'=>$item->description,
                        'unit_price'=>$item->unit_price,
                        'discount_percent'=>$item->discount_percent,
                        'tax_type_id'=>$item->tax_type_id,
                        'quantity'=>$item->quantity
                    );

                    $this->db->insert('debtor_trans_details',$rever_credit_note);
//                     bug($rever_credit_note);
                }}
//                 die('end');
//                 bug( $this->db->last_query() );
//                 bug($order_items);die;
            }
        }
	}

}