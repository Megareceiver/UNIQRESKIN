<?php
class form extends ci {

	function __construct(){

	}

	static function anchor($template){
	    $uri = ( isset($template['uri']) )?$template['uri']:null;
	    $title = ( isset($template['title']) )?$template['title']:null;
	    $newpage = ( isset($template['newpage']) )?$template['newpage']:FALSE;

	    $attr = null;
	    if( $newpage ) {
	        $attr = ' target="_blank" onclick="javascript:openWindow(this.href,this.target); return false;" ';
	    }

	    return '<a href="'.site_url($uri).'" '.$attr.' >'.$title.'</a>';
	}

	static function formInputGroup($template=null, $params=null){
		$title = ( isset($template['title']) )?$template['title']:null;
		$colunm = ( isset($template['colunm']) )?$template['colunm']:'3-9';

		$colunm_per = explode('-', $colunm);
		if( isset($colunm_per[1]) && is_numeric($colunm_per[1]) && $colunm_per[1] < 12 ){
			$right = $colunm_per[1];
		} else {
			$right = 9;
		}
		$left = 12 - $right;

		$field = ( isset($template['field']) )?$template['field']:null;

        if ( empty($field) && !empty($params->tpl_vars) AND array_key_exists($template['name'], $params->tpl_vars)){
//             bug($params->tpl_vars);die;
            $field = $params->tpl_vars[$template['name']]->value;
        }

		if ( $field ){


		    if( !$title && isset($field['title']) ){
		        $title = $field['title'];
		    }
		    if( !isset($template['name']) && isset($field['type']) ){
		        $template['name'] = $field['type'];
		    }
		    if( !isset($template['type']) && isset($field['type']) ){
		        $template['type'] = $field['type'];
		    }

		    if( !$title ){
		        $title = ucfirst($template['name']);
		    }
		}

		if( strlen($title) > 1 ){
		    $html = form_group_bootstrap($title, self::formInput($template,$params));
// 		    $html = '<div class="form-group clearfix">
//                 <div class="col-sm-'.$left.'"><label for="">'.$title.'</label></div>
//     				<div class="col-sm-'.$right.'">'.self::formInput($template,$params).'</div>
//     		</div>';
		} else {
		    $html = '<div class="form-group">'.self::formInput($template,$params)."</div>";
		}

		return $html;
	}

	static function inputRow($template=null, $params=null){
	    $title = ( isset($template['title']) )?$template['title']:null;
	    $field = ( isset($template['field']) )?$template['field']:null;

	    if ( $field ){
	        if( !$title && isset($field['title']) ){
	            $title = $field['title'];
	        }
	        if( !isset($template['name']) && isset($field['type']) ){
	            $template['name'] = $field['type'];
	        }
	        if( !isset($template['type']) && isset($field['type']) ){
	            $template['type'] = $field['type'];
	        }
	    }




	    $html = '<label for="aaa">'.$title.'</label>'.self::formInput($template,$params);
	    return $html;
	}

	static function submit_button($template=null){
	    $title = ( isset($template['title']) )?$template['title']:null;
	    $name = ( isset($template['name']) )?$template['name']:"SUBMIT";
	    $atype = ( isset($template['atype']) )?$template['atype']:false;

	    $icon = ( isset($template['icon']) )?$template['icon']:'file-pdf-o';

	    $html = '<button title="'.$title.'" value="submit" name="'.$name.'" aspect="default" type="submit" class="'.($atype ? 'ajaxsubmit' : NULL).'">'.$title.'</button>';

	    $html = submit($name,$title, false, $title, $atype,$icon);
	    return $html;
	}

