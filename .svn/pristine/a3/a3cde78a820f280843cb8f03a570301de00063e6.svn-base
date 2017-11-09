<?php


function submit_cells($name, $value, $extra="", $title=false, $async=false)
{
    echo "<td $extra>";
    submit($name, $value, true, $title, $async);
    echo "</td>\n";
}


function icon_anchor_cell($name, $button_type = 'secondary', $icon='save', $js_onclick=NULL, $title=NULL, $content=NULL)
{

    if( is_null($button_type) ){
        $button_type = 'secondary';
    }


    $attributes = array(
        'href'=>'javascript:void(0)',
        'name'=>$name,
        'onclick'=>" $js_onclick; return true; ",
        'title'=>$title,
        'class'=>"btn btn-$button_type",
    );



    if ( !is_string($icon) ){
        $icon_show = '<i class="fa fa-save"></i> ';
    } elseif( is_string($icon) ) {

        if( strpos($icon, 'fa-') !== false ){
            $icon = "fa $icon";
        }
        $icon_show = '<i class="'.$icon.'"></i> ';
    }

    $button =  "<a "._parse_attributes($attributes)." >".$icon_show."</a>";
    //     submit($name, $value, true, $title, $async);
    echo "<td align=\"center\" >$button $content </td>\n";
}

function submit_row($name, $value, $right=true, $extra="", $title=false, $async=false)
{
	echo "<tr>";
	if ($right)
		echo "<td>&nbsp;</td>\n";
		submit_cells($name, $value, $extra, $title, $async);
	echo "</tr>\n";
}

function submit_center($name, $value, $echo=true, $title=false, $async=false, $icon=false)
{
    if ($echo) echo "<center>";
    submit($name, $value, $echo, $title, $async, $icon);
    if ($echo) echo "</center>";
}

function submit_center_first($name, $value, $title=false, $async=false, $icon=false)
{

    submit($name, $value, true, $title, $async, $icon);

}

function submit_center_last($name, $value, $title=false, $async=false, $icon=false)
{
    echo "&nbsp;";
    submit($name, $value, true, $title, $async, $icon);
    echo "</center>";
}

/*
 Universal submit form button.
 $atype - type of submit:
 Normal submit:
 false - normal button; optional icon
 null  - button visible only in fallback mode; optional icon
 Ajax submit:
 true	  - standard button; optional icon

 'default' - default form submit on Ctrl-Enter press; dflt ICON_OK icon
 'selector' - ditto with closing current popup editor window
 'cancel'  - cancel form entry on Escape press; dflt ICON_CANCEL
 'process' - displays progress bar during call; optional icon
 'nonajax' - ditto, non-ajax submit
 $atype can contain also multiply type selectors separated by space,
 however make sense only combination of 'process' and one of defualt/selector/cancel
 */

function submit($name, $value, $echo=true, $title=false, $atype=false, $icon=false,$float=NULL)
{

    $aspect='';
    if ($atype === null) {
        $aspect = fallback_mode() ? " aspect='fallback'" : " style='display:none;'";

    } elseif (!is_bool($atype)) { // necessary: switch uses '=='

        $aspect = "aspect='$atype' ";
        $types = explode(' ', $atype);

        foreach ($types as $type) {
            switch($type) {
                case 'selector':
                    $aspect = " aspect='selector' rel = '$value'";
                    $value = _("Select");
//                     if ($icon===false)
//                         $icon='save';
                    break;

//                 case 'default':
//                     if ($icon===false)
//                         $icon='save';
//                     break;

//                 case 'cancel':
//                     if ($icon===false)
//                         $icon=ICON_ESCAPE; break;

                case 'nonajax':
                    $atype = false;
                    break;
            }
        }
    }

    $icon_show = NULL;
    if ( !is_string($icon) AND $icon ){
        $icon_show = '<i class="fa fa-save"></i> ';
    } elseif( is_string($icon) ) {
        $icon_show = '<i class="fa fa-'.$icon.'"></i> ';
    }



//     if( strlen($float) < 1 ){
//         $float = ' float-xl-right float-lg-right float-md-right ';
//     }

    $attributes = array(
        'type'=>'submit',
        'name'=>$name,
        'id'=>$name,
        'value'=>$value,
        'title'=>$title,
        'class'=>"btn green",
    );

    if( $atype ){
        $attributes['class'] .= ' ajaxsubmit';
    } else {
        $attributes['class'] .= ' inputsubmit';
    }


    $submit_str = "<button $aspect "._parse_attributes($attributes)." >".$icon_show.$value."</button>\n";

    if ($echo)
		echo $submit_str;
	else
		return $submit_str;
}

function submit_search($name, $value, $async=false){
    $title = "";
    submit($name, $value, true, $title, $async,'search');
}
function submit_icon($name, $value, $icon=false, $title=false ){
    $async = 'default';
    submit($name, $value, $echo=true, $title, $async, $icon);
}