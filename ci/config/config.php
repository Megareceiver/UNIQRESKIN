<?php
$config['rewrite_short_tags'] = FALSE;
$config['uri_protocol']	= 'REQUEST_URI';
$config['assets_url'] = '//'.$_SERVER['SERVER_NAME'].'/assets/';
$config['assets_dir'] = 'D:\PHP-www\accountanttoday\accounting\at-2016/assets/';

global $session;
$config['assets_domain'] = '//'.$_SERVER['SERVER_NAME']."/tmp/".$session->checkSubDirectory()."/";
$config['assets_path'] = realpath(APPPATH."/../tmp/".$session->checkSubDirectory())."/";

$config['kastam'] = false;
$config['mobile_document'] = false;

$config['remove_index_php'] = false;


