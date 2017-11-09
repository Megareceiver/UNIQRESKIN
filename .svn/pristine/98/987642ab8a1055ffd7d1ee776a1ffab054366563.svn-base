/**
 *
 */

var item_listview = {
	actions : function() {
		$(document).ready(
				function() {
					var table = $('table.tablestyle');
					$('tr', table).each(function() {
						item_listview.itemPrice($(this));
					});
					$('a.edit', table).unbind('click').click(
							function() {
								row = $(this).parents('tr');
								var itemedit = table.find('tr.edit-row')
										.clone().removeClass('edit-row');
								item_listview.edit(row, itemedit);
							});

					$('a.remove', table).click(function() {
						$(this).parents('tr').remove();
						$('#Update').click();
					});
					item_listview.changeProduct();
					$('a.add-item', table).unbind('click').click(function() {
						item_listview.addRow(table);
					});
				});

	},

	changeProduct : function() {
		input = $('select.products');
		input.change(function() {
			row = $(this).parents('tr');
			row.find("input[name=stock_id]").val($(this).val());

			row.find("input[name=units]").val(
					input.find('option[value="' + $(this).val() + '"]').attr(
							'units'));

		});
	},

	edit : function(rowTaget, rowControl) {

		$('input[type=text]', rowControl).each(
				function() {
					input_text = $(this);
					old = $("input[name$='[" + input_text.attr('name') + "]']",
							rowTaget);
					input_text.val(old.val());
				});

		$('select', rowControl).each(
				function() {
					input = $(this);
					old = $("select[name$='[" + input.attr('name') + "]']",
							rowTaget);

					value = '';
					if (input.attr('name') == 'item_description') {
						value = rowTaget.find("input[name$='[stock_id]']")
								.val();
						units = $('option[value="' + value + '"]', input).attr(
								'units');
						rowControl.find('input[name=units]').val(units);
					} else if (input.attr('name') == 'tax_type_id') {
						input.attr('disabled', true);
						value = rowTaget.find("input[name$='[tax_type_id]']")
								.val();
					}

					$('option[value="' + value + '"]', input).prop('selected',
							true);
				});

		rowControl.find('a.save').attr('index', rowTaget.index());

		rowTaget.after(rowControl.show());
		rowTaget.hide();

		item_listview.changeProduct();

		rowControl.find('a.save').click(function() {
			item_listview.save(rowControl, rowControl.parents('table'));
		});
		rowControl.find('a.cancel').click(function() {
			rowControl.remove();
			rowTaget.show();

		});
	},

	save : function(taget, table) {
		rowTagetIndex = parseInt(taget.find('a.save').attr('index')) + 1;

		rowTaget = $('tr:eq(' + rowTagetIndex + ')', table);
		$('input[type=text]', taget)
				.each(
						function() {
							$("input[name$='[" + $(this).attr('name') + "]']",
									rowTaget).val($(this).val());
						});

		$('select', taget).each(
				function() {
					input = $(this);
					selected = $(this).find(
							'option[value="' + $(this).val() + '"]');
					if (input.attr('name') == 'item_description') {
						// $(
						// "span."+$(this).attr('name')+"_title",rowTaget).html(
						// selected.attr('desc') );
						$("input[name$='[" + $(this).attr('name') + "]']",
								rowTaget).val(selected.attr('desc'));
						rowTaget.find("input[name$='[stock_id]']").prev('a')
								.attr('href', '#').html($(this).val());

					} else if (input.attr('name') == 'tax_type_id') {

						$("span[class$='tax_type_id]_title']", rowTaget).html(
								selected.html());
						$("input[name$='[" + $(this).attr('name') + "]']",
								rowTaget).val($(this).val());
					}
					$('option[value="' + value + '"]', input).prop('selected',
							true);
				});

		item_listview.itemPrice(rowTaget);
		taget.remove();
		rowTaget.show();
		$('#Update').click();
	},

	itemPrice : function(row) {
		qty = parseFloat(row.find("input[name$='[qty_dispatched]']").val());
		if (isNaN(qty)) {
			qty = 1;
			row.find("input[name$='[qty_dispatched]']").val(1);
		}
		quantity = parseFloat(row.find("input[name$='[quantity]']").val());
		if (isNaN(quantity)) {
			row.find("input[name$='[quantity]']").val(1);
		}
		qty_done = parseFloat(row.find("input[name$='[qty_done]']").val());
		if (isNaN(qty_done)) {
			row.find("input[name$='[qty_done]']").val(0);
		}

		price = parseFloat(row.find("input[name$='[price]']").val());
		row.find("input[name$='[price]']").val(price.toFixed(user.pdec));
		discount = parseFloat(row.find("input[name$='[discount_percent]']")
				.val());
		if (isNaN(discount)) {
			discount = 0;
			row.find("input[name$='[discount_percent]']").val(0);
		}
		total = qty * price * (1 - discount / 100);
		row.find("input[name$='[total]']").val(total.toFixed(user.pdec));
	},

	addRow : function(table) {
		var done = true;
		newRow = $('tbody tr:last', table).prev('tr').clone();
		rowControl = $('tbody tr:last', table);

		$('input[type=text]', rowControl).each(
				function() {
					inName = $(this).attr('name');
					inVal = $(this).val();
					if (isNaN(parseFloat(inVal))) {
						inVal = 0;
					}
					if (inName == 'discount_percent') {
						if (inVal < 0 || inVal > 1) {
							$(this).val(0);
						}

					} else if (inName == 'price' && parseFloat(inVal) == 0) {
						alert('Input price');
						done = false;
					}

					$("input[name$='[" + $(this).attr('name') + "]']", newRow)
							.val($(this).val());

					$(this).val('');
				});

		$('select', rowControl).each(
				function() {
					input = $(this);
					selected = $(this).find(
							'option[value="' + $(this).val() + '"]');
					if (input.attr('name') == 'item_description') {
						// $(
						// "span."+$(this).attr('name')+"_title",newRow).html(
						// selected.attr('desc') );
						$("input[name$='[" + $(this).attr('name') + "]']",
								newRow).val(selected.attr('desc'));
					} else if (input.attr('name') == 'tax_type_name') {

						$("span[class$='tax_type_name]_title']", newRow).html(
								selected.html());
						$("input[name$='[" + $(this).attr('name') + "]']",
								newRow).val($(this).val());
					}
					$(this).find("option").prop("selected", false);
				});

		if (done == true) {
			jQuery('input', newRow).each(
					function() {
						name = $(this).attr('name');
						last_index = $('tbody tr:last', table).prev('tr')
								.index();

						name_new = name.replace('[' + last_index + ']', '['
								+ (last_index + 1) + ']');
						$(this).attr('name', name_new);
					});

			$('tbody tr:last', table).prev('tr').after(newRow);
			item_listview.actions();
		}

	}
};

