<?php
class Product_Model {
	function __construct(){
		global $ci;
		$this->prod = $ci->db;
	}
	
	function get_row($id=0){
		return $this->prod->where('stock_id',$id)->get('stock_master')->row();
	}
	
	function item_options(){
		$category = array();
		$this->prod->select('stock_id, s.description AS name, c.description AS cate_name, c.category_id, s.inactive, s.editable')->order_by('s.description', 'ASC');
		
		$this->prod->from('stock_master s');
		$this->prod->join('stock_category AS c', 's.category_id=c.category_id', 'left');
		$this->prod->where('s.mb_flag !=','D');
		$data = $this->prod->get()->result();
		
		if( $data ){
			
			
			foreach ($data AS $row){
// 				$options[$row->stock_id] = $row->name;
				if( !isset($category[$row->category_id]) ){
					$category[$row->category_id] = array('title'=>$row->cate_name,'items'=>array());
				}
				$category[$row->category_id]['items'][$row->stock_id] = $row->name;
				
			}
		}
// 		bug($category);
		return $category;
	}
	
}