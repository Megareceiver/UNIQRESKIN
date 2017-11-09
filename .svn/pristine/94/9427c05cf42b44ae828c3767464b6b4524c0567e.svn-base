<?php
class Tax_Report_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		global $ci;
		$this->tax_model = $ci->module_model( 'tax' ,'tax',true);

	}

	function items_line_by_taxes($tax_filter=null,$from=null,$to=null){
	    $ci = get_instance();
        $supp_trans_model = $ci->module_model( 'purchases' ,'supplier_trans',true);
        $cus_trans_model = $ci->module_model( 'sales' ,'customer_trans',true);
        $bank_trans_model = $ci->module_model( 'bank' ,'trans',true);


	    $from = date2sql($from);
	    $to = date2sql($to);
	    $tax_all = taxes_items();

	    $tax_items = array();

	    if( is_null($tax_filter) ){
	        $tax_filter = "*";
	    }
	    if( $tax_all && !empty($tax_all) ) foreach ($tax_all AS $k=>$t){

	        if( ($tax_filter != '*' && !in_array($t->id, $tax_filter)) ) continue;



	        $tax_setting = $this->tax_model->get_setting($t->id);

	        if( is_object($tax_setting) )
	        $tax_items[] = (object)array(
	            'id'=>$t->id,
	            'name'=>$t->name,
	            'rate'=>$t->rate,
	            'no'=>$t->no,
	            'gst_03_type'=>$tax_setting->f3_box,
	            'purchasing_gl_code'=>$tax_setting->purchasing_gl_code,
	            'sales_gl_code'=>$tax_setting->sales_gl_code
	        );
	    }

	    $item_count = 0;
	    $item_detail_ids = array();

	    foreach ($tax_items AS $index=>$tax){

	        $tax_items[$index]->items = array();

	        $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $supp_trans_model->gst_grouping( $from,$to,$tax->id ) );
	        $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $supp_trans_model->gst_grouping_from_trans_tax( $from,$to,$tax->id ) );

	        $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $cus_trans_model->gst_grouping( $from,$to,$tax->id ) );
	        $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $cus_trans_model->gst_grouping_from_trans_tax( $from,$to,$tax->id ) );

	        $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $bank_trans_model->gst_grouping( $from,$to,$tax->id ) );

	        if( $tax->id==$_SESSION['SysPrefs']->prefs['baddeb_sale_tax'] ){ //AJS

	            $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $cus_trans_model->gst_grouping_baddebt( $from,$to) );

	        } elseif ( $tax->id==$_SESSION['SysPrefs']->prefs['baddeb_purchase_tax'] ){ //AJP

	            $tax_items[$index]->items = array_merge_recursive($tax_items[$index]->items, $supp_trans_model->gst_grouping_baddebt( $from,$to) );

	        }
	        usort($tax_items[$index]->items, function($a,$b){ return strtotime($a->tran_date)-strtotime($b->tran_date);} );

	        foreach ($tax_items[$index]->items AS $ite){
	            $item_detail_ids[] = $ite->id;
	        }
	    }

	    /*
	     * add item no tax
	     */
	    $tax_items[] = (object)array(
	        'id' => 'null',
	        'rate' => '0',
	        'sales_gl_code' => null,
	        'purchasing_gl_code' => null,
	        'name' => 'Other (no tax input)',
	        'inactive' => 0,
	        'gst_03_type' => 0,
	        'f3_box' => null,
	        'use_for' => null,
	        'amount' => 0,
	        'no'=>null

	    );

	    return $tax_items;
	}
}