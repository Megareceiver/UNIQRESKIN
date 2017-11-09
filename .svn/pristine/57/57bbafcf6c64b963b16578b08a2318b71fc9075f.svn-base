var interval = 500;
function do_import(user) {
//    $.ajax({
//	    type: 'GET',
//	    url: '/admin/import.php?type=adddata',
//	    dataType: 'json',
//	    success: function (data) {
//	    	$('#hidden').val(data);// first set the value
//	    }, complete: function (data) {
//	       //setTimeout(do_import, interval);
//	    }
//    });

	//$.get('',function(){},"jsonp");
	$('<iframe />', {
	    name: 'myFrame',
	    id:   'myFrame',
	    src: '//api.accountanttoday.net/import/run/'+user,
	    style:"display:none;"
	}).appendTo('body');
}

function get_complate(process_id){
	if( process_id != undefined ){
		$.ajax({
		    type: 'GET',
		    url: '/admin/import.php?type=status&process='+process_id,
		    dataType: 'json',
		    success: function (data) {
		    	module = data.module;
		    	percent = Math.round( data.complate/ data.total * 100 );
		    	if( $('#process'+module).length <=0 ){
		    		$('#msgbox').after('<div id="process'+module+'" class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100" style="width:'+percent+'%"></div> </div>');
		    	}
		    	$('#process'+module).find('.progress-bar').css('width',percent+'%').html('read '+module+' data ('+data.complate +'/'+ data.total+')');
		    }, complete: function (data) {
		    	var obj = jQuery.parseJSON( data.responseText);


		    	if( typeof obj.total != "undefined" && typeof obj.complate != "undefined" &&  parseInt(obj.total) > parseInt(obj.complate) ){
		    		setTimeout(function() {
		    			get_complate(process_id);
		    		}, interval);
		    		//setTimeout(get_complate, interval);
		    	} else {
		    		check_import();
		    	}


		    }
	    });
	}

}

function check_import(){
	jQuery.ajax({
	    type: 'GET',
	    url: '/admin/import.php?type=check',
	    dataType: "json",
	    success: function (data) {

	    	if( typeof data.total != "undefined" && typeof data.complate != "undefined" &&  parseInt(data.total) > parseInt(data.complate) ){
	    		do_import(data.subdomain);
	    		get_complate(data.id);
	    	} else {
	    		console.log('ajax complate');
	    		window.location.href = '/admin/import.php';
	    	}
	    },
	    complete:function(){
	    	import_items_action();
	    }
    });
}

function import_items_action(){
	$('input[name=checkall]').change(function(){
		console.log('check all');
		table = $(this).parents('table');
		if( $(this).is(':checked') ){
			$('tbody input[type=checkbox]',table).prop('checked',true);
		} else {
			$('tbody input[type=checkbox]',table).prop('checked',false);
		}

	});
}

function tab_actions(){
	$('ul.ajaxtabs button').click(function(){
		action = $(this).attr('name').substring(5);
		window.location.href = window.location.href.split('?')[0]+'?_tabs_sel='+action;
		return false;
	});
}



function do_repost_sale(){
	items = $('textarea#invoices').val();
	items = items.split(',');
	invoice = items[0];
//	alert(invoice);
//	delete items[0];


	if( typeof invoice === 'undefined' ) invoice = 0;

	next = items[1];
	if( typeof next === 'undefined' ) next = 0;
	if( invoice !=0 ){
		$.ajax({
		    type: 'GET',
		    url: '/sales/repost.php?do='+invoice+'&next='+next,
		    dataType: 'json',
		    success: function (data) {
		    	module = 'saleinvoice';
		    	var complate = parseInt($('input#ajaxcomplate').val());
		    	var total = parseInt($('input#ajaxtotal').val());
		    	complate++;
		    	$('input#ajaxcomplate').val(complate);

		    	percent = Math.round( complate/ total * 100 );

		    	if( $('#process'+module).length <=0 ){
		    		$('#msgbox').after('<div id="process'+module+'" class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100" style="width:'+percent+'%"></div> </div>');
		    	}
	    		$('#process'+module).find('.progress-bar').css('width',percent+'%').html('read '+module+' data ('+complate +'/'+ total+')');
		    	items = $('textarea#invoices').val();
		    	items = items.split(',');
		    	items.splice(0, 1);

		    	$('textarea#invoices').text(items.join(','));


		    }, complete: function (data) {
		    	var obj = jQuery.parseJSON(data.responseText);

		    	if( typeof obj.next != "undefined" ){
		    		setTimeout(function() {
		    			do_repost_sale();
		    		}, 1);
		    		//setTimeout(get_complate, interval);
		    	} else {
//		    		//check_import();
		    	}
//

		    }
	    });
	}


}


