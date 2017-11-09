//gst_03_box_section


$( document ).ready(function() {
	msicSelect();
	select_chosen();
	ajax_common_func();
	gst_grouping();

	jQuery('select[name=goods_invoice]').change(function(){
		$(this).closest('form').submit();
	});


});

function gst_grouping(){
	jQuery('table .button-hide').click(function(){
		table_body = $(this).closest('table').find('tbody');
		if( $(this).hasClass('glyphicon-plus') ){
			table_body.show();
			$(this).removeClass('glyphicon-plus').addClass('glyphicon-minus');
		} else {
			table_body.hide();
			$(this).addClass('glyphicon-plus').removeClass('glyphicon-minus');
		}
	});

	$('table.gst-grouping tbody td').hover( function(){
	    //var i = $(this).prevAll('td').length;
		var i = $(this).index() ;
	    $(this).parent().addClass('hover');
	    $('table.gst-grouping tbody td:nth-child(' + (i + 1) + ')').addClass('hover');
	    if( i > 8 ){
	    	$('table.gst-grouping tfoot td:nth-child(' + (i -7) + ')').addClass('hover');
	    }

	    $('table.gst-grouping-header td:nth-child(' + (i+ 1) + ')').addClass('hover');

	}, function(){
		$('table.gst-grouping,table.gst-grouping-header').find('.hover').removeClass('hover');
	});



}

function strtonumber(str){
	str = str.replace(",",'');
	str = str.replace(/ /g,'');
	str = parseFloat( str );
	return str;

}

function popitup(url) {
	newwindow=window.open(url,'name','height=600,width=850');
	if (window.focus) {newwindow.focus();}
	return false;
}

function ajax_common_func(){
	$('button[name=page_reload]').click(function(){
		form = $('#_page_body form');
		form.append('<input name="page_reload" value="" type="hidden" />');
		form.submit();
	});
	item_listview.actions();

	loading_bootstrap.remove();
	self_bill.supplier_inc();


	jQuery('select[name=datebaddeb]').change(function(){
    	JsHttpRequest.request("datebaddeb");

    });
	
	jQuery('.monthpicker').MonthPicker();
	finput.multicheckbox();

}

var loading_bootstrap = {
	open: function(){
		jQuery('#hints').html('');
		jQuery('body').append('<div class="modal-backdrop fade in"></div> <div class="modal fade in" ><div class="modal-processing"><span class="text">Loading ...</span><span class="loader"></span></div></div>');
		loading_bootstrap.set_center();

		$( window ).resize(function() {
			loading_bootstrap.set_center();
		});
	},
	remove:function(){
		jQuery('.modal-backdrop, .modal.in').remove();
	},
	set_center: function(){
		var item_top = $( window ).height()/2;
		if ( $( window ).height() > 40 ){
			item_top = ($( window ).height() - 40)/2;
		}
		if( item_top > 40 ){
			item_top = item_top - 40;
		} else {
			item_top = 10;
		}
		jQuery('.modal-processing').css('margin-top',item_top);
	}
};




