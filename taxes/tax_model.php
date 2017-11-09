<?php
class tax_model {
	function tax_type($id=0){
		$out = array();
		$sql = "SELECT * FROM ".TB_PREF."tbltaxcode WHERE taxcodeid= $id";
		$result = db_query($sql, "could not get tbltaxcode");
		$data =  db_fetch($result);
		if( $data ){
			foreach ($data AS $k=>$val){
				if( is_string($k) ){
					$out[$k] = $val;
				}
			}
		}
// 		bug($id);die;
		return (object)$out;
	}

	function item($id=0){
		$id = intval($id);
		$out = array();
// 		display_error( _("error tax id=".$id));

		if( $id <=0 ){
			//
			return (object)$out;
		}

		$sql = "SELECT * FROM ".TB_PREF."tax_types WHERE id= $id";
		$result = db_query($sql, "could not get tax_types");
		$data =  db_fetch($result);
		if( $data ){
			foreach ($data AS $k=>$val){
				if( is_string($k) ){
					$out[$k] = $val;
				}
			}

			if (($startPos = strpos($out['name'], '(')) !== false && ($endPos = strpos($out['name'], ')', $startPos+=strlen('('))) !== false) {
			    // match found
			    $out['name_code'] = substr($out['name'], $startPos, $endPos-$startPos);
			}
		}



		return (object)$out;
	}



}