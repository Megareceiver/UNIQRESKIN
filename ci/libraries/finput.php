<?php
class CI_finput {


    function __construct(){
        global $ci;
        $this->ci = $ci;
    }

	public function __call($method, $args){
		$input_title = ( isset($args[0]) ) ? $args[0] : _('Title');
		$input_name = ( isset($args[1]) ) ? $args[1] : 'inputname';
		$input_val = ( isset($args[2]) ) ? $args[2] : null;
		$input_return_type = ( isset($args[3]) ) ? $args[3] : 'input';

		if( method_exists($this,$method) ){

		    return $this->$method();
		} else if ( !isset($this->$method) && file_exists(BASEPATH."finput/$method.php")) {
			require_once(BASEPATH."finput/$method.php");

			$method_class = new $method();
            unset($args[0]);
			$input = call_user_func_array(array($method_class, "input"), $args);

			if( $method=='inputtaxes' && isset($args[4]) ) {
			    $input_return_type = $args[4];
			}
		} else {

		    $input = $this->text($input_name,$input_val);
		}

		switch ($input_return_type){
			case 'row':
				return '<tr> <td class="label">'.$input_title.':</td> <td>'.$input.'</td> </tr>'; break;
			case 'row_echo':
			    echo '<tr> <td class="label">'.$input_title.':</td> <td>'.$input.'</td> </tr>'; break;
			case 'column':
			case 'in_row_input':
			case 'in_row_title':
			    return '<td>'.$input.'</td>'; break;
			default :
				return $input; break;
		}
	}

	private function output($input=null,$input_title=null,$output_type='html'){
	    switch ($output_type){
	        case 'row':
	            return '<tr> <td class="label">'.$input_title.':</td> <td>'.$input.'</td> </tr>'; break;
	        case 'row_echo':
	            echo '<tr> <td class="label">'.$input_title.':</td> <td>'.$input.'</td> </tr>'; break;
	        case 'column':
	        case 'in_row_input':
	        case 'in_row_title':
	            return '<td>'.$input.'</td>'; break;
	        default :
	            return $input; break;
	    }
	}

	private function return_output($input_return_type,$input_title,$input=null){
	    switch ($input_return_type){
	        case 'row':
	            return '<tr> <td class="label">'.$input_title.':</td> <td>'.$input.'</td> </tr>'; break;
	        case 'column':

	        case 'in_row_input':
	        case 'in_row_title':
	            return '<td>'.$input.'</td>'; break;
            case 'cells':
                return "<td><lable>$input_title</lable> $input </td>"; break;
	        default :
	            return $input; break;
	    }
	}

	function text($title='',$name,$val='',$showType=''){
        $input= '<input type="text" value="'.$val.'" maxlength="18" name="'.$name.'">';
        return $this->output($input,$title,$showType);
	}

	function hidden($name,$value){
	    return '<input type="hidden" value="'.$value.'"name="'.$name.'">';
	}