var msic = {

	actions: function(){

		this.selectionSelect = $('select[name=gst_03_box_section]');
		this.divistionSelect = $('select[name=gst_03_box_division]');
		this.divistionCache = $('select[name=division_cache]');
		this.msicSelect = $('select[name=gst_03_box_msic]');
		this.msicCache = $('select[name=item_cache]');
		this.msicInputCode = $('input[name=gst_03_box_code]');


		this.inputCodeChange();

		this.selectionSelect.change(function(){
	    	msic.divisionSetValue( $(this).val() );
	    });

		this.divistionSelect.change(function(){
			msic.msicSetValue( $(this).val() );
		});
		this.msicSelect.change(function(){
			msic.msicInputCode.val( $(this).val() );
		});

		var msic_code_selected = msic.msicCache.find('option[value='+this.msicInputCode.val()+']');

		if( msic_code_selected.length > 0 ){
			msic.msicSetValue(msic_code_selected.attr('parent'),msic_code_selected.attr('value'));
		} else {
			msic.msicSetValue(-1);
			msic.divisionSetValue(-1);
			msic.selectionSelect.find('option[value=-1]').prop('selected', true);;
		}

	},




	divisionSetValue : function(section,value){
		$('option',msic.divistionSelect).each(function(){
            if( $(this).attr('value') != -1 ) {
                $(this).remove();
            }
        });
		$('option',msic.divistionCache).each(function(){
			if( $(this).attr('parent') == section ) {
                $(this).clone().appendTo(msic.divistionSelect);
            }
        });

		if( value ){
        	item = msic.divistionSelect.find('option[value="'+value+'"]');
        	if( item ){
        		item.prop('selected', true);
        		msic.selectionSelect.find('option[value='+item.attr('parent')+']').prop('selected', true);


        	} else {
        		msic.divistionSelect.find('option[value=-1]').prop('selected', true);
        	}

        } else {
        	msic.divistionSelect.find('option[value=-1]').prop('selected', true);

        	$('option',msic.msicSelect).each(function(){
                if( $(this).attr('value') != -1 ) {
                    $(this).remove();
                }
            });
        }
	},

	msicSetValue:function(divstion,value){

		$('option',msic.msicSelect).each(function(){
            if( $(this).attr('value') != -1 ) {
                $(this).remove();
            }
        });

        $('option',msic.msicCache).each(function(){
           if( $(this).attr('parent') == divstion ) {
                $(this).clone().appendTo(msic.msicSelect);
            }
        });
        if( value ){
        	item = msic.msicSelect.find('option[value='+value+']');
        	if( item ){
        		item.prop('selected', true);
        		//alert(item.attr('parent'));
        		division = msic.divistionCache.find('option[value="'+item.attr('parent')+'"]');
        		if( division ){
        			msic.divisionSetValue(division.attr('parent'),division.val() );
        		}

        	} else {
        		msic.msicSelect.find('option[value=-1]').prop('selected', true);
        	}

        } else {
        	msic.msicSelect.find('option[value=-1]').prop('selected', true);
        }
	},
	inputCodeChange:function(){
		this.msicInputCode.change(function() {
			var item = msic.msicCache.find('option[value='+$(this).val()+']');
			if( item.length > 0 ){
				msic.msicSetValue(item.attr('parent'),item.attr('value'));
			} else {
				msic.msicSetValue(-1);
				msic.divisionSetValue(-1);
				msic.selectionSelect.find('option[value=-1]').prop('selected', true);;
			}
		});
	}
};


function msicSelect(){

	msic.actions();


}

function msicconfig(){
    var selectionSelect = $('select[name=section_section]');
    var divistionSelect = $('select[name=division]');
    var divistionCache = $('select[name=division_cache]');

    selectionSelect.change(function(){
        var section = $(this).val();

        $('option',divistionSelect).each(function(){
            if( $(this).attr('value') != -1 ) {
                $(this).remove();
            }
        });

        $('option',divistionCache).each(function(){
            if( $(this).attr('parent')== section ) {
                $(this).appendTo(divistionSelect);
            }
        });
        divistionSelect.val('-1');



    });
}

function select_chosen(){
	var items = $('select[name=stock_id]');
	item_codes = [];
	$('option',items).each(function(){
		if( $(this).val()!='' || $(this).val()!=null ){
			item_codes.push($(this).val());
		}

	});
	$('select[name=stock_id]').css({width:350,'max-width':350}).chosen({});

	$( "input[type=text][rel=stock_id]" ).autocomplete({
	      source: item_codes,
	      focus: function (event, ui) {
	          event.preventDefault();
	          $("#tags").val(ui.item.label);
	      },
	      select: function (event, ui) {
	    	  $('select[name=stock_id]').val(ui.item.value).trigger("chosen:updated");
	      }
    });
	$( "input[type=text][rel=stock_id]" ).change(function () {

	    	  $('select[name=stock_id]').val($(this).val()).trigger("chosen:updated");

  });


}



function load_currency(){
	$('select[name=supplier]').change(function(){
		$.ajax({
		  url: '/admin/opening_balance/supplier.php?supp='+$(this).val(),
		  success: function(data){
			  if(data.curr){
				  $('select[name="curr[]"] option[value='+data.curr+']').attr('selected','selected').prop('selected', true);
			  }
		  },
		  dataType: "JSON"
		});
	});
}


var openning = {

};

