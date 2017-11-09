<?php
class model {
	function __construct(){

	}

	function insert($data=null,$table=''){
		if( is_array($data) && $table){
			$field = $dataInsert = '';

			foreach ($data AS $key=>$val){
				$field.="$key,";
				$dataInsert.=db_escape($val).",";
			}
			$sql = "INSERT INTO ".TB_PREF."$table (".substr($field, 0, -1).") VALUES ( ".substr($dataInsert, 0, -1).")";
			db_query($sql,"an item $table could not be updated");
		}
	}

	function update($data=null,$table='',$id=0){

		if( is_array($data) && $table){
			$setdata = '';

			foreach ($data AS $key=>$val){
				$setdata.=" `$key`=".db_escape($val).", ";

			}
			$sql = "UPDATE ".TB_PREF."$table SET ".substr($setdata, 0, -2)." WHERE `id` = $id;";

			db_query($sql,"an item $table could not be updated");
		}
	}

	function get_all($table=''){
		if( $table ){
			return db_query('SELECT * FROM `'.TB_PREF.$table.'`',"an item $table could not be updated");
		}

	}

	function get_row($where='',$table=''){

		if( $table ){
			$sql = 'SELECT * FROM `'.TB_PREF.$table.'`';
			if( $where ){
				$sql .=' WHERE '.$where;
			}
			$sql.=' LIMIT 1';
			$resouce = db_fetch( db_query($sql,"an item $table could not be updated") );

			$data = array();

			if($resouce ){
				foreach ($resouce AS $key=>$val){
					if( is_string($key) ){
						$data[$key] = $val;
					}
				}
			}
			return $data;
		}

	}

	function delete($id,$table=''){
		return db_query('DELETE FROM `'.TB_PREF.$table.'` WHERE `id` = '.$id,"an item $table could not be updated");

	}

	function get_trans_no($table='',$trans_field = 'trans_no'){
		$sql = "SELECT MAX($trans_field) AS no FROM $table";
		$resouce = db_fetch( db_query($sql,"an item $table could not be updated") );
		if($resouce && isset($resouce['no']) ){
			return $resouce['no']+1;
		}
	}

	function get_row_id($where='',$table=''){
		$data = self::get_row($where,$table);
		if( isset($data['id']) && $data['id'] )
			return $data['id'];
		else
			return null;
	}
}