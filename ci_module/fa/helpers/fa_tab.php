<?php

// -----------------------------------------------------------------------------
// Tabbed area:
// $name - prefix for widget internal elements:
// Nth tab submit name: {$name}_N
// div id: _{$name}_div
// sel (hidden) name: _{$name}_sel
// $tabs - array of tabs; string: tab title or array(tab_title, enabled_status)
function tabbed_content_start($name, $tabs, $dft = '')
{
    global $Ajax;

    $selname = '_' . $name . '_sel';
    $div = '_' . $name . '_div';

    $sel = find_submit($name . '_', false);
    if ($sel == null)
        $sel = get_post($selname, (string) ($dft === '' ? key($tabs) : $dft));

    if ($sel !== @$_POST[$selname])
        $Ajax->activate($name);

    $_POST[$selname] = $sel;

    div_start($name);
    $str = "<ul class='ajaxtabs' rel='$div'>\n";
    foreach ($tabs as $tab_no => $tab) {

        $acc = access_string(is_array($tab) ? $tab[0] : $tab);
        $disabled = (is_array($tab) && ! $tab[1]) ? 'disabled ' : '';
        $str .= ("<li>" . "<button type='submit' name='{$name}_" . $tab_no . "' class='" . ((string) $tab_no === $sel ? 'current' : 'ajaxbutton') . "' $acc[1] $disabled>" . "<span>$acc[0]</span>" . "</button>\n" . "</li>\n");
    }

    $str .= "</ul>\n";
    $str .= "<div class='spaceBox'></div>\n";
    $str .= "<input type='hidden' name='$selname' value='$sel'>\n";
    $str .= "<div class='contentBox' id='$div'>\n";
    echo $str;
}

function tabbed_content_end()
{
    echo "</div>"; // content box (don't change to div_end() unless div_start() is used above)
    div_end(); // tabs widget
}

function tab_changed($name)
{
    $to = find_submit("{$name}_", false);
    if (! $to)
        return null;

    return array(
        'from' => $from = get_post("_{$name}_sel"),
        'to' => $to
    );
}