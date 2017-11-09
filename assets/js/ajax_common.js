/**
 * QuanNH 2016-02-26 build function call document ready , after ajax call
 */


var loading_bootstrap = {
    open : function() {
		jQuery('#hints').html('');
		jQuery('body')
			.append(
				'<div class="modal-backdrop fade in"></div> <div class="modal fade in" ><div class="modal-processing"><span class="text">Loading ...</span><span class="loader"></span></div></div>');
		//loading_bootstrap.set_center();
	
		//$(window).resize(function() {
		    //loading_bootstrap.set_center();
		//});
    },
    remove : function() {
    	jQuery('.modal-backdrop, .modal.in').remove();
    },
    set_center : function() {
	var item_top = $(window).height() / 2;
	if ($(window).height() > 40) {
	    item_top = ($(window).height() - 40) / 2;
	}
	if (item_top > 40) {
	    item_top = item_top - 40;
	} else {
	    item_top = 10;
	}
	jQuery('.modal-processing').css('margin-top', item_top);
    }
};

var ATBefor_jsHttpRequest = {
    'inc' : function() {
	if (jQuery('input[name=valid_gst]').is(':checked')) {
	    jQuery('input[name=valid_gst]').val(1);
	    jQuery('select[name=supplier_tax_id]').val(0);
	} else {
	    jQuery('input[name=valid_gst]').val(0);
	}
    }
};

var ajax_fun = {
    int : function() {
	$('button[name=page_reload]').click(function() {
	    form = $('#_page_body form');
	    form.append('<input name="page_reload" value="" type="hidden" />');
	    form.submit();
	});

	jQuery('.modal-dialog button.close, button[data-dismiss=modal]').click(
		function() {
		    $(this).parents('.modal.fade').hide();
		});


//	jQuery('.qdate').qdate();
//
//	$('.inputdate .icon, .inputdate > i').click(function() {
//	    $(".qdate").qdate("hide");
//	    $(this).parents('.inputdate').find('.qdate').qdate('show');
//	});

	$('.date-picker').datepicker({
           // rtl: App.isRTL(),
            orientation: "left",
            autoclose: true,
            format: date_format
        });
//	console.log(date_format);

	$('.inputdate .icon, .inputdate > i').click(function() {
	    $(this).parents('.inputdate').find('.date-picker').datepicker('show');
	});

	item_listview.actions();

	loading_bootstrap.remove();
	// self_bill.supplier_inc();

	if (typeof imported_good != "undefined"
		&& typeof imported_good.ini == "function") {
	    imported_good.ini();
	}

	jQuery('.modal-dialog button.close, button[data-dismiss=modal]').click(
		function() {
		    $(this).parents('.modal.fade').hide();
		});

	jQuery('select.autosubmit').change(function() {
	    JsHttpRequest.request(this);
	});

	if (typeof openning != "undefined" && typeof openning.ini == "function") {
	    openning.ini();
	}

	jQuery('button.remove').click(
		function() {
		    var form = $(this).parents('form');
		    form.append('<input type="hidden" value="' + $(this).val()
			    + '" name="_remove">');
		    JsHttpRequest.request(this);
		});

	jQuery('#delete-fiscalyear-confim').modal('show');
	jQuery('#delete-fiscalyear-confim .btn-submit').click(function() {
	    loading_bootstrap.open();
	    jQuery('#delete-fiscalyear-confim').modal('hide');
	});

    },

};

ajax_fun.supplier = function() {

    var gst_by = $("input[name=gst_by]:checked");
    var supplier_tax = $("select[name=supplier_tax_id]");
    if (gst_by.val() == 0) {
	supplier_tax.prop('disabled', true);
    } else {
	supplier_tax.prop('disabled', false);
    }
    supplier_tax.change(function() {
	$('input[name=_supplier_tax_id_sel]').val($(this).val());
    });
};

ajax_fun.multicheckbox = function() {
    inputarea = jQuery('.multicheckbox');

    inputarea.parents('.form-body').find('button.checkall').click(function() {
	jQuery('input[type=checkbox]', inputarea).prop("checked", true);
	return false;
    });
    inputarea.parents('.form-body').find('button.uncheckall').click(function() {
	jQuery('input[type=checkbox]', inputarea).prop("checked", false);
	return false;
    });
};

ajax_fun.selectpicker = function(){
    jQuery('select.form-control').selectpicker();
    jQuery('select.form-control').change(function() {
    	$(this).selectpicker('refresh');
//    	$(this).selectpicker('show');
//    	$('.dropdown-menu.open').hide();
    });
}

ajax_fun.table_responsive = function(){
    var table = jQuery('.form-body table.table');
    if( table.length > 0 ){
	body = table.parents('.form-body');

	if( table.find('thead').length > 0 ){
		table_width = table.find('thead').width();
	} else {
		table_width = table.find('tr').width();
	}

	if( table_width <  body.width()){
	    table.css('display','table');
	}

    }
}

ajax_fun.grouping_detail = function(){
	jQuery('table .button-hide').click(function(){
	var row_taget = $(this).parents('tr');

	    if( $(this).hasClass('fa-plus') ){
			row_taget.nextUntil( '.tax_footer','tr' ).show();
			$(this).removeClass('fa-plus').addClass('fa-minus');
	    } else {

			row_taget.nextUntil( '.tax_footer','tr').hide();
			$(this).addClass('fa-plus').removeClass('fa-minus');
	    }
	});
}

ajax_fun.sale_ref_update = function(){
	jQuery( "input[name=cust_ref2], input[name=cust_ref]" ).change(function() {
		//alert('sbumit change ref');
		JsHttpRequest.request(this);
		//jQuery(this).parents('form').submit();
		
	});
}
