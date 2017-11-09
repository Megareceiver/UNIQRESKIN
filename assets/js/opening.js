
var openning = {
	ini: function(){
		jQuery(function() {
			//openning.load_branch();
			openning.input_credit_debit();
			$('select[name=currency],input[name=curr_rate], input[name=debit], input[name=credit]').change(function(){

				openning.input_credit_debit();
			});
		});



	},

	input_credit_debit: function (){

		var debit = $('input[name=debit]');
		debit_val = (debit.lenght > 0) ? strtonumber( debit.val() ) : 0;

		var credit = $('input[name=credit]');
		credit_val = (credit.lenght > 0) ? strtonumber( credit.val() ) : 0;


		$('input.debit_base, input.credit_base').val('');
		$('input[name=debit], input[name=credit]').prop("disabled", false);
		var rate = jQuery('input[name=curr_rate]').val();

		if( !isNaN(debit_val) && debit_val > 0 ){
			$('input.debit_base').val(debit_val*rate).prop("disabled", true);
			credit.prop("disabled", true);
		}

		if( !isNaN(credit_val) && credit_val > 0 ){
			$('input.credit_base').val(credit_val*rate).prop("disabled", true);
			debit.prop("disabled", true);
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



