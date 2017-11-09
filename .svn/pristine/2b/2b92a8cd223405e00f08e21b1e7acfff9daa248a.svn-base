<?php
class FaThemeSmarty{
    function __construct(){

    }

    static function company_logo(){
        $coy_logo = company_logo();
//         $system_config = get_instance()->db->where('"name','coy_logo')->get('sys_prefs')->row();
        $logo = null;
        if ( $coy_logo) {
            $logo = '<img src="' . $coy_logo . '" class="img-responsive" >';
        } else {
            $logo = get_company_Pref('coy_name');
        }


        return anchor(site_url(),"<span>$logo</span>");
    }
}