<?php

class bootrap_smarty {

    private static function formInput($template=null, $params=null){
        global $ci;

        $type = ( isset($template['type']) )?$template['type']:null;
        $name = ( isset($template['name']) )?$template['name']:null;
        $attributes = ( isset($template['attr']) )?$template['attr']:null;
        $disabled = ( isset($template['disabled']) )?$template['disabled']:false;

        $input_field = ( isset($template['field']) )?$template['field']:null;
        $value = '';

        if( isset($input_field['value']) ){
            $value = $input_field['value'];
        } else if ( isset($template['value']) ) {
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

        if( isset($input_field['unit']) ){
            $attributes['unit'] = $input_field['unit'];
        }
        if( isset($input_field['submitchange']) ){
            $attributes['submitchange'] = $input_field['submitchange'];
        }

        $title = "";
        $html = '';
        switch ($type){
            case 'DATEBEGINM':
            case 'DATEENDM':
            case 'DATE':
            case 'date':
            case 'qdate':
                $attributes['class'] = 'qdatepicker';
                return $ci->finput->qDate($title,$name,$value,$attributes,$disabled,false,$view);
                break;
            case 'TEXTBOX':
                return self::input_textarea($name,$value); break;
            case 'HIDDEN':
                return self::hidden($name,$value); break;
            case 'BANK_ACCOUNTS':
                return self::input_bank_account($name,$value); break;
            CASE 'CUSTOMER':
            case 'customer':

                $all = ( isset($template['all']) )?$template['all']:false;
                //return self::inputCustomer($name,$value,$all,$attributes,$view); break;

                return $ci->finput->customer('',$name,$value,$view,$readonly=false,$all=true,$attributes); break;
            CASE 'SUPPLIER':
                return self::input_supplier($name,$value); break;

            case 'BRANCH':
            case 'branch':
                $customer_id = 0;
                if( isset($params->tpl_vars['customer']) ){
                    $customer_id = $params->tpl_vars['customer']->value['value'];
                }
                if( !$customer_id && isset($input_field['debtor']) ){
                    $customer_id = $input_field['debtor'];
                }

                return $ci->finput->branch('',$name,$value,$view,$customer_id,$attributes);
                break;
            CASE 'TAX':
                $use_for = 0;
                if( isset($params->tpl_vars['customer']) ){
                    $use_for = 2;
                }

                return self::input_tax($name,$value,$use_for); break;
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
                $attributes = array('class'=>'number');
                return self::input_text($name,$value,$attributes,$disabled); break;
            case 'hidden':
                return self::inputHidden(array('name'=>$name,'value'=>$value)); break;
            case 'bank_accounts':
                $submit_on_change = ( isset($template['onchange_ajax']) )?$template['onchange_ajax']:false;
                return bank_accounts_list($name, $value,$submit_on_change,'Select Bank Account');
            case 'multitaxes':
                return $ci->finput->multitaxes(null,$name, $value); break;
            case 'trans_type':
                return $ci->finput->trans_type(null,$name, $value); break;
            case 'checkbox':
                return $ci->finput->checkbox($title,$name,$value,$attributes,$disabled); break;
            case 'orientation':
                $orientations = array();
                $orientations[] = (object) array('id'=>'portrait','title'=>'Portrait');
                $orientations[] = (object) array('id'=>'landscape','title'=>'Landscape');
                return $ci->finput->options($name,$orientations,$value,NULL,'combo2 form-control'); break;
                break;
            case 'payment':
                return $ci->finput->payment(null,$name,$value); break;
            case 'sales_type':
                return $ci->finput->sale_type(null,$name,$value); break;
            case 'location':
                return $ci->finput->location(null,$name,$value); break;
            case 'options':
                $options = array();
                if( isset($input_field['options']) && !empty($input_field['options']) ){
                    $options = $input_field['options'];
                }

                return $ci->finput->options($name,$options,$value,NULL,'combo2 form-control'); break;
                break;
                // 		    case 'stock_search':
                // 		        return $ci->finput->product_items(null,$name, $value); break;
            case 'textarea':
                return '<textarea rows="4" name="'.$name.'" >'.$value.'</textarea>'; break;
            default:


                return self::inputText($name,$value,$attributes,$disabled,$view); break;
        }

    }



    static function binput($template=null, $params=null){
        $key = ( isset($template['key']) )?$template['key']:null;
        $title = ( isset($template['title']) )?$template['title']:null;

        if( !isset($params->tpl_vars['fields']) || !$key ) return NULL;

        $fields = $params->tpl_vars['fields']->value;
        if( !array_key_exists($key, $fields) ){
            return _('No Field Input');
        }
        $field = $fields[$key];

        $template['field'] = $field;
        $left = ( isset($template['leftCol']) )?$template['leftCol']:3;

        $right = 12 - $left;

//         $field = ( isset($template['field']) )?$template['field']:null;
        $template['name'] = $key;
        if ( is_array($field) && count($field) > 0 ){
            if( !$title && array_key_exists('title',$field) ){
                $title = $field['title'];
            }
            if( !isset($template['name']) && isset($field['type']) ){
                $template['name'] = $field['type'];
            }
            if( !isset($template['type']) && isset($field['type']) ){
                $template['type'] = $field['type'];
            }


        }
        if( !$title ){
            $title = ucfirst($key);
        }

        $html = '<div class="form-group clearfix">
		<div class="col-sm-'.$left.'"><label for="">'.$title.'</label></div>
				<div class="col-sm-'.$right.'">'.self::formInput($template,$params).'</div>
		</div>';
        return $html;
    }

    static function inputText($name='',$value=null,$attributes=null,$disabled=false,$view=NULL){
        if( !$attributes ){
            $attributes = 'class="form-control"';
        }
        $html = NULL;
        $unit = NULL;
        if( is_array($attributes) ){
            if(  !array_key_exists('class', $attributes) ){
                $attributes['class'] = "form-control";
            }
            if(  array_key_exists('unit', $attributes) ){
                $unit = $attributes['unit'];
                unset($attributes['unit']);
            }
        }

        switch ($view){
            case 'value':
                $html = $value; break;
            default:
                $html = '<input type="text" value="'.$value.'" name="'.$name.'" autocomplete="off"  '._parse_attributes($attributes).'  '.( $disabled? 'disabled': null).' >';
                if( $unit!=NULL ){
                    $html .="<span class=\"unit\">$unit</span>";
                }
                break;

        }
        return $html;

    }

    static function inputSubmit($template=null){

    }

}