	static function formInput($template=null, $params=null){
	    global $ci;
	    $name = ( isset($template['name']) )?$template['name']:null;

		$type = ( isset($template['type']) )?$template['type']:null;

		$attributes = ( isset($template['attr']) )?$template['attr']:null;
		$disabled = ( isset($template['disabled']) )?$template['disabled']:false;

		$input_field = ( isset($template['field']) )?$template['field']:null;
		$value = '';

		if( isset($input_field['value']) ){
			$value = $input_field['value'];
		}else if ( isset($template['value']) ) {
			$value = $template['value'];
		}

		if( $attributes ){
			$attributes = _parse_attributes($attributes);
		} else if ( isset($input_field['attr']) ){
			$attributes = $input_field['attr'];
		}

		$view = false;
		$tpl_vars = $params->smarty->tpl_vars;

		if( isset($tpl_vars['formViewOnly']) && $tpl_vars['formViewOnly']->value ){
		    $view = 'value';
		}
		if( !is_array($attributes) ){

		    $attributes = _attributes_str2array($attributes);
		}

		if(  isset($template['class']) ){
		    $class = null;

		    if( !array_key_exists('class', $attributes) ){
		        $attributes['class'] = $template['class'];
		    } else {
		        $attributes['class'] .= " ".$template['class'];
		    }

		}
		if(  isset($template['readonly']) ){
		    $attributes['readonly'] = 1;
		}

		$html = '';
		if( !isset($title) ){
		    $title = NULL;
		}
		switch ($type){
			case 'DATEBEGINM':
			case 'DATEENDM':
			case 'DATE':
			case 'date':
			case 'qdate':
			    $attributes['class'] = 'qdatepicker';
			    $input = "";
                $input = input_date_bootstrap(NULL,$name,$value);
                return $input;
			    //return $ci->finput->qDate($title,$name,$value,$attributes,$disabled,false,$view);
			    break;
			case 'TEXTBOX':
				return self::input_textarea($name,$value); break;
			case 'HIDDEN':
				return self::hidden($name,$value); break;

			CASE 'CUSTOMER':
			case 'customer':

			    $all = ( isset($template['all']) )?$template['all']:false;
				return self::input_customer($name,$value,$all,$attributes,$view); break;
			CASE 'SUPPLIER':
			    $all = ( isset($template['all']) )?$template['all']:false;
				return self::input_supplier($name,$value,$all); break;

			case 'BRANCH':
		    case 'branch':
				$customer_id = 0;
				if( isset($params->tpl_vars['customer']) ){
					$customer_id =$params->tpl_vars['customer']->value['value'];
				}
				return self::input_branch($name,$value,$customer_id,$attributes,$view); break;
			CASE 'TAX':
		    CASE 'gst':
				$use_for = 0;
				if( isset($params->tpl_vars['customer']) ){
					$use_for = 2;
				}
				if( isset($template['group']) ){
				    $use_for = $template['group'];
				}

				//return self::input_tax($name,$value,$use_for);
				return $ci->finput->inputtaxes(null,$name,$value,$use_for);
				break;
			case 'products':
				return self::input_product($name,$value,$attributes,$disabled); break;
			case 'stock_product_select':
                $html = $ci->finput->text(NULL,$name.'_code');
			    $html.= $ci->finput->product_items(null,$name.'_select',$value,$attributes,$disabled);
			    return $html;
			    break;
			case 'currency':
			case 'CURRENCY':
// 				return self::input_currency($name,$value,$attributes,$view);
				return $ci->finput->currency(null,$name, $value,$attributes,$disabled); break;
				break;
			case 'number':
				$attributes = array('class'=>'number form-control');
				return self::input_text($name,$value,$attributes,$disabled); break;
			case 'hidden':
			    return self::inputHidden(array('name'=>$name,'value'=>$value)); break;

		    case 'BANK_ACCOUNTS':
		        return self::input_bank_account($name,$value); break;
			case 'bank_accounts':
			    $submit_on_change = ( isset($template['onchange_ajax']) )?$template['onchange_ajax']:false;
			    return bank_accounts_list($name, $value,$submit_on_change,'Select Bank Account');

			case 'GL_ACC':
            case 'gl_acc':
                return gl_all_accounts_list($name, $value);
//                 return $ci->finput->gl_acc(null, $name, $value);
                break;

		    case 'multitaxes':
                //return $ci->finput->multitaxes(null,$name, $value);
                return input_multitaxes(null,$name, $value);
                break;
		    case 'trans_type':
		    	return $ci->finput->trans_type(null,$name, $value); break;
	    	case 'check':
		    case 'checkbox':

// 		        return $ci->finput->checkbox($title,$name,$value,$attributes,$disabled);
                return checkbox_material($name,$value);
		        break;
		    case 'orientation':
		        $orientations = array();
		        $orientations[] = (object) array('id'=>'portrait','title'=>'Portrait');
		        $orientations[] = (object) array('id'=>'landscape','title'=>'Landscape');
		        return $ci->finput->options($name,$orientations,$value,NULL,'combo2 form-control'); break;
		        break;
            case 'destination':
	            $destination = array();
	            $destination[] = (object) array('id'=>'pdf','title'=>'PDF/Printer');
	            $destination[] = (object) array('id'=>'excel','title'=>'Excel');
	            return $ci->finput->options($name,$destination,$value,NULL,'combo2 form-control'); break;
		    case 'options':
		        $options = array();
		        if( isset($input_field['options']) && !empty($input_field['options']) ){
		            $options = $input_field['options'];
		        }

		        return $ci->finput->options($name,$options,$value,NULL,'combo2 form-control'); break;
		        break;
// 		    case 'stock_search':
// 		        return $ci->finput->product_items(null,$name, $value); break;
			default:
				return self::input_text($name,$value,$attributes,$disabled,$view); break;
		}

	}


