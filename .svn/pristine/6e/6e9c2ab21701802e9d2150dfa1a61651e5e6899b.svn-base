<?php defined('BASEPATH') OR exit('No direct script access allowed');

class pdf {

	function pdf(){

	}

	function load($param=NULL){
		include_once BASEPATH.'libraries/mpdf60/mpdf.php';

		if ($params == NULL) {
			$param = '"utf-8","A4",0,"",10,10,10,10,6,3';
		}

		return new mPDF($param);
	}
}