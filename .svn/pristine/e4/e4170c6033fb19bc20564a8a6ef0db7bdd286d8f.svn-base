jQuery(function() {
	//sale.ini();
	//alert('callme');
});

var openning = {
	ini: function(){
		jQuery(function() {
			openning.load_branch();

			$('select[name=currency],input[name=curr_rate], input[name=debit], input[name=credit]').change(function(){
				openning.exchange_money();
				openning.input_credit_debit();
			});
		});



	},

	load_branch_ajax : function(customer_id){
		if( !customer_id ){
			customer_id =$('select[name=customer]').val();
		}
		if(customer_id){
			$.ajax({
				  url: '/admin/opening_balance/customer.php',
				  data:{'cus':customer_id,'date':$('input[name=tran_date]').val()},
				  success: function(data){
					  var input_branch = $('select[name=branch]');
					  input_branch.find('option').remove();
					  if(data.branches){
						  $.each(data.branches,function(key, value) {
							  input_branch.append($("<option/>", {value: key,text: value}));
						  });
					  }

					  if(data.curr){
						  $('select[name="currency"] option[value='+data.curr+']').attr('selected','selected').prop('selected', true);
						  openning.exchange_money();

						  if( data.curr == data.curr_base ){
							  $('input[name="curr_rate"]').val(1).prop("disabled", true);
						  } else if(data.curr_rate){
							  $('input[name="curr_rate"]').val(data.curr_rate).prop("disabled", false);
						  }
					  }


				  },
				  dataType: "JSON"
			});
		}

	},
	load_supplier_branch : function(supplier_id){
		if( !supplier_id ){
			supplier_id =$('select[name=supplier]').val();
		}
		if( supplier_id ){
			$.ajax({
				  url: '/admin/opening_balance/supplier.php',
				  data:{'supp':supplier_id,'date':$('input[name=tran_date]').val()},
				  success: function(data){
					  if(data.curr){
						  $('select[name="currency"] option[value='+data.curr+']').attr('selected','selected').prop('selected', true);
						  openning.exchange_money();

						  if( data.curr == data.curr_base ){
							  $('input[name="curr_rate"]').val(1).prop("disabled", true);
						  } else if(data.curr_rate){
							  $('input[name="curr_rate"]').val(data.curr_rate).prop("disabled", false);
						  }
					  }


				  },
				  dataType: "JSON"
			});
		}

	},
	load_currency_rate : function(currentcy){
		if( !currentcy ){
			currentcy =$('select[name=currency]').val();
		}
		if( currentcy ){
			$.ajax({
				  url: '/admin/opening_balance/supplier.php',
				  data:{'date':$('input[name=tran_date]').val(),'curr':currentcy},
				  success: function(data){
					  openning.exchange_money();

					  if( data.curr == data.curr_base ){
						  $('input[name="curr_rate"]').val(1).prop("disabled", true);
					  } else if(data.curr_rate){
						  $('input[name="curr_rate"]').val(data.curr_rate).prop("disabled", false);
					  }



				  },
				  dataType: "JSON"
			});
		}

	},
	load_branch : function(){
		openning.load_branch_ajax();
		$('select[name=customer]').change(function(){
			openning.load_branch_ajax($(this).val());
		});
		openning.load_supplier_branch();
		$('select[name=supplier]').change(function(){
			openning.load_supplier_branch($(this).val());
		});
		openning.load_currency_rate();
		$('select[name=currency]').change(function(){
			openning.load_currency_rate($(this).val());
		});
	},

	exchange_money : function(){
		var debit = $('input[name=debit]');
		var credit = $('input[name=credit]');
		var rate = strtonumber( $('input[name=curr_rate]').val() );
		//if( rate > 0 ){
			debit_val = strtonumber(debit.val());
			if( debit_val !='' &&  !isNaN(debit_val) ){
				debit_base = debit_val * rate;
				$('input.debit_base').val(debit_base.toFixed(2));
				debit.val(debit_val.toFixed(2));
			}
			credit_val = strtonumber(credit.val());
			if( credit_val !='' && !isNaN(credit_val) ){
				credit_base = credit_val * rate;
				$('input.credit_base').val(credit_base.toFixed(2));
				credit.val(crebit_val.toFixed(2));
			}
		//}


	},

	input_credit_debit: function (){

		var debit = $('input[name=debit]');

		debit_val = strtonumber( debit.val() );
		var credit = $('input[name=credit]');
		credit_val = strtonumber( credit.val() );

		if( isNaN(debit_val) ){
			$('input[name=debit], input.debit_base').val('').prop("disabled", true);
		}

		if( isNaN(credit_val) ){
			$('input[name=credit], input.credit_base').val('').prop("disabled", true);
		}

		if( !isNaN(debit_val) && debit_val > 0 ){
			$('input[name=credit], input.credit_base').val('').prop("disabled", true);
		} else if( !isNaN(credit_val) && credit_val > 0 ){
			$('input[name=debit], input.debit_base').val('').prop("disabled", true);
		} else {
			$('input[name=debit], input[name=credit]').prop("disabled", false);
		}
	},

	openning_total: function (){
		//$( document ).ready(function() {
//			var count = $('input.total_trans');
//			count.val(0);
//			var debit_total = $('input.total_debit');
//			debit_total.val(0);
//			var credit_total = $('input.total_credit');
//			credit_total.val(0);
//
//			$('input[type=hidden].openning_debit').each(function( index ) {
//				base_val = parseFloat($(this).val());
//				count.val( parseFloat(count.val())+1);
//				if( !isNaN(base_val) && base_val > 0 ){
//
//					debit_total.val( parseFloat(debit_total.val())+base_val);
//					console.log( index+' debit base '+parseFloat(debit_total.val()) );
//				}
//			});
//			if( parseFloat(debit_total.val()) > 0 ){
//				debit_total.val( parseFloat(debit_total.val()).formatMoney(2, '.', ',') );
//			}
//
//			$('input[type=hidden].openning_credit').each(function( index ) {
//				base_val = parseFloat($(this).val());
//				if( !isNaN(base_val) && base_val > 0 ){
//					credit_total.val( parseFloat(credit_total.val())+base_val);
//				}
//			});
//
//			if( parseFloat(credit_total.val()) > 0 ){
//				credit_total.val( parseFloat(credit_total.val()).formatMoney(2, '.', ',') );
//			}
	//	});
	},
	inventory: function(){
		$( document ).ready(function() {
			var count = $('input.total_inventory');
			count.val(0);
			var amount = $('input.total_inventory_base');
			amount.val(0);
			$('input[name="total_iventory[]"]').each(function( index ) {
				base_val = parseFloat($(this).val());
				if( base_val > 0 ){
					count.val( parseFloat(count.val())+1);
					amount.val( parseFloat(amount.val())+base_val);
				}

			});

		});
	}
};

Number.prototype.formatMoney = function(c, d, t){
	var n = this,
	    c = isNaN(c = Math.abs(c)) ? 2 : c,
	    d = d == undefined ? "." : d,
	    t = t == undefined ? "," : t,
	    s = n < 0 ? "-" : "",
	    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
	    j = (j = i.length) > 3 ? j % 3 : 0;
	   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};