	static function input_date($name='',$value=null,$disabled=false){
		global $dateformats,$dateseps;

		$config_model = self::model('config',true);
		$date_system = user_date_format();
		$dateseps_system = user_date_sep();

		$dateformat = $dateformats[$date_system];
		$dateseps_use = $dateseps[$dateseps_system];
		$format = 'd-m-Y';

		switch ($dateformat){
			case 'MMDDYYYY': $format="m$dateseps_use"."d$dateseps_use"."Y"; break;
			case 'DDMMYYYY': $format="d$dateseps_use"."m$dateseps_use"."Y"; break;
			case 'YYYYMMDD': $format="Y$dateseps_use"."m$dateseps_use"."d"; break;
			case 'MmmDDYYYY': $format="M$dateseps_use"."d$dateseps_use"."Y"; break;
			case 'DDMmmYYYY': $format="D$dateseps_use"."M$dateseps_use"."Y"; break;
			case 'YYYYMmmDD': $format="Y$dateseps_use"."M$dateseps_use"."D"; break;
			default: $format = 'd-m-Y'; break;

		}
		$value = date($format,strtotime($value));
		$out = '<input type="text" value="'.$value.'" '.$disabled.' maxlength="12" size="10" class="date" name="'.$name.'" autocomplete="off" '.( $disabled? 'disabled': null).' >';
		$img = '<a class="inputdate" href="javascript:date_picker(document.getElementsByName(\''.$name.'\')[0]);" tabindex="-1">'
					.'<img width="16" height="16" border="0" alt="Click Here to Pick up the date" src="'.site_url('').'/themes/default/images/cal.gif">'
				.'</a>';
		return '<span class="form-control inputdate">'.$out.$img.'</span>';
	}



	static function input_view(){

	}

	static function days_to_now($template){
        if( !isset($template['time']) ){
            return 0;
        } else {
            $days = strtotime(Today()) - strtotime($template['time']);
            return $days/(60*60*24);
        }
	}

	static function inputHidden($template){
		$name = ( isset($template['name']) )?$template['name']:null;
		$value = ( isset($template['value']) )?$template['value']:null;
		return self::hidden($name,$value);
	}

	static function hidden($name='',$value=null){
		if( !is_array($value) && !is_array($name) ){
			return '<input type="hidden" value="'.$value.'" name="'.$name.'">';
		}

	}

	static function input_text($name='',$value=null,$attributes=null,$disabled=false,$view=NULL){
		if( !$attributes ){
			$attributes = 'class="form-control"';
		}
		switch ($view){
		    case 'value':
		        return $value; break;
		    default:
		        return '<input type="text" value="'.$value.'" name="'.$name.'" autocomplete="off"  '._parse_attributes($attributes).'  '.( $disabled? 'disabled': null).' >';
		}

	}

	static function input_textarea($name=''){
		return '<textarea class="form-control" rows="3" name="'.$name.'"></textarea>';
	}

	static function input_selectbox($name='',$value=null,$opts=null,$attributes=null){
	    global $Ajax;
		$html = '<select autocomplete="off" class="combo2 form-control '.( isset($attributes['class']) ? $attributes['class']: NULL).'"  name="'.$name.'"  rel="_'.$name.'_edit" _last="0"  >';

		if( isset($opts['options']) && !empty($opts['options'])){
			foreach ($opts['options'] AS $val=>$opt){
				$selected = null;
				if( is_array($opt) ){
					$html .=' <optgroup label="'.$opt['title'].'">';
					foreach ($opt['items'] AS $val=>$opt){
						$selected = null;
						if( $val== $value){
							$selected = ' selected="selected" ';
						}
						$html.='<option value="'.$val.'" '.$selected.' >'.$opt.'</option>';

					}
					$html .=' </optgroup>';
				} else {
					if( $val== $value){
						$selected = ' selected="selected" ';
					}
					$html.='<option value="'.$val.'" '.$selected.' >'.$opt.'</option>';
				}

			}
		}
		$html.='</select>';


		return $html;
	}

	static function input_bank_account($name='',$value=null){
		$model = self::model('bank',true);
		$banks = $model->item_options();
		return self::input_selectbox($name,$value,array('options'=>$banks));
	}