	function array2options($array=NULL){
	    $options = array();
        if( !empty($array) ){ foreach ( $array AS $k=>$val ){
            $options[] = (object) array('id'=>$k,'title'=>$val);
        }}
        return $options;
	}
	function options($name,$option_opt,$selected=null,$empty_title=NULL,$class="combo2" ,$autocomplete='off',$ajax_update=false,$readonly=false){


	    if( !$class ){
	        $class="combo2";
	    }
	    $readonly_set = null;
	    if ($readonly) {
	        $readonly_set =' disabled="true"  ';
	        $selected = null;
	    }
	    $html ='<select class="'.$class.'" rel="_'.$name.'_edit" title="" name="'.$name.'" autocomplete="'.$autocomplete.'" '.$readonly_set.' >';
	    if( $empty_title ){
	        $html.='<option value="">'.$empty_title.'</option>';
	    }

	    if( !empty($option_opt) ){

	        foreach ($option_opt AS $k=>$opt){
	            if( is_array($opt) ){
	                $opt = (object) $opt;
	            } elseif( is_string($opt) ){
	                $opt = (object)array('id'=>$k,'title'=>$opt);
	            }
	            $value = ( $opt->id==$selected ) ? ' selected ' : null;

	            $title = "";
	            if( isset($opt->title) ) {
	                $title = $opt->title;
	            }elseif($opt->value){
	                $title = $opt->value;
	            }
	            if( isset($opt->items) && !empty($opt->items) ){
	                $html.='<optgroup label="'.$title.'">';
	                foreach ($opt->items AS $sub){
	                    $value = ( $sub->id==$selected ) ? ' selected ' : null;
	                    $html.='<option value="'.$sub->id.'" '.$value.'>'.$sub->title.'</option>';
	                }
	                $html.='</optgroup>';
	            } else {
	                $html.='<option value="'.$opt->id.'" '.$value.'>'.$title.'</option>';
	            }

	        }
	    }
	    $html.='</select>';
	    if( $ajax_update ) {
	        global $Ajax;
	        $Ajax->addUpdate($name, "_{$name}_sel", $html);

	        $html = "<span id='_{$name}_sel' class='select'>".$html."</span>";
	        $html .='<input type="submit" value="" aspect="fallback" name="_'.$name.'_update"  style="display: none;" class="combo_select">';
	    }

	    return '<span id="_'.$name.'_sel" class="select">'.$html.'</span>';
	}

	function qDate($input_title, $name,$value=0,$input_return_type='',$disabled=false,$submit_on_change=null,$view=null,$inc_days=0,$inc_months=0, $inc_years=0){
	    global $ci;
	    if( !$value ){
	        if( isset($_POST[$name]) ){
	            $value = $_POST[$name];
	        } else {
	            $value = Today();
	        }
	    }
	    if ($inc_days != 0)
	        $value = add_days($value, $inc_days);
	    if ($inc_months != 0)
	        $value = add_months($value, $inc_months);
	    if ($inc_years != 0)
	        $value = add_years($value, $inc_years);

	    if( $view =='value' ){
	        return qdate_format($value);
	    }
	    $input = '<span class="form-control inputdate">';
	    $input.= '<input class="qdate" type="text" name="'.$name.'" value="'.qdate_format($value).'" data-date-format="'.$ci->dateformat.'" ><span class="icon"> </span>';
	    $input.= '</span>';

	    return $this->return_output($input_return_type,$input_title,$input);
	}

	function checkbox($input_title, $name,$value=0,$input_return_type='',$disabled=false){
	    $input = '<input class="" type="checkbox" name="'.$name.'" '.($value==1 ? 'checked' : null ).' >';

	    return $this->return_output($input_return_type,$input_title,$input);
	}


	function print_form($fields){
	    include_once(ROOT . "/ci/libraries/form.php");
	    $input_html = '';
        foreach ($fields AS $key=>$field){
            $form_field = array(
                'type'=>$field['input'],
                'name'=>$key,
                'value'=> ( isset($field['value']) )? $field['value'] : null
            );
            if( isset($field['onchange_ajax']) ){
                $form_field['onchange_ajax'] = $field['onchange_ajax'];
            }
            $input = form::formInput($form_field);

            if( $field['input'] =='hidden' ){
                $input_html .= $input;
            } else {
                $input_html .= "<tr><td>".$field['title']."</td><td>$input</td>";
            }

        }
        start_form();
        start_table(TABLESTYLE2);
        echo $input_html;
        end_table(1);
        submit_center('submit', _("Submit"), true, '', 'default');
	    end_form(1);

	}


	function get_post($fields=null){
	    $data = null;
	    if( !empty($fields) && is_array($fields) ){
	        $data = array();
	        foreach ($fields AS $k => $v){
	            $key =  is_string($k) ? $k : $v;
	            if( isset($_POST[$key]) ){
	                $data[$key] = $_POST[$key];
	            }
	        }
	    }
	    return $data;
	}

}