var menu = {
	ini : function() {
		if ($(window).width() > 750) {
			menu.level2();
			menu.level3();
		}

	},
	level2 : function() {

		jQuery(".navbar-nav > li > ul").each(
				function(i) {
					item1 = jQuery(this);
					var title_max_length = 0;
					jQuery("> li > a", item1).each(
							function(ii) {
								title = jQuery(this);
								title_max_length = Math.max(title_max_length,
										title.text().length)
							});
					item1.css('width', title_max_length * 8 + 30);
					return;
				});
	},
	level3 : function() {

		jQuery(".navbar-nav > li > ul > li > ul").each(
				function(i) {
					item1 = jQuery(this);
					var title_max_length = 0;
					jQuery("> li > a", item1).each(
							function(ii) {
								title = jQuery(this);
								title_max_length = Math.max(title_max_length,
										title.text().length)
							});
					item1.css('width', title_max_length * 8 + 30);
					return;
				});
	}
};

$(document).ready(function() {
	menu.ini();

});

ajax_fun.swich_input = function() {
	var inputs_switch = jQuery('input.switch');
	inputs_switch.bootstrapSwitch();
	inputs_switch.on('switchChange.bootstrapSwitch', function(event, state) {
		$(this).click();
		if ($(this).hasClass('ajaxsubmit')) {

			JsHttpRequest.request('_' + $(this).attr('name') + '_update',
					this.form);
		}
	});
}

//$(function() {

//});