	static function input_customer($name='',$value=null,$all=false,$attributes=null,$view=NULL){

		$model = self::model('cutomer',true);
		$debtors = $model->item_options();
		$options = array();
		if($all){
		    $options['all'] = 'All Customer';
		} else {
		    $options[null] = '-- Select Customer --';
		}
		$options+= $debtors;

		switch ($view){
		    case 'value':
		        return $options[$value];
		        break;
		    default:
                return self::input_selectbox($name,$value,array('options'=>$options),$attributes);
		}

	}

	static function input_supplier($name='',$value=null,$all=false){
		$model = self::model('supplier',true);


		$options = array();

		if($all){
		    $options['all'] = 'All Supplier';
		} else {
		    $options[null] = '-- Select Supplier --';
		}

		$options += $model->item_options();

		return self::input_selectbox($name,$value,array('options'=>$options));
	}

	static function input_branch($name='',$value=null,$customer_id=0,$attributes=null,$view=NULL){
		$model = self::model('cutomer',true);
		$items = array();
		if( $customer_id ){
			$items =$model->branch_options($customer_id);
		}


		switch ($view){
		    case 'value':
		        return $items[$value];
		        break;
		    default:
		        return self::input_selectbox($name,$value,array('options'=>$items),$attributes);
		}

	}

	static function input_currency($name='',$value=null,$attributes=NULL,$view=NULL){
		$model = self::model('config',true);
		$items = $model->currency_options();
		if( !$value ) {
			$value = $model->get_sys_pref_val('curr_default');
		}
		switch ($view){
		    case 'value':
		        return $items[$value];
		        break;
		    default:
		       return self::input_selectbox($name,$value,array('options'=>$items),$attributes);
		}


	}

	static function input_tax($name='',$value=null,$use_for=1){
		$model = self::model('tax',true);


		$items = $model->item_options($use_for);

		return self::input_selectbox($name,$value,array('options'=>$items));
	}

	static function input_product($name,$value){
		$model = self::model('product',true);
		$items = $model->item_options();
		return self::input_selectbox($name,$value,array('options'=>$items));
	}



	/*
	 * helpper function
	 */
	static function print_address($template){
	    global $ci;
	    $address = ( isset($template['addr']) )?$template['addr']:null;
	    $address = trim($address);
	    $address = str_replace("\n",'<br>',$address);
	    return html_entity_decode($address);

	}
	static function date_format($template){
	    global $dateformats,$dateseps;
	    $date_system = user_date_format();
	    $dateseps_system = user_date_sep();

	    $dateformat = $dateformats[$date_system];
	    $dateseps_use = $dateseps[$dateseps_system];
	    $format = 'd-m-Y';

	    switch ($dateformat){
	        case 'MMDDYYYY': $format="m$dateseps_use"."d$dateseps_use"."Y"; break;
	        case 'DDMMYYYY': $format="d$dateseps_use"."m$dateseps_use"."Y"; break;
	        case 'YYYYMMDD': $format="Y$dateseps_use"."m$dateseps_use"."d"; break;
	        case 'MmmDDYYYY': $format="M$dateseps_use"."d$dateseps_use"."Y"; break;
	        case 'DDMmmYYYY': $format="D$dateseps_use"."M$dateseps_use"."Y"; break;
	        case 'YYYYMmmDD': $format="Y$dateseps_use"."M$dateseps_use"."D"; break;
	        default: $format = 'd-m-Y'; break;

	    }
	    $time = ( isset($template['time']) )?$template['time']: NULL;
	    if( $time !='0000-00-00' && $time )
	        return date($format,strtotime($time));
	    return NULL;
	}

	static function datetime_format($template){
	    global $ci;
	    $time = ( isset($template['time']) )?$template['time']: Today();

	    if( $time !='0000-00-00' && $time )
	        return date($ci->dateformatPHP,date_convert_timestamp($time)).' '.Now();
	    return NULL;
	}

    static function page_padding($template){
        global $ci;
        $total = ( isset($template['total']) )?intval($template['total']):0;
        $page = ( isset($template['page']) )?intval($template['page']):1;

        $html = NULL;
        if( $page > 1 ){
            $html .= anchor($ci->uri->uri_string."?p=1",'First',array('class'=>'ajaxsubmit'));
            $html .=anchor($ci->uri->uri_string."?p=".($page-1),'Previous',array('class'=>'ajaxsubmit'));
        }

        $last = $total/page_padding_limit;
        if( $last > intval($last) ){
            $last = intval($last)+1;
        } else {
            $last = intval($last);
        }

        if( $page*page_padding_limit < $total ){
            $html .=anchor($ci->uri->uri_string."?p=".($page+1),'Next',array('class'=>'ajaxsubmit'));
            $html .=anchor($ci->uri->uri_string."?p=".($last),'Last',array('class'=>'ajaxsubmit'));
        }

        return $html;
    }







}