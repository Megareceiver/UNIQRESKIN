<?php
class inputtaxes {
	function input($name,$val,$group=0,$input_return_type='html',$readonly=false,$ids_filter=null,$showname='code'){
		global $Ajax, $ci;

		if ( $input_return_type=='inputtitle') {
			//$tax = $ci->db->select('id, name AS title')->where('id',$val)->get('tax_types')->row();

			$tax = get_gst($val);

			$out = $ci->finput->hidden($name, isset($tax->id)? $tax->id : 0);
			$out.= '<span class="'.$name.'_title" >'.( isset($tax->name) ? $tax->name." (".$tax->rate."%)".$tax->no : null ).'</span>';
			return $out;
		} else if ( $input_return_type =='in_row_title' ) {
		    $title = null;
		    //$tax = $ci->db->select('id, name AS title, rate')->where('id',$val)->get('tax_types')->row();
		    $tax = get_gst($val);
		    if( $tax && isset($tax->name) ){
		        $tax_name_new = strstr($tax->name,"(");
		        $title = str_replace(array("(",")"),"",$tax_name_new);
		        $title .="(".$tax->rate."%)";
		    }
		    return $title;
		}


		$onchange = null;
		$group_tax = null;

		if( $group ){
		    if( !is_array($group) ){
		        $group_tax = str_replace(',','+', $group);
		    } elseif ( is_array($group) ){
		        $group_tax = implode("+",$group);
		    }
		    $group_tax = str_replace('1','2,3', $group_tax);

		}

		if( $showname=='fullname' ) {

		    if( !isset($_SESSION['taxcode'][$group_tax]) ){
		        $item_api = $ci->api_membership->get_data('taxes/'.$group_tax);
		        $_SESSION['taxcode'][$group_tax] = (array)$item_api->options;
		    }
		} else {
		    if( !isset($_SESSION['taxcode'][$group_tax]) ){
		        $item_api = $ci->api_membership->get_data('taxcode/'.$group_tax);
		        $_SESSION['taxcode'][$group_tax] = (array)$item_api->options;
		    }

		}

        $items = $_SESSION['taxcode'][$group_tax];

		if( !$val ){
			$val = $ci->input->post($name);
		}

		if( !empty($items) && $val=='' && isset($items[0]) ){

			$val = $_POST[$name] = $items[0]->id;
		}

		if( $input_return_type == 'value'){
			return $val;
		}

		//if( !Class_exists('Sale_Cart') ){
// 		    $cart = load_class('Cart', 'sales/libraries','Sale_');
		    require_once MODULEPATH.'sales/libraries/cart.php';
// 		}

		if ( isset($_SESSION['Items']) && isset($_SESSION['Items']->trans_type) ){

		    if( in_array($_SESSION['Items']->trans_type, array(ST_SALESORDER,ST_SALESQUOTE,ST_CUSTDELIVERY,ST_SALESINVOICE,ST_CUSTCREDIT)) && strtotime($_SESSION['SysPrefs']->prefs['gst_start_date']) < strtotime(Date('d-m-Y'))){
		        $readonly = true;
		    }
		} elseif ( isset($_SESSION['PO']) && isset($_SESSION['PO']->trans_type) ) {
		    if( in_array($_SESSION['PO']->trans_type, array(ST_PURCHORDER,ST_SUPPRECEIVE,ST_SUPPINVOICE)) && strtotime($_SESSION['SysPrefs']->prefs['gst_start_date']) < strtotime(Date('d-m-Y'))){
		        $readonly = true;
		    }
		} elseif( isset($_SESSION['supp_trans']) && isset($_SESSION['supp_trans']->trans_type) ) {
		    if( in_array($_SESSION['supp_trans']->trans_type, array(ST_SUPPCREDIT,ST_SUPPINVOICE)) && strtotime($_SESSION['SysPrefs']->prefs['gst_start_date']) < strtotime(Date('d-m-Y'))){
		        $readonly = true;
		    }
		} elseif( isset($_SESSION['pay_items']) && isset($_SESSION['pay_items']->trans_type)){
		    if( in_array($_SESSION['pay_items']->trans_type, array(ST_BANKPAYMENT,ST_BANKDEPOSIT)) && strtotime($_SESSION['SysPrefs']->prefs['gst_start_date']) < strtotime(Date('d-m-Y'))){
		        $readonly = true;
		    }
		}

		/*
		 * remove Kastam 150921
		 */
		/*
		if( $readonly && isset($_SESSION['SysPrefs']->prefs['gst_default_code'])){
		    $items = array();
		    $tax_default = $ci->api_membership->get_data('taxdetail/'.$_SESSION['SysPrefs']->prefs['gst_default_code'] );
		    $items[] = (object)array('id'=>$tax_default->id,'title'=>$tax_default->no .'('.$tax_default->rate.'%)');
		    $readonly = false;
		    $val = $tax_default->id;

		}

		*/
		$ids_filter = null;
		$readonly = false;


        if( is_array($ids_filter) && count($ids_filter) > 0){
            $options= array();
            foreach ($items AS $k=>$item_val){
                if( in_array($item_val->id, $ids_filter) ){
                    $options[] =$item_val;
                }
            }


            return $ci->finput->options( $name,$options,$val,null, "combo2" ,'off',false,$readonly);
        } else {

            return $ci->finput->options( $name,$items,$val,'--Select GST--', "combo2" ,'off',false,$readonly);
        }

	}

}