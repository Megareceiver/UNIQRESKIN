<?php
class Common_Model {
	function __construct(){
		global $ci;
		$this->com = $ci->db;
	}

	function get_comments($type, $type_no){
		$str_return = "";

		$items = $this->com->where( array('type'=>$type, 'id'=>$type_no) )->get('comments')->result();

		if( $items && !empty($items) ){
			foreach ($items AS $ite){
				$str_return .= $ite->memo_;
			}
		}

		return $str_return;
	}

	function where_not_voided(){

	}

	function update($data,$table,$where=null,$do_ci=true,$update_limit =1) {
	    $this->com->reset();
        if( !empty($where) && $this->get_row($where,$table) ){

            if( $do_ci ){
                $this->com->update($table,$data,$where);
            } else {
                $sql = $this->com->update($table,$data,$where,$update_limit,true);
            }

        } else {
            if( is_array($where) && !empty($where) ){
                $data = array_merge($data,$where);
            }

// die('go to insert');
            if( $do_ci ){
                 $this->com->insert($table,$data);
            } else {
                $sql =  $this->com->insert($table,$data,true);
            }
        }
        if(  !$do_ci ){
//             bug(' sql do update = '.$sql);
            db_query($sql,"The $table could not be updated" );
        }
	}

	function delete($table = '', $where = '', $limit = 0 ,$do_ci=true){
	    $this->com->reset();
	    if( $do_ci ){
	        $this->com->delete($table, $where, $limit);
	    } else {

	        $sql =  $this->com->delete($table, $where, $limit,TRUE,true);
	        db_query($sql,"The $table could not be updated" );
	    }
// 	    bug($sql);
// 	    die('remove db');

	}

	function get_row($where='',$table=''){
	    if( $table ){
	        $this->com->reset();
	        if( $where ){
	            $this->com->where($where);
	        }
            $data = $this->com->limit(1)->get($table);//->row();
// bug($this->com->last_query() );
            return $data->row();
	    }

	}


	function stock_move_update($data=array()){
	    if( !array_key_exists('trans_no', $data) ){
            return false;
	    } else if( !array_key_exists('stock_id', $data) ) {
	        return false;
	    } else if ( !array_key_exists('type', $data) ) {
	        return false;
	    }

	    $where = array('trans_no'=>$data['trans_no'],'stock_id'=>$data['stock_id'],'type'=>$data['type']);
	    $row_exits = $this->com->where($where)->get('stock_moves')->row();
// 	    bug(  $this->com->last_query() );
	    $this->com->reset();
// 	    bug($data);
	    if( $row_exits && isset($row_exits->trans_no) ){
	        $this->com->update('stock_moves',$data,$where);

	    } else {
// 	        $this->com->insert($data,'stock_moves');
	        $this->com->insert('stock_moves',$data);

// 	        bug( $this->com->last_query() );die;
	    }

// 	    bug(  $this->com->last_query() );
	}

	function update_material_cost($stock_id=0){
	    $standard = 0;
	    $this->com->from('stock_moves')->where('stock_id',$stock_id);
	    $this->com->select('SUM(price), SUM(qty), SUM(price)/SUM(qty) AS standard',false);

	    $data = $this->com->get()->row();

	    if( is_object($data) ){
	        $this->com->where('stock_id',$stock_id)->update('stock_master',array('material_cost'=>$data->standard));
	        $standard = $data->standard;
	    }

	    return $standard;


	}
}