<?php
class form_smarty {
    static $smarty, $ci;
    function __construct(){
        $ci = get_instance();
        if( isset($ci->smarty) ){
            self::$smarty = $ci->smarty;
        }
        self::$ci = $ci;
    }

    static function table_page_padding($template,$params){
		global $ci;
		$ajax = ( isset($template['ajax']) )?$template['ajax']:false;
		$page = ( isset($template['page']) )?$template['page']:0;
        if( !$page ){
            $page = input_val('page');
        }
    	$total = $params->tpl_vars['total']->value;
    	$items_show = count($params->tpl_vars['items']->value);
    	$page_max = round($total/page_padding_limit);
    	if( $page_max*page_padding_limit <$total){
    		$page_max++;
    	}


    	$html = self::padding_button(1,'First',$ajax);

    	if( $page > 1 ){
    		$html .= self::padding_button($page-1,'Pre',$ajax);

    	}
    	if( $page < $page_max ){
    		$html .= self::padding_button($page+1,'Next',$ajax);
    	}
    	if( $page < $page_max ){
    		$html .= self::padding_button($page_max,'End',$ajax);
    	}

    	return $html;
    }

    static function padding_button($page=1,$title='',$ajax=0){

        $uri = $_SERVER['PATH_INFO'];
        parse_str( html_entity_decode($_SERVER['QUERY_STRING']), $querys);
        if( array_key_exists('page', $querys) ){
            unset( $querys['page']);
        }
        if( !empty($querys) ){

            $uri .=  '?'.http_build_query($querys).'&';
        } else {
            $uri .= '?';
        }

        $name = str_replace(" ", null, strtolower($title));

        if( $ajax ){
            return '<button value="'.$page.'" name="'.$name.'" type="submit" class="navibutton btn green">'.$title.'</button>';
        } else {
            return '<a href="'.site_url($uri).'page='.$page.'" class="ajaxsubmit">'.$title.'</a>';
        }
        $html = '';
    }
}