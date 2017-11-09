<?php
function site_url($uri=null){
    if( strpos($uri, COMPANY_DIR) ){
        $uri = str_replace(COMPANY_DIR, COMPANY_ASSETS, $uri);
        return $uri;
    }
    $ext = NULL;
    if( strlen($uri) > 0 ){
        $uri_query = explode('?', $uri);
        $ext = pathinfo($uri_query[0], PATHINFO_EXTENSION);

        $uri = str_replace('index.php/', NULL, $uri);
    }



// 	return 'http://'.$_SERVER['HTTP_HOST'].( !in_array($ext, array('php','html')) ? '/index.php/' : '/' ).$uri;
    return 'http://'.$_SERVER['HTTP_HOST'].( strlen($ext) < 1 ? '/index.php/' : '/' ).$uri;
}
function base_uri($uri=null){
    global $ci;
    return $ci->uri->config->config['base_url'].$uri;
}

if ( ! function_exists('url_title')) {

    function url_title($str, $separator = '-', $lowercase = true) {
        if ($separator === 'dash') {
            $separator = '-';
        } elseif ($separator === 'underscore') {
            $separator = '_';
        }

        $q_separator = preg_quote($separator, '#');

        $trans = array(
            '&.+?;'			=> '',
            '[^\w\d _-]'		=> '',
            '\s+'			=> $separator,
            '('.$q_separator.')+'	=> $separator
        );

        $str = strip_tags($str);
        foreach ($trans as $key => $val) {
            $str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
        }

        if ($lowercase === TRUE) {
            $str = strtolower($str);
        }

        return trim(trim($str, $separator));
    }
}

if ( ! function_exists('anchor')){

	function anchor($uri = '', $title = '', $attributes = ''){
		$title = (string) $title;
		if ( ! is_array($uri)){
		    if( $uri==null || $uri=='#' ){
		        $site_url = 'javascript:void(0)';
		    } elseif (substr( $uri, 0, 11 ) === "javascript:"){
		        $site_url = $uri;
		    } else {
		        if( substr($uri, 0,1) =='/' ){
		            $uri = substr($uri, 1);
		        }
		        $site_url = ( ! preg_match('!^\w+://! i', $uri)) ? site_url($uri) : $uri;
		    }
		} else {
	        $site_url = site_url($uri);
		}

		if ($title == '') {
			$title = $site_url;
		}

		if ($attributes != '') {
			$attributes = _parse_attributes($attributes);
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}

	function current_url($url=null,$query=''){
		$uri = substr($_SERVER['REQUEST_URI'], 1);
		return $uri.( $query ? "?$query" : null );

	}
}

if ( ! function_exists('redirect')){
	function redirect($uri = '', $method = 'refresh', $http_response_code = 302){
		if ( ! preg_match('#^https?://#i', $uri)){
			$uri = site_url($uri);
		}

		switch($method){
			case 'refresh'	: header("Refresh:0;url=".$uri); break;
			default			: header("Location: ".$uri, TRUE, $http_response_code); break;
		}
		exit;
	}
}

if ( ! function_exists('_parse_attributes')){
	function _parse_attributes($attributes, $javascript = FALSE){
		if (is_string($attributes)){
			return ($attributes != '') ? ' '.$attributes : '';
		}

		$att = '';
        if( !empty($attributes) )
		foreach ($attributes as $key => $val){
			if ($javascript == TRUE){
				$att .= $key . '=' . $val . ',';
			} else {
			    if( is_array($val) ){
			        $val = implode(' ', $val);
			    }
			    $val = trim($val);

			    if( strlen($val) > 0 ){
			        $att .= " $key=\"$val\" ";
			    }

			}
		}

		if ($javascript == TRUE AND $att != '') {
			$att = substr($att, 0, -1);
		}

		return $att;
	}

	function _attributes_str2array($attributes=''){
	    if( is_array($attributes) ){
	        return $attributes;
	    }
	    $pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
	    //$pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
	    preg_match_all($pattern, $attributes, $matches, PREG_SET_ORDER);
	    $attrs = array();
	    foreach ($matches as $match) {
	        if (($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2])-1]) {
	            $match[2] = substr($match[2], 1, -1);
	        }
	        $name = strtolower($match[1]);
	        $value = html_entity_decode($match[2]);
	        switch ($name) {
	            case 'class':
	                //                     $attrs[$name] = preg_split('/\s+/', trim($value));
	                $attrs[$name] = trim($value);
	                break;
	            case 'style':
	                $attrs[$name] = trim($value);
	                // parse CSS property declarations
	                break;
	            default:
	                $attrs[$name] = $value;
	        }
	    }
	    return $attrs;
	}
}

function func_name($str=NULL){
    if( $str ){
        $str = str_replace(array('-'), '_', $str);
    }
    return $str;

}

function company_logo(){
    global $ci;
    $coy_logo = null;
    $company_info = $ci->db->where('name','coy_logo')->get('sys_prefs')->row();

    if ( $company_info && isset($company_info->value) ) {
        $coy_logo = $company_info->value;
    }

    if( $coy_logo ){
        if( file_exists(COMPANY_DIR."/images/$coy_logo") ){
            $coy_logo = COMPANY_ASSETS."/images/$coy_logo";
        } else if ( file_exists(ROOT."/company/0/images/$coy_logo")){

            $coy_logo = "//".$_SERVER['SERVER_NAME']."/company/0/images/".$coy_logo;
        } else {
            $coy_logo = AT_ASSEETS."images/logo.jpg";
        }

    }
    return $coy_logo;
}

function uri_is($url_str = NULL,$auto_redirect=false){

    if( isset($_SESSION["wa_current_user"]) AND isset($_SESSION["wa_current_user"]->logged) AND $_SESSION["wa_current_user"]->logged ){
        $uri = $_SERVER['REQUEST_URI'];


        if( strpos($uri, "?") ){
            $uri_exp = explode("?",$uri);
            $uri = $uri_exp[0];
        }
        $uri = substr($uri, 1);

        if( strlen($uri) < 1 || $uri =='access/logout.php' ){
            return false;
        }

        $check = ($uri==$url_str);

        if( isset($_SESSION["run_wizard"]) AND !$_SESSION["run_wizard"] ){
            $auto_redirect = false;
        }

        if( $auto_redirect ){
            if( !$check ){
                if( in_ajax() ){
                    global $Ajax;
                    $Ajax->redirect($url_str);
                } else {
                    redirect($url_str);
                }

            }

        } else {
            return $check;
        }
    }

}

function isMobile() {
    if( input_get("mobile-test") ){
        return true;
    }
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function isNotMobile(){
    $is_mobile = isMobile();
    return !$is_mobile;
}
