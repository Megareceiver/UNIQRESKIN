<?php
function set_icon($icon, $title=false)
{
    switch ($icon){
        case ICON_EDIT:
            return '<i class="fa fa-edit text-info"></i>';
            break;
        case ICON_DELETE:
            return '<i class="fa fa-remove text-danger"></i>';
            break;
        case ICON_DOWN:
            return '<i class="fa fa-download text-info"></i>';
            break;
        case ICON_VIEW:
            return '<i class="fa fa-eye text-info"></i>';
            break;
        case ICON_UPDATE:
            return '<i class="fa fa-save text-info"></i>';
            break;
        case ICON_CANCEL:
            return '<i class="fa fa-rotate-left text-info"></i>';
            break;
        case ICON_ALLOC:
            return '<i class="fa fa-chain text-info"></i>';
            break;

        default: return 'button icon'; break;
    }
}

function button($name, $value, $title=false, $icon=false,  $aspect='')
{
    // php silently changes dots,spaces,'[' and characters 128-159
    // to underscore in POST names, to maintain compatibility with register_globals
    $rel = '';
    if ($aspect == 'selector') {
        $rel = " rel='$value'";
        $value = _("Select");
    }
    if (user_graphic_links() && $icon) {
        if ($value == _("Delete")) // Helper during implementation
            $icon = ICON_DELETE;

        return "<button type='submit' class='editbutton table_actions' name='"
            .htmlentities(strtr($name, array('.'=>'=2E', '='=>'=3D',// ' '=>'=20','['=>'=5B'
            )))
            ."' value='1'" . ($title ? " title='$title'":" title='$value'")
            . ($aspect ? " aspect='$aspect'" : '')
            . $rel
            ." />".set_icon($icon)."</button>\n";
    } else
        return "<input type='submit' class='editbutton' name='"
            .htmlentities(strtr($name, array('.'=>'=2E', '='=>'=3D',// ' '=>'=20','['=>'=5B'
            )))
            ."' value='$value'"
            .($title ? " title='$title'":'')
            . ($aspect ? " aspect='$aspect'" : '')
			. $rel
			." />\n";
}