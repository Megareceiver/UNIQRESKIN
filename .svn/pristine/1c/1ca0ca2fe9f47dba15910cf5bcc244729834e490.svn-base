<?php

function numbers_list($label, $name, $selected, $from, $to, $no_option = false)
{
    $input = number_list($name, $selected, $from, $to, $no_option);
    form_group_bootstrap($label, $input);
}

function dateformats_list($label, $name, $value = null)
{
    global $dateformats;

    $input = array_selector($name, $value, $dateformats);
    form_group_bootstrap($label, $input);
}

function dateseps_list($label, $name, $value = null)
{
    global $dateseps;

    $input = array_selector($name, $value, $dateseps);
    form_group_bootstrap($label, $input);
}

function thoseps_list($label, $name, $value = null)
{
    global $thoseps;

    $input = array_selector($name, $value, $thoseps);
    form_group_bootstrap($label, $input);
}

function decseps_list($label, $name, $value = null)
{
    global $decseps;

    $input = array_selector($name, $value, $decseps);
    form_group_bootstrap($label, $input);
}

function languages_bootstrap($label, $name, $selected_id = null, $all_option = false)
{
    $input = languages_list($name, $selected_id, $all_option);
    form_group_bootstrap($label, $input);
}

function pagesizes_list($label, $name, $value=null)
{
    global $pagesizes;

    $items = array();
    foreach ($pagesizes as $pz)
        $items[$pz] = $pz;

    $input = array_selector( $name, $value, $items );
	form_group_bootstrap($label, $input);
}


function tab_list($label, $name, $selected_id=null)
{
    global $installed_extensions;

    $tabs = array();
    foreach ($_SESSION['App']->applications as $app) {
        $tabs[$app->id] = access_string($app->name, true);
    }
    $input =  array_selector($name, $selected_id, $tabs);
    form_group_bootstrap($label, $input);
}
