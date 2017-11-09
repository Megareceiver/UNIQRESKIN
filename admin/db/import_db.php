<?php

include $path_to_root.'/includes/phpexcel.php';

class import_data {
	function __construct(){
		global $ci;
		$this->ci = $ci;

		if( !$this->ci->db->table_exists('import_process') ){
			$this->ci->db->query("CREATE TABLE IF NOT EXISTS `import_process` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `module` char(100) NOT NULL,
			  `total` int(10) NOT NULL,
			  `complate` int(10) NOT NULL,
			  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `file` varchar(100) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `id` (`id`),
			  KEY `id_2` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
		}

	}

	var $sheetTaget = 0;

	var $customer = null;
	function readfile($file=''){
		$filename = $file;
		$file = company_path()."/import/".$file;
		if( file_exists($file) && is_file($file) ){

			$this->obj = PHPExcel_IOFactory::load($file);

			foreach ($this->obj->getSheetNames() AS $index=>$name){
				$this->obj->setActiveSheetIndex($index);
				switch ($name){
					case 'Customer': $this->customer =  $this->obj->getActiveSheet(); break;
					case 'Customer Branch': $this->brancher = $this->obj->getActiveSheet(); break;
					case 'Supplier': $this->supplier = $this->obj->getActiveSheet(); break;
					case 'Supplier Contact': $this->supplier_contact = $this->obj->getActiveSheet(); break;
					case 'Product': $this->product = $this->obj->getActiveSheet(); break;
					case 'Sales Man': $this->saleman = $this->obj->getActiveSheet(); break;
					case 'Units of Measure': $this->units = $this->obj->getActiveSheet(); break;
					case 'Area': $this->area = $this->obj->getActiveSheet(); break;
					case 'Customer Contact': $this->contact = $this->obj->getActiveSheet(); break;
					case 'Instruction': $this->instruction = $this->obj->getActiveSheet(); break;
					// 					case 'tax type': $this->tax_type = $this->obj->getActiveSheet(); break;
					default : break;
				}
			}
		}

		if( isset($this->supplier) && $this->supplier->getHighestRow() > 1 ){
			$this->ci->db->insert('import_process',array('module'=>'supplier','total'=>$this->supplier->getHighestRow()-1,'complate'=>0,'file'=>$filename));
		}

		if( isset($this->supplier_contact) && $this->supplier_contact->getHighestRow() > 1 ){
			$this->ci->db->insert('import_process',array('module'=>'supplier_contact','total'=>$this->supplier_contact->getHighestRow()-1,'complate'=>0,'file'=>$filename));
		}


		if( isset( $this->product) && $this->product->getHighestRow() > 1 ){
			$this->ci->db->insert('import_process',array('module'=>'product','total'=>$this->product->getHighestRow()-1,'complate'=>0,'file'=>$filename));
		}


		if( isset($this->customer) && $this->customer->getHighestRow() > 1 ){
			//$this->importCustomer();
			$this->ci->db->insert('import_process',array('module'=>'customer','total'=>$this->customer->getHighestRow()-1,'complate'=>0,'file'=>$filename));
		}

		if( isset($this->brancher) && $this->brancher->getHighestRow() > 1 ){
			$this->ci->db->insert('import_process',array('module'=>'brancher','total'=>$this->brancher->getHighestRow()-1,'complate'=>0,'file'=>$filename));
		}

		if( isset($this->contact) && $this->contact->getHighestRow() > 1 ){
			$this->ci->db->insert('import_process',array('module'=>'contact','total'=>$this->contact->getHighestRow()-1,'complate'=>0,'file'=>$filename));
		}
/*
		$this->checkCustomerBrancher();
*/
		return true;
	}
/*
	private function excelVal($index,$sheet=null){
		if( $sheet !=null ){
			//$value = strtolower(trim( $sheet->getCell($index)->getCalculatedValue() ));
			$value = trim( $sheet->getCell($index)->getCalculatedValue() );
		} else {
			$value = null;
		}
		if ( $value =='-' ){
			$value = null;
		}
		return $value;
	}
*/
	var $customerField = array(
	// 		'id'=>'A',
			'name'=>'A',
			'debtor_ref'=>'B',
			'address'=>'C',
			'tax_id'=>'D',
			'curr_code'=>'E',
			'sales_type'=>'F',
			// 		'dimension_id'=>'H',
	// 		'dimension2_id'=>'I',
			'credit_status'=>'I',
			'payment_terms'=>'J',
			'discount'=>'K',
			'pymt_discount'=>'L',
			'credit_limit'=>'M',
			'notes'=>'N',
			//'inactive'=>'P',
			'customer_tax_id'=>'P',
			'industry_code'=>'Q',
			//'branch'=>'L',
	);

	private function add_brancher($customer_name='',$debtor_id=0){
		if( !$customer_name || !$debtor_id ) return;

		$branchers = $this->ci->db->where('debtor_no',$customer_name)->get('import_brancher')->result_array();

		foreach ($branchers AS $bran){
			if( $bran['salesman'] ){
				$saleman = $this->ci->db->where('salesman_name',$bran['salesman'])->get('import_saleman')->row_array();
				unset($saleman['id']);

				$salesman_duplicate = $this->ci->db->where('salesman_name',$saleman['salesman_name'])->get('salesman')->row();
				if( $salesman_duplicate ){
					$bran['salesman'] = $salesman_duplicate->salesman_code;
				} else {
					$this->ci->db->insert('salesman', $saleman);
					$bran['salesman'] = $this->ci->db->insert_id();
				}

				if( !$bran['salesman'] ){
					$bran['salesman'] = 1;
				}


			}
			unset($bran['id']);
			$bran['debtor_no'] = $debtor_id;
			$this->ci->db->insert('cust_branch', $bran);
		}

	}

	private function add_contact($customer_name='',$debtor_id=0,$type='customer'){
		if( !$customer_name || !$debtor_id ) return;

		$contacts_ref = $this->ci->db->where('entity_id',$debtor_id)->get('crm_contacts')->result();
		$person_dupplicate = array();
		if( $contacts_ref ){
			foreach ($contacts_ref AS $conta){
				$person_dupplicate[] = $conta->person_id;
			}
		}

		$table = ( $type=='supplier' ) ? 'import_supplier_contact' : 'import_contact';


		$contacts = $this->ci->db->where('customer',$customer_name)->get($table)->result_array();

		if ( !empty($contacts) ){
			foreach ($contacts AS $contact){
				unset($contact['customer']);
				unset($contact['id']);
				$person_exit = 0;

				if( !empty($person_dupplicate) ){
					$items_exist = $this->ci->db->where('name',$contact['name'])->where_in('id',$person_dupplicate)->get('crm_persons')->row();
					if($items_exist){
						$person_exit = $items_exist->id;
					}
				}

				if( $person_exit ){
					$this->ci->db->where('id', $person_exit)->update('crm_persons', $contact);
					$person_id = $person_exit;
				} else {
					$this->ci->db->insert('crm_persons', $contact);
					$person_id = $this->ci->db->insert_id();
				}

				$ref = $this->ci->db->where(array('person_id'=>$person_id,'type'=>$type,'action'=>'general','entity_id'=>$debtor_id))->get('crm_contacts')->row();
				if( !$ref ) {
					$this->ci->db->insert('crm_contacts',  array('person_id'=>$person_id,'type'=>$type,'action'=>'general','entity_id'=>$debtor_id));
				}


			}
		}
	}
/*
	function add_supplier_contact($customer_name='',$supplier_id=0){
		if( !$customer_name || !$supplier_id ) return;

		$contacts_ref = $this->ci->db->where('entity_id',$supplier_id)->get('crm_contacts')->result();
		$person_dupplicate = array();
		if( $contacts_ref ){
			foreach ($contacts_ref AS $conta){
				$person_dupplicate[] = $conta->person_id;
			}
		}



		$contacts = $this->ci->db->where('customer',$customer_name)->get('import_contact')->result_array();

		if ( !empty($contacts) ){
			foreach ($contacts AS $contact){
				unset($contact['customer']);
				unset($contact['id']);
				$person_exit = 0;
				if( !empty($person_dupplicate) ){
					$items_exist = $this->ci->db->where('name',$contact['name'])->where_in('id',$person_dupplicate)->get('crm_persons')->row();
					if($items_exist){
						$person_exit = $items_exist->id;
					}
				}
				if( $person_exit ){
					$this->ci->db->where('id', $person_exit)->update('crm_persons', $contact);
					$person_id = $person_exit;
				} else {
					$this->ci->db->insert('crm_persons', $contact);
					$person_id = $this->ci->db->insert_id();
				}

				$this->ci->db->insert('crm_contacts', array('person_id'=>$person_id,'type'=>'customer','action'=>'general','entity_id'=>$debtor_id) );

			}
		}
	}
	*/

	function add_customer($cache_id=0){
		$new_data = $this->ci->db->where('id',$cache_id)->get('import_customer')->row_array();

		if( $new_data['sales_type'] ){
			switch ($new_data['sales_type']){
				case 'wholesale': $new_data['sales_type'] = 2; break;
				default: $new_data['sales_type'] = 2; break;
			}
		}
		if( $new_data['curr_code'] ){
			$new_data['curr_code'] = strtoupper($new_data['curr_code']);
		} else {
			$new_data['curr_code'] = 'USD';
		}

		$old_id = $new_data['id'];
		unset( $new_data['id'] );
		unset( $new_data['created'] );

		$duplicate = $this->ci->db->where('debtor_ref',$new_data['debtor_ref'])->get('debtors_master')->row();

		if( $duplicate ){
			$this->ci->db->where('debtor_ref', $new_data['debtor_ref'])->update('debtors_master', $new_data);

			$debtor_id = $duplicate->debtor_no;
		} else {
			$this->ci->db->insert('debtors_master', $new_data);
			$debtor_id = $this->ci->db->insert_id();
		}


		if( $this->ci->db->table_exists('import_brancher') ){
			self::add_brancher($new_data['name'],$debtor_id);
		}

		if( $this->ci->db->table_exists('import_contact') ){
			self::add_contact($new_data['name'],$debtor_id);
		}



		if( empty($branchers) ){
			$bran_default = array(
				'debtor_no'=> $debtor_id,
				'br_name'=>$new_data['name'],
				'branch_ref'=>$new_data['debtor_ref'],
				'area'=>1,
				'default_location'=>'DEF',
				'tax_group_id'=>1,
				'salesman'=>1
			);
			$this->ci->db->insert('cust_branch', $bran_default);
		}
		$this->ci->db->where('id', $old_id)->delete('import_customer');
	}

	function checkCustomerBrancher(){
		$addNew = 0;

		for( $line=2;$line <= $this->customer->getHighestRow(); $line++){
			$row = array();

			$tax = self::excelVal($this->customerField['name'].$line,$this->customer);
			$sqlGET="SELECT * FROM ".TB_PREF."debtors_master WHERE name=".db_escape($tax);
			$customer =  db_fetch(db_query($sqlGET,"an debtors_master could not be retrieved"));
			$salesmanQuery = db_fetch(db_query('SELECT MAX(salesman_code) AS id FROM `salesman` ',"an debtors_master could not be retrieved"));

			$row['branch_ref'] = self::excelVal($this->customerField['debtor_ref'].$line,$this->customer);



			if( $customer ){
				$sqlCheckExist = "SELECT * FROM ".TB_PREF."cust_branch WHERE branch_ref=".db_escape($row['branch_ref']).' AND debtor_no='.db_escape($customer['debtor_no']);

				$existed =  db_fetch(db_query($sqlCheckExist,"an suppliers could not be retrieved"));

				if( !isset($existed['branch_code']) ){
// 					bug($existed);die;
					$row['debtor_no'] = $customer['debtor_no'];
					$row['salesman'] = $salesmanQuery['id'];
					$row['br_name'] = self::excelVal($this->customerField['name'].$line,$this->customer);
					$row['default_location'] = 'DEF';
					$row['tax_group_id'] = 1;
					$row['area'] = 1;

					self::sqlInsert($row,'cust_branch');
				}



			}
		}

	}

	var $unitsField = array(
			'abbr'=>'A',
			'description'=>'B',
			'decimals'=>'C',
	);

	function importUnits(){
		$addNew = 0;
		for( $line=4;$line <= $this->units->getHighestRow(); $line++){
			$row = array();
			foreach ($this->unitsField AS $key=>$value){
				$row[$key] = self::excelVal($value.$line,$this->units);
			}
			$sqlGET="SELECT * FROM ".TB_PREF."item_units WHERE abbr=".db_escape($row['abbr']);
			$existed =  db_fetch(db_query($sqlGET,"an unit of measure could not be retrieved"));
			if( !$existed ){
				$sqlNew = "INSERT INTO ".TB_PREF."item_units (abbr, name, decimals) VALUES ( ".db_escape($row['abbr']).",".db_escape($row['description']).", ".db_escape($row['decimals']).")";
				db_query($sqlNew,"an item unit could not be updated");
				$addNew++;
			}
		}
		if( $addNew > 0 ){
			display_notification_centered(_("Success import $addNew unit of measure"));
		}
	}

	function importSaleman(){
		$fields = array('salesman_name'=>'B','salesman_phone'=>'C','salesman_fax'=>'D','salesman_email'=>'E');
		$addNew = 0;
		for( $line=2;$line <= $this->saleman->getHighestRow(); $line++){
			$row = array();
			foreach ($fields AS $key=>$value){
				$row[$key] = self::excelVal($value.$line,$this->saleman);
			}
			$sqlGET="SELECT * FROM ".TB_PREF."salesman WHERE salesman_email=".db_escape($row['salesman_email']);
			$existed =  db_fetch(db_query($sqlGET,"an unit of salesman could not be retrieved"));
			if( !$existed ){
				$sqlNew = "INSERT INTO ".TB_PREF."salesman (salesman_name, salesman_phone, salesman_fax, salesman_email) VALUES
				( ".db_escape($row['salesman_name']).",".db_escape($row['salesman_phone']).", ".db_escape($row['salesman_fax']).", ".db_escape($row['salesman_email']).")";
				db_query($sqlNew,"an item unit could not be updated");
				$addNew++;
			}
		}
		if( $addNew > 0 ){
			display_notification_centered(_("Success import $addNew SalesMan"));
		}
	}

	private function getSalemanExcel($id=0){
		if( $id ){
			for( $line=2;$line <= $this->saleman->getHighestRow(); $line++){
				if( self::excelVal("A$line",$this->saleman) == $id){
					$sqlGET="SELECT * FROM ".TB_PREF."salesman WHERE salesman_email=".db_escape(self::excelVal("E$line",$this->saleman));
					$existed =  db_fetch(db_query($sqlGET,"an unit of salesman could not be retrieved"));
					if( $existed  ){
						return $existed['salesman_code'];
					}
				}
			}
		}

	}

	var $brancherField = array(
	// 		'id'=>'A',
			'br_name'=>'B',
			'branch_ref'=>'C',
			'salesman'=>'D',
			'area'=>'E',
			'group_no'=>'F',
			'default_location'=>'G',
			'default_ship_via'=>'H',
			'br_post_address'=>'I',
			'br_address'=>'J',
			'notes'=>'K',
			'debtor_no'=>'L'
	);
	function importBrancher(){
		$addNew = 0;
		for( $line=2;$line <= $this->brancher->getHighestRow(); $line++){
			$row = array();
			foreach ($this->brancherField AS $key=>$value){
				$row[$key] = self::excelVal($value.$line,$this->brancher);
			}
			$sqlGET="SELECT * FROM ".TB_PREF."cust_branch WHERE branch_ref=".db_escape($row['branch_ref']);
			$existed =  db_fetch(db_query($sqlGET,"an unit of cust_branch could not be retrieved"));
			if( !$existed ){
				if( isset($row['debtor_no']) ){

// 					$sqlCustomerGET="SELECT * FROM ".TB_PREF."debtors_master WHERE tax_id=".db_escape($row['debtor_no']);
					$sqlCustomerGET="SELECT * FROM ".TB_PREF."debtors_master WHERE name=".db_escape($row['debtor_no']);
					$customer =  db_fetch(db_query($sqlCustomerGET,"an debtors_master could not be retrieved"));
					if($customer && isset($customer['debtor_no']) ){
						$row['debtor_no'] = $customer['debtor_no'];
						if( $row['salesman'] ){
							$row['salesman'] = self::getSalemanExcel($row['salesman']);
						} else {
							$salesmanQuery = db_fetch(db_query('SELECT MAX(salesman_code) AS id FROM `salesman` ',"an debtors_master could not be retrieved"));
							$row['salesman'] = $salesmanQuery['id'];

						}
						$row['area'] = 1;
						if( $row['group_no'] ){
							$row['group_no'] = strtolower($row['group_no']);
							switch ($row['group_no']){
								case 'large': $row['group_no'] = 3; break;
								case 'medium': $row['group_no'] = 2; break;
								case 'small': $row['group_no'] = 1; break;
								default: $row['group_no'] = ''; break;
							}
						}
						$row['default_location'] = 'DEF';
						$row['default_ship_via'] = '';
						$row['tax_group_id'] = 1;
						self::sqlInsert($row,'cust_branch');
						$addNew++;
					}


				}

			}
		}
		if( $addNew > 0 ){
			display_notification_centered(_("Success import $addNew Branches"));
		}

	}

	var $supplierField = array(
			'supp_name'=>'A',
			'supp_ref'=>'B',
			'gst_no'=>'C',
			'website'=>'D',
			'curr_code'=>'E',
			'supp_account_no'=>'F',
			'bank_account'=>'G',
			'credit_limit'=>'H',
			'payment_terms'=>'I',
			'tax_included'=>'J',
			'payable_account'=>'K',
			'purchase_account'=>'L',
			'payment_discount_account'=>'M',
			'address'=>'N',
			'supp_address'=>'O',
			'notes'=>'P',
	);

	function add_supplier($cache_id=0){
		$new_data = $this->ci->db->where('id',$cache_id)->get('import_supplier')->row_array();

		if( !$new_data['payable_account'] ){
			$new_data['payable_account'] = self::getField(array('name'=>'creditors_act','category'=>'glsetup.purchase'),'value','sys_prefs');
		}
		if( !$new_data['payment_discount_account'] ){
			$new_data['payment_discount_account'] = self::getField(array('name'=>'pyt_discount_act','category'=>'glsetup.purchase'),'value','sys_prefs');
		}

		$old_id = $new_data['id'];
		unset( $new_data['id'] );
		unset( $new_data['created'] );

		if( isset($new_data['supp_ref']) ) {
			$duplicate = $this->ci->db->where('supp_ref',$new_data['supp_ref'])->get('suppliers')->row();

			if( $duplicate && isset($duplicate->supp_ref) ){
				$supplier_id = $duplicate->supplier_id;
				$this->ci->db->where('supp_ref', $new_data['supp_ref'])->update('suppliers', $new_data);
			} else {
				$this->ci->db->insert('suppliers', $new_data);
				$supplier_id = $this->ci->db->insert_id();
			}

			if( $this->ci->db->table_exists('import_supplier_contact') ){
				self::add_contact($new_data['supp_name'],$supplier_id,'supplier');

			}

			$this->ci->db->where('id', $old_id)->delete('import_supplier');
		}

	}


	var $productField = array(
			'stock_id'=>'A',
			'description'=>'B',
			'long_description'=>'C',
			'category_id'=>'D',
			'units'=>'F',
			'mb_flag'=>'E',

			'editable'=>'G',
			'no_sale'=>'H',

			'sales_gst_type'=>'I',
			'purchase_gst_type'=>'J',
			'sales_account'=>'K',
			'inventory_account'=>'L',
			'cogs_account'=>'M',
			'adjustment_account'=>'N',


	);


	function add_product($cache_id=0){
		$new_data = $this->ci->db->where('id',$cache_id)->get('import_products')->row_array();

		if( $new_data['category_id'] ){

			$new_data['category_id'] = trim(strtolower($new_data['category_id']));

			if( !is_int($new_data['category_id'] ) ){
				$category = $this->ci->db->where('description',$new_data['category_id'])->get('stock_category')->row();
				if( !$category ){
					$category_new = array(
						'description'=>$new_data['category_id'],
						'dflt_sales_act'=>$new_data['sales_account'],
						'dflt_cogs_act'=>$new_data['cogs_account'],
						'dflt_inventory_act'=>$new_data['inventory_account'],
						'dflt_adjustment_act'=>$new_data['adjustment_account'],
					);
					$this->ci->db->insert('stock_category', $category_new);
					$new_data['category_id'] = $this->ci->db->insert_id();
				} else {
					$new_data['category_id'] = $category->category_id;
				}
			}

		}

		if( !$new_data['category_id'] ) {
			$new_data['category_id'] = 1;
		}
		$old_id = $new_data['id'];
		unset( $new_data['id'] );

		if( $new_data['units'] ){
			$system_unit = $this->ci->db->where('abbr',$new_data['units'])->get('item_units')->row();
			if( !$system_unit ){
				$new_unit = $this->ci->db->where('abbr',$new_data['units'])->get('import_units')->row_array();
				if( $new_unit ){
					$this->ci->db->insert('item_units', $new_unit);
					$new_data['units'] =$new_unit['abbr'];
				}
			} else {
				$new_data['units'] =$system_unit->abbr;
			}
		}



		$duplicate = $this->ci->db->where('stock_id',$new_data['stock_id'])->get('stock_master')->row();
		if( $duplicate && isset($duplicate->stock_id) ){
			$this->ci->db->where('stock_id', $new_data['stock_id'])->update('stock_master', $new_data);
		} else {
			$this->ci->db->insert('stock_master', $new_data);
		}

		$item_code = array(
				'item_code'=>$new_data['stock_id'],
				'stock_id'=>$new_data['stock_id'],
				'description'=>$new_data['description'],
				'category_id'=>$new_data['category_id'],
				'quantity'=>0,
				'is_foreign'=>0
		);

		$item_code_duplicate = $this->ci->db->where('stock_id',$new_data['stock_id'])->get('item_codes')->row();
		if( $item_code_duplicate && isset($duplicate->stock_id) ){
			$this->ci->db->where('stock_id', $item_code['stock_id'])->update('item_codes', $item_code);
		} else {
			$this->ci->db->insert('item_codes', $item_code);
		}


		$this->ci->db->where('id', $old_id)->delete('import_products');
	}
}