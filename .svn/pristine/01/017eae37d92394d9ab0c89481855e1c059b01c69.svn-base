<?php
class update extends ci {
	var $date_from, $date_to;
	function __construct(){
		global $db_connections;

		$this->load_db($db_connections[0]);

	}

	function check(){
	    /*
		self::addFixAsset();
		self::openningDB();
		self::fixMsic();
		self::bankTransDetail();
		self::addAmountDec();
		self::addChequec();
		self::fix150730();
		self::check_cheque('debtor_trans');
		self::check_cheque('supp_trans');

		self::customer_allocate150820();
		self::baddebts();
		self::kastam();

		self::rounding_difference();

		self::form5();
	    */

	}

	function check_cheque($table){

	    if($table){
	        if ( !$this->db->field_exists('cheque', $table)){
	            $this->db->query("ALTER TABLE `$table` ADD `cheque` varchar(50) NULL; ");

	        }
	    }
	}

	function addAmountDec(){
		if ( !$this->db->field_exists('amount_dec', 'users')){
			$this->db->query("ALTER TABLE `users` ADD `amount_dec` TINYINT(1) NOT NULL DEFAULT '2' ");
		}

	}

	function addChequec(){
	    if ( !$this->db->field_exists('cheque', 'bank_trans')){
	        $this->db->query("ALTER TABLE `bank_trans` ADD `cheque` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' ");
	    }

	}

	function addFixAsset(){
		if ( !$this->db->field_exists('fixed_access', 'supp_trans')){
			$this->db->query("ALTER TABLE `supp_trans` ADD `fixed_access` TINYINT(1) NOT NULL DEFAULT '0' ");
		}

	}