var sale = {
		form_item : function (){
			$('input[name=cust_ref2]').change(function(){
				$('input[name=cust_ref]').val( $(this).val() );
			});
			$('input[name=cust_ref]').change(function(){
				$('input[name=cust_ref2]').val( $(this).val() );
			});

			$('select[name=Location2]').change(function(){
				$('select[name=Location] option').filter('[value='+$(this).val()+']').prop('selected', true);
			});

			$('select[name=Location]').change(function(){
				$('select[name=Location2] option').filter('[value='+$(this).val()+']').prop('selected', true);
			});
		},

};


var item_listview = {
	actions: function(){
		$( document ).ready(function() {
			var table = $('table.tablestyle');
			$('tr',table).each(function(){
				item_listview.itemPrice($(this));
			});
			$('a.edit',table).unbind('click').click(function(){
				row = $(this).parents('tr');
				var itemedit = table.find('tr.edit-row').clone().removeClass('edit-row');
				item_listview.edit(row,itemedit);
			});

			$('a.remove',table).click(function(){
				$(this).parents('tr').remove();
				$('#Update').click();
			});
			item_listview.changeProduct();
			$('a.add-item',table).unbind('click').click(function(){
				item_listview.addRow(table);
			});
		});


	},

	changeProduct : function(){
		input = $('select.products');
		input.change(function(){
			row = $(this).parents('tr');
			row.find("input[name=stock_id]").val( $(this).val() );

			row.find("input[name=units]").val( input.find('option[value="'+$(this).val()+'"]').attr('units') );

		});
	},

	edit : function(rowTaget,rowControl){

		$('input[type=text]',rowControl).each(function(){
			input_text = $(this);
			old = $( "input[name$='["+input_text.attr('name')+"]']",rowTaget);
			input_text.val(old.val());
		});

		$('select',rowControl).each(function(){
			input = $(this);
			old = $( "select[name$='["+input.attr('name')+"]']",rowTaget);

			value = '';
			if( input.attr('name')=='item_description' ){
				value = rowTaget.find("input[name$='[stock_id]']").val();
				units = $('option[value="'+value+'"]',input).attr('units');
				rowControl.find('input[name=units]').val(units);
			} else if( input.attr('name')=='tax_type_id' ){
				value = rowTaget.find("input[name$='[tax_type_name]']").val();
			}
			$('option[value="'+value+'"]',input).prop('selected', true);
		});

		rowControl.find('a.save').attr('index',rowTaget.index());

		rowTaget.after(rowControl.show());
		rowTaget.hide();

		item_listview.changeProduct();
		rowControl.find('a.save').click(function(){
			item_listview.save(rowControl,rowControl.parents('table'));
		});
		rowControl.find('a.cancel').click(function(){
			rowControl.remove();
			rowTaget.show();

		});
	},

	save : function(taget,table){
		rowTagetIndex = parseInt(taget.find('a.save').attr('index')) + 1;

		rowTaget = $('tr:eq('+rowTagetIndex+')',table );
		$('input[type=text]',taget).each(function(){
			$( "input[name$='["+$(this).attr('name')+"]']",rowTaget).val($(this).val());
		});

		$('select',taget).each(function(){
			input = $(this);
			selected = $(this).find('option[value="'+$(this).val()+'"]');
			if( input.attr('name')=='item_description' ){
//				$( "span."+$(this).attr('name')+"_title",rowTaget).html( selected.attr('desc') );
				$( "input[name$='["+$(this).attr('name')+"]']",rowTaget).val( selected.attr('desc') );
				rowTaget.find("input[name$='[stock_id]']").prev('a').attr('href','#').html($(this).val());

			} else if( input.attr('name')=='tax_type_id' ){

				$( "span[class$='tax_type_id]_title']",rowTaget).html( selected.html() );
				$( "input[name$='["+$(this).attr('name')+"]']",rowTaget).val($(this).val());
			}
			$('option[value="'+value+'"]',input).prop('selected', true);
		});


		item_listview.itemPrice(rowTaget);
		taget.remove();
		rowTaget.show();
		$('#Update').click();
	},

	itemPrice : function(row){
		qty = parseFloat( row.find("input[name$='[qty_dispatched]']").val() );
		if( isNaN(qty) ){
			qty = 1;
			row.find("input[name$='[qty_dispatched]']").val(1);
		}
		quantity = parseFloat( row.find("input[name$='[quantity]']").val() );
		if( isNaN(quantity) ){
			row.find("input[name$='[quantity]']").val(1);
		}
		qty_done = parseFloat( row.find("input[name$='[qty_done]']").val() );
		if( isNaN(qty_done) ){
			row.find("input[name$='[qty_done]']").val(0);
		}

		price = parseFloat( row.find("input[name$='[price]']").val() );
		row.find("input[name$='[price]']").val(price.toFixed(user.pdec));
		discount = parseFloat( row.find("input[name$='[discount_percent]']").val() );
		if( isNaN(discount) ){
			discount = 0;
			row.find("input[name$='[discount_percent]']").val(0);
		}
		total = qty*price*(1-discount/100);
		row.find("input[name$='[total]']").val(total.toFixed(user.pdec));
	},

	addRow: function(table){
		var done = true;
		newRow = $('tbody tr:last',table).prev('tr').clone();
		rowControl = $('tbody tr:last',table);

		$('input[type=text]',rowControl).each(function(){
			inName = $(this).attr('name');
			inVal = $(this).val();
			if( isNaN(parseFloat(inVal)) ){
				inVal = 0;
			}
			if( inName=='discount_percent' ){
				if(inVal < 0 || inVal > 1){
					$(this).val(0);
				}

			} else if ( inName=='price' && parseFloat(inVal) == 0 ){
				alert('Input price');
				done = false;
			}

			$( "input[name$='["+$(this).attr('name')+"]']",newRow).val( $(this).val() );

			$(this).val('');
		});

		$('select',rowControl).each(function(){
			input = $(this);
			selected = $(this).find('option[value="'+$(this).val()+'"]');
			if( input.attr('name')=='item_description' ){
//				$( "span."+$(this).attr('name')+"_title",newRow).html( selected.attr('desc') );
				$( "input[name$='["+$(this).attr('name')+"]']",newRow).val( selected.attr('desc') );
			} else if( input.attr('name')=='tax_type_name' ){

				$( "span[class$='tax_type_name]_title']",newRow).html( selected.html() );
				$( "input[name$='["+$(this).attr('name')+"]']",newRow).val($(this).val());
			}
			$(this).find("option").prop("selected", false);
		});

		if( done==true ){
			jQuery('input',newRow).each(function(){
				name =  $(this).attr('name');
				last_index = $('tbody tr:last',table).prev('tr').index();

				name_new = name.replace('['+last_index+']', '['+(last_index+1)+']');
				$(this).attr('name',name_new);
			});

			$('tbody tr:last',table).prev('tr').after(newRow);
			item_listview.actions();
		}

	}
};
var self_bill = {
	'supplier_inc': function(){
		self_bill.approval_ref_check();
		$('input[name=self_bill_approval_ref],input[name=gst]').change(function(){
			self_bill.approval_ref_check();
		});

	},
	approval_ref_check:function(){
		if( $('input[name=self_bill_approval_ref]').val() =='' ){
			$("input[name=self_bill]").prop( "checked", false ).prop('disabled', true);
		} else {
			$("input[name=self_bill]").prop('disabled', false);
		}
//		console.log( $('input[name=gst]:checked').val() );
		if( $('input[name=gst]:checked').val() == 0 ){
			$("select[name=supplier_tax_id]").val(null).prop('disabled', true);
		} else {
			$("select[name=supplier_tax_id]").prop('disabled', false);
		}
//		if( !$('input[name=valid_gst]').is(':checked') ){
//			$('input[name=gst][value=1]').prop('checked',true);
//			$("select[name=supplier_tax_id]").val(16).prop('disabled', true);
//		} else {
//			$("select[name=supplier_tax_id]").prop('disabled', false);
//		}

	}
};

var ATBefor_jsHttpRequest = {
	'inc': function(){
		if( jQuery('input[name=valid_gst]').is(':checked') ){
			jQuery('input[name=valid_gst]').val(1);
			jQuery('select[name=supplier_tax_id]').val(0);
		} else {
			jQuery('input[name=valid_gst]').val(0);
		}
	}
};

finput = {
	multicheckbox: function(){
		inputarea = jQuery('.multicheckbox');

		inputarea.parents('.form-group').find('button.checkall').click(function(){
			jQuery('input[type=checkbox]',inputarea).prop( "checked", true );
			return false;
		});
		inputarea.parents('.form-group').find('button.uncheckall').click(function(){
			jQuery('input[type=checkbox]',inputarea).prop( "checked", false );
			return false;
		});
	}
};