	function openningDB(){
		if( !$this->db->table_exists('opening_gl_system') ){
			$this->db->query("CREATE TABLE IF NOT EXISTS `opening_gl_system` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `account` int(10) NOT NULL,
				  `amount` double NOT NULL,
				  `tran_date` date NOT NULL,
				  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `id` (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		}

		if( !$this->db->table_exists('opening_cache') ){
			$this->db->query("CREATE TABLE IF NOT EXISTS `opening_cache` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `data` text NOT NULL,
							  PRIMARY KEY (`id`),
							  KEY `id` (`id`)
							) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		}

		if( !$this->db->table_exists('opening_gl') ){
			$this->db->query("CREATE TABLE IF NOT EXISTS `opening_gl` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `pay_type` char(15) NOT NULL,
								  `type` char(20) NOT NULL,
								  `account` char(20) NOT NULL,
								  `amount` double NOT NULL,
								  `tran_date` date NOT NULL,
								  `gl_tran_id` int(11) NOT NULL,
								  PRIMARY KEY (`id`),
								  KEY `id` (`id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		}

		if( !$this->db->table_exists('opening_sale') ){
			$this->db->query("CREATE TABLE IF NOT EXISTS `opening_sale` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `type` char(15) DEFAULT 'sale',
				  `customer` int(11) DEFAULT NULL,
				  `branch` int(11) DEFAULT NULL,
				  `ref` varchar(40) DEFAULT NULL,
				  `trans_no` int(11) DEFAULT NULL,
				  `tran_date` date NOT NULL,
				  `currency` char(10) DEFAULT NULL,
				  `curr_rate` double NOT NULL,
				  `amount` double DEFAULT NULL,
				  `payment` double DEFAULT NULL,
				  `debit` double NOT NULL,
				  `credit` double NOT NULL,
				  `status` bit(1) DEFAULT b'0',
				  PRIMARY KEY (`id`),
				  KEY `id` (`id`)

				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		}

		$this->db->delete('opening_sale',array('id'=>0));
        $this->db->query('ALTER TABLE `opening_sale` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;');



		if ( !$this->db->field_exists('debit', 'opening_sale')){
		    $this->db->query(" ALTER TABLE `opening_sale` ADD `debit` double NOT NULL DEFAULT '0'; ");
		}
		if ( !$this->db->field_exists('credit', 'opening_sale')){
		    $this->db->query(" ALTER TABLE `opening_sale` ADD `credit` double NOT NULL DEFAULT '0'; ");
		}
		if ( !$this->db->field_exists('curr_rate', 'opening_sale')){
		    $this->db->query(" ALTER TABLE `opening_sale` ADD `curr_rate` double NOT NULL DEFAULT '1'; ");
		}


		if( !$this->db->table_exists('opening_product') ){
			$this->db->query("CREATE TABLE IF NOT EXISTS `opening_product` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `code` char(20) NOT NULL,
						  `name` varchar(255) DEFAULT NULL,
						  `cost` int(20) NOT NULL,
						  `qty` int(10) NOT NULL,
						  `price` int(20) NOT NULL,
						  PRIMARY KEY (`id`),
						  KEY `id` (`id`),
						) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");


		}

		if ( !$this->db->field_exists('openning', 'gl_trans')){
			$this->db->query("ALTER TABLE `gl_trans` ADD `openning` varchar(255) NOT NULL DEFAULT '' ");
		}

		// ALTER TABLE `gl_trans` ADD `hidden` TINYINT(1) NOT NULL DEFAULT '0' AFTER `openning`;

		if( !$this->db->table_exists('opening_sale_item') ){
		    $this->db->query("CREATE TABLE IF NOT EXISTS `opening_sale_item` (
                  `id` int(11) NOT NULL,
                  `sale_id` int(11) NOT NULL,
                  `description` tinytext NOT NULL,
                  `quantity` double NOT NULL,
                  `discount_percent` int(2) DEFAULT NULL,
                  `tax_type_id` int(11) DEFAULT NULL,
                  `unit_price` double DEFAULT NULL,
                  `currency` char(10) DEFAULT NULL,
                  `credit` double NOT NULL DEFAULT '0',
                  `debit` double NOT NULL DEFAULT '0'
                )  ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		    $this->db->query("ALTER TABLE `opening_sale` ADD INDEX(`id`);");
		    $this->db->query("ALTER TABLE `opening_sale` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;");
		}
		$this->db->query('ALTER TABLE `opening_sale_item` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;');

		if ( !$this->db->field_exists('gl_tran_id', 'opening_product')){
		    $this->db->query("ALTER TABLE `opening_product` ADD `gl_tran_id` INT(11) NOT NULL DEFAULT '0' ");
		}
		if ( !$this->db->field_exists('trans_no', 'opening_product')){
		    $this->db->query("ALTER TABLE `opening_product` ADD `trans_no` INT(11) NOT NULL DEFAULT '0' ");
		}




	}

	function fixMsic(){
		$this->db->query("ALTER TABLE `debtors_master` CHANGE `msic` `msic` CHAR(10) NOT NULL;");
	}

	function bankTransDetail(){
		if( !$this->db->table_exists('bank_trans_detail') ){
			$this->db->query("CREATE TABLE IF NOT EXISTS `bank_trans_detail` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `type` int(11) NOT NULL,
				  `account_code` char(10) NOT NULL,
				  `amount` double NOT NULL,
				  `trans_no` int(11) NOT NULL,
				  `currence` char(50) NOT NULL,
				  `currence_rate` double NOT NULL,
				  `tax` int(11) NOT NULL,
					PRIMARY KEY (`id`),
					  KEY `id` (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");


		}
		if ( !$this->db->field_exists('tax_inclusive', 'bank_trans')){
			$this->db->query(" ALTER TABLE `bank_trans` ADD `tax_inclusive` TINYINT(1) NOT NULL DEFAULT '0' AFTER `bank_act`; ");
		}

	}

	function fix150730(){
	    $this->db->query("ALTER TABLE `trans_tax_details` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;");
	}

	function customer_allocate150820(){
	    $customer_allocates =  $this->db->where(array('trans_type_to'=>10,'trans_type_from'=>12))->get('cust_allocations')->result();
        if( $customer_allocates && count($customer_allocates) >0 ) foreach ($customer_allocates AS $allo){
            $this->db->reset();
            $invoice = $this->db->where( array('trans_no'=>$allo->trans_no_to,'type'=>$allo->trans_type_to,'alloc'=>0) )->get('debtor_trans')->row();
            if( $invoice ){
            }

        }
	}

	function baddebts(){

        $accounts = array('baddeb_sale_reverse'=>4015,'baddeb_sale_tax_reverse'=>'A2150','baddeb_purchase_reverse'=>5620,'baddeb_purchase_tax_reverse'=>'A1300','baddeb_sale_tax'=>35,'baddeb_purchase_tax'=>25);
        foreach ($accounts AS $gl=>$acc){
            $exited = $this->db->where('name',$gl)->get('sys_prefs')->row();
            if( empty($exited) ){
                $this->db->insert('sys_prefs',array('name'=>$gl,'value'=>$acc));
            }
        }
        if( !$this->db->table_exists('bad_debts') ){
            $this->db->query("CREATE TABLE IF NOT EXISTS `bad_debts` (
                  `id` int(11) NOT NULL AUTO_INCREMENT ,
                  `type` smallint(6) NOT NULL,
                  `type_no` int(16) NOT NULL,
                  `tran_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `step` tinyint(2) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`), KEY `id` (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
        }
	}

	private function kastam(){

	    $this->db->where('name', 'curr_default')->where('value IS NULL')->update('sys_prefs', array('value'=>'MYR'));
// 	    bug( $this->db->last_query() );die;
        // 1.	COMPANY GST REGISTRATION DATE
	    $accounts = array('gst_start_date'=>date('Y-m-d'),'gst_default_code'=>null);
	    foreach ($accounts AS $gl=>$acc){
	        $exited = $this->db->where('name',$gl)->get('sys_prefs')->row();
	        if( empty($exited) ){
	            $this->db->insert('sys_prefs',array('name'=>$gl,'value'=>$acc));
	        }
	    }

	    // 2.	SUPPLIER VERIFIED DATE
	    if ( !$this->db->field_exists('valid_gst', 'suppliers')){
	        $this->db->query(" ALTER TABLE `suppliers` ADD `valid_gst` TINYINT(1) NOT NULL DEFAULT '1'; ");
	    }
	    if ( !$this->db->field_exists('last_verifile', 'suppliers')){
	        $this->db->query(" ALTER TABLE `suppliers` ADD `last_verifile` DATE NULL DEFAULT NULL; ");
	    }

	    // 3.	SELF BILL INVOICE
	    // 8.	MAXIMUM CLAIMABLE INPUT TAX FOR SIMPLIFIED TAX INVOICE
	    $self_bill = array( 'self_bill_approval_ref','self_bill_start_date','self_bill_end_date','maximum_claimable_currency','maximum_claimable_input_tax');
	    foreach ($self_bill AS $field){
	        $exited = $this->db->where('name',$field)->get('sys_prefs')->row();
	        if( empty($exited) ){
	            $this->db->insert('sys_prefs',array('name'=>$field,'value'=>null));
	        }
	    }
	    if ( !$this->db->field_exists('self_bill', 'suppliers')){
	        $this->db->query(" ALTER TABLE `suppliers` ADD `self_bill`  TINYINT(1) NULL DEFAULT 0; ");
	    }
	    if ( !$this->db->field_exists('self_bill_approval_ref', 'suppliers')){
	        $this->db->query(" ALTER TABLE `suppliers` ADD `self_bill_approval_ref`  varchar(255) NULL DEFAULT NULL; ");
	    }

	    // 9.	Debit/credit note mandatory reason
	    if ( !$this->db->field_exists('reason', 'debtor_trans')){
	        $this->db->query(" ALTER TABLE `debtor_trans` ADD `reason`  varchar(255) NULL DEFAULT NULL; ");
	    }

	    if ( !$this->db->field_exists('reason', 'supp_trans')){
	        $this->db->query(" ALTER TABLE `supp_trans` ADD `reason`  varchar(255) NULL DEFAULT NULL; ");
	    }

	    // 14.	IMPORTED GOODS FROM OVERSEA
	    if ( !$this->db->field_exists('imported_goods', 'supp_trans')){
	        $this->db->query(" ALTER TABLE `supp_trans` ADD `imported_goods`  TINYINT(1) NULL DEFAULT 0; ");
	    }
	    if ( !$this->db->field_exists('paid_tax', 'supp_trans')){
	        $this->db->query(" ALTER TABLE `supp_trans` ADD `paid_tax`  TINYINT(1) NULL DEFAULT 0; ");
	    }



	}

	function rounding_difference(){
        $acc4451 = $this->db->where('account_code',4451)->get('chart_master')->row();
        if( empty($acc4451) ){
            $this->db->insert('chart_master',array('account_code'=>4451,'account_name'=>'Rounding Difference','account_type'=>9,'inactive'=>0));
        }
//         bug($this->db->last_query() );die;
        $this->db->reset();
        $system_config = $this->db->where('name','rounding_difference_act')->get('sys_prefs')->row();
        if( empty($system_config) ){
            $this->db->insert('sys_prefs',array('name'=>'rounding_difference_act','value'=>4451));
        }
	}

	private function form5(){
	    if ( !$this->db->field_exists('permit', 'supp_trans')){
	        $this->db->query(" ALTER TABLE `supp_trans` ADD `permit`  varchar(100) NULL DEFAULT NULL; ");
	    }

	    if( !$this->db->table_exists('source_reference') ){

	        $this->db->query("CREATE TABLE IF NOT EXISTS `source_reference` (
                  `id` int(11) NOT NULL,
                  `trans_type` tinyint(5) DEFAULT NULL,
                  `trans_no` tinyint(11) DEFAULT NULL,
                  `reference` varchar(50) DEFAULT NULL
                ) ENGINE=InnoDB AUTO_INCREMENT=1;");
	        $this->db->query(" ALTER TABLE `source_reference` ADD PRIMARY KEY (`id`); ");
	        $this->db->query(" ALTER TABLE `source_reference` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1; ");

	    }
